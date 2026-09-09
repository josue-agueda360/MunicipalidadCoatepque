<?php

use App\Models\Project;
use App\Models\ProjectFinancialDocument;
use App\Models\ProjectFinancialMovement;
use App\Models\ProjectFinancialRequiredDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('security.document_delete_code_hash', Hash::make('59264018'));
});

function projectForFinanceTests(User $user, string $snip = '654321'): Project
{
    $folder = $user->projectFolders()->create([
        'name' => "Proyectos financieros {$user->id}",
    ]);

    return $folder->projects()->create([
        'user_id' => $user->id,
        'snip' => $snip,
        'name' => 'Construcción calle municipal',
        'place' => 'Barrio La Batalla',
        'latitude' => 14.7,
        'longitude' => -91.86,
        'color' => '#e4a52c',
    ]);
}

test('the financial module is protected and is available from home', function () {
    $this->get(route('project-finance.index'))->assertRedirect(route('login'));

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Control financiero')
        ->assertSee('data-module-target="finance"', false)
        ->assertSee('id="project-finance-frame"', false)
        ->assertSee(route('project-finance.index', ['embed' => 1]), false);
});

test('the financial module lists the shared municipal projects', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    projectForFinanceTests($owner, '111222');
    $foreign = projectForFinanceTests($other, '999888');
    $foreign->update(['name' => 'Proyecto financiero ajeno']);

    $this->actingAs($owner)
        ->get(route('project-finance.index'))
        ->assertOk()
        ->assertSee('Control financiero')
        ->assertSee('111222')
        ->assertSee('999888')
        ->assertSee('Presupuesto asignado')
        ->assertSee('Fuentes de financiamiento')
        ->assertSee('Documentos financieros')
        ->assertSee('No tengo contrato')
        ->assertSee('Este documento es obligatorio y no puede omitirse.')
        ->assertDontSee('id="movement-form"', false)
        ->assertSee('Avance físico vs. financiero')
        ->assertSee('const submittedForm = event.currentTarget;', false)
        ->assertSee('submittedForm.reset();', false)
        ->assertSee("event.data?.type === 'theme-changed'", false);
});

test('contract and budget files are stored in their own idrive project folders', function () {
    Storage::fake('local');
    config()->set('filesystems.project_files_disk', 'local');
    config()->set('filesystems.project_files_prefix', 'ArchivosMunicipalidad');
    $user = User::factory()->create();
    $project = projectForFinanceTests($user);

    $this->actingAs($user)
        ->post(route('project-finance.required-documents.store', [$project, 'contract']), [
            'file' => UploadedFile::fake()->create('Contrato principal.pdf', 120, 'application/pdf'),
        ])
        ->assertCreated()
        ->assertJsonPath('project.required_documents.contract.has_file', true)
        ->assertJsonPath('project.required_documents.contract.name', 'Contrato principal.pdf')
        ->assertJsonPath('project.required_documents.budget.has_file', false);

    $this->actingAs($user)
        ->post(route('project-finance.required-documents.store', [$project, 'budget']), [
            'file' => UploadedFile::fake()->create('Presupuesto 2026.xlsx', 80, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ])
        ->assertCreated()
        ->assertJsonPath('project.required_documents.budget.has_file', true)
        ->assertJsonPath('project.required_documents.budget.name', 'Presupuesto 2026.xlsx');

    $contract = ProjectFinancialRequiredDocument::query()->where('document_type', 'contract')->firstOrFail();
    $budget = ProjectFinancialRequiredDocument::query()->where('document_type', 'budget')->firstOrFail();
    expect($contract->path)->toBe("ArchivosMunicipalidad/{$project->snip}/finanzas/documentos/contract/Contrato principal.pdf");
    expect($budget->path)->toBe("ArchivosMunicipalidad/{$project->snip}/finanzas/documentos/budget/Presupuesto 2026.xlsx");
    Storage::disk('local')->assertExists($contract->path);
    Storage::disk('local')->assertExists($budget->path);
});

test('contract can be waived but a saved contract must be deleted first', function () {
    Storage::fake('local');
    config()->set('filesystems.project_files_disk', 'local');
    $user = User::factory()->create();
    $project = projectForFinanceTests($user);

    $this->actingAs($user)
        ->patchJson(route('project-finance.contract-waiver.update', $project), ['waived' => true])
        ->assertOk()
        ->assertJsonPath('project.required_documents.contract.waived', true)
        ->assertJsonPath('project.required_documents.contract.has_file', false);

    $this->actingAs($user)
        ->post(route('project-finance.required-documents.store', [$project, 'contract']), [
            'file' => UploadedFile::fake()->create('Contrato.pdf', 30, 'application/pdf'),
        ])
        ->assertCreated()
        ->assertJsonPath('project.required_documents.contract.waived', false)
        ->assertJsonPath('project.required_documents.contract.has_file', true);

    $this->actingAs($user)
        ->patchJson(route('project-finance.contract-waiver.update', $project), ['waived' => true])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('waived');
});

test('required financial documents are shared and deletion removes the stored file', function () {
    Storage::fake('local');
    config()->set('filesystems.project_files_disk', 'local');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $project = projectForFinanceTests($owner);
    $document = $project->financialRequiredDocuments()->create([
        'document_type' => 'budget',
        'original_name' => 'presupuesto.pdf',
        'path' => 'finanzas/documentos/presupuesto.pdf',
        'disk' => 'local',
        'mime_type' => 'application/pdf',
        'size' => 10,
    ]);
    Storage::disk('local')->put($document->path, 'contenido');

    $this->actingAs($other)
        ->get(route('project-finance.required-documents.preview', [$project, $document]))
        ->assertOk();

    $this->actingAs($owner)
        ->deleteJson(route('project-finance.required-documents.destroy', [$project, $document]), ['code' => '00000000'])
        ->assertUnprocessable();
    Storage::disk('local')->assertExists($document->path);

    $this->actingAs($owner)
        ->deleteJson(route('project-finance.required-documents.destroy', [$project, $document]), ['code' => '59264018'])
        ->assertOk()
        ->assertJsonPath('project.required_documents.budget.has_file', false);
    Storage::disk('local')->assertMissing($document->path);
    $this->assertDatabaseMissing('project_financial_required_documents', ['id' => $document->id]);
});

test('an owner can save budget and physical progress with calculated summary', function () {
    $user = User::factory()->create();
    $project = projectForFinanceTests($user);

    $this->actingAs($user)
        ->putJson(route('project-finance.profile.update', $project), [
            'allocated_budget' => 1500000,
            'physical_progress' => 45,
        ])
        ->assertOk()
        ->assertJsonPath('project.allocated_budget', 1500000)
        ->assertJsonPath('project.physical_progress', 45)
        ->assertJsonPath('project.executed_amount', 0)
        ->assertJsonPath('project.available_balance', 1500000)
        ->assertJsonPath('project.financial_status', 'normal');

    $this->assertDatabaseHas('project_financial_profiles', [
        'project_id' => $project->id,
        'allocated_budget' => '1500000.00',
        'physical_progress' => '45.00',
    ]);
});

test('funding sources can be added and other requires its name', function () {
    $user = User::factory()->create();
    $project = projectForFinanceTests($user);

    $this->actingAs($user)
        ->postJson(route('project-finance.funding-sources.store', $project), [
            'source_type' => 'municipal',
            'amount' => 800000,
        ])
        ->assertCreated()
        ->assertJsonPath('project.funding_total', 800000)
        ->assertJsonPath('project.funding_sources.0.label', 'Fondos municipales');

    $this->actingAs($user)
        ->postJson(route('project-finance.funding-sources.store', $project), [
            'source_type' => 'other',
            'amount' => 1000,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('custom_name');
});

test('a movement stores its idrive backup below the project snip and updates calculations', function () {
    Storage::fake('local');
    config()->set('filesystems.project_files_disk', 'local');
    config()->set('filesystems.project_files_prefix', 'ArchivosMunicipalidad');
    $user = User::factory()->create();
    $project = projectForFinanceTests($user);
    $project->financialProfile()->create([
        'allocated_budget' => 100000,
        'physical_progress' => 20,
    ]);

    $response = $this->actingAs($user)
        ->post(route('project-finance.movements.store', $project), [
            'occurred_on' => '2026-08-10',
            'movement_type' => 'expense',
            'category' => 'materials',
            'description' => 'Compra de cemento',
            'amount' => 25000,
            'document_number' => 'FAC-100',
            'provider' => 'Materiales del Pacífico',
            'file' => UploadedFile::fake()->create('Factura cemento.pdf', 120, 'application/pdf'),
        ])
        ->assertCreated()
        ->assertJsonPath('project.executed_amount', 25000)
        ->assertJsonPath('project.available_balance', 75000)
        ->assertJsonPath('project.financial_progress', 25)
        ->assertJsonPath('project.comparison_status', 'balanced')
        ->assertJsonPath('project.movements.0.recorded_by', $user->name)
        ->assertJsonPath('project.movements.0.document.name', 'Factura cemento.pdf');

    $movement = ProjectFinancialMovement::query()->firstOrFail();
    $document = ProjectFinancialDocument::query()->firstOrFail();
    expect($document->path)->toStartWith("ArchivosMunicipalidad/{$project->snip}/finanzas/{$movement->id}/");
    Storage::disk('local')->assertExists($document->path);
    expect($response->json('project.movements.0.document.preview_url'))->toContain('/ver');
});

test('financial execution detects near limit exceeded and monthly periods', function () {
    $user = User::factory()->create();
    $project = projectForFinanceTests($user);
    $project->financialProfile()->create(['allocated_budget' => 100, 'physical_progress' => 25]);
    foreach ([['2026-01-10', 60], ['2026-02-10', 30]] as [$date, $amount]) {
        $project->financialMovements()->create([
            'recorded_by_user_id' => $user->id,
            'occurred_on' => $date,
            'movement_type' => 'expense',
            'category' => 'services',
            'description' => 'Pago de servicio',
            'amount' => $amount,
        ]);
    }

    $response = $this->actingAs($user)->get(route('project-finance.index'))->assertOk();
    $response->assertSee('financial_ahead', false)
        ->assertSee('near_limit', false)
        ->assertSee('enero 2026', false)
        ->assertSee('febrero 2026', false);
});

test('deleting financial records requires the shared deletion code', function () {
    $user = User::factory()->create();
    $project = projectForFinanceTests($user);
    $source = $project->fundingSources()->create(['source_type' => 'donation', 'amount' => 500]);
    $movement = $project->financialMovements()->create([
        'recorded_by_user_id' => $user->id,
        'occurred_on' => '2026-08-20',
        'movement_type' => 'expense',
        'category' => 'transport',
        'description' => 'Flete',
        'amount' => 250,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('project-finance.funding-sources.destroy', [$project, $source]), ['code' => '00000000'])
        ->assertUnprocessable();
    $this->assertDatabaseHas('project_funding_sources', ['id' => $source->id]);

    $this->actingAs($user)
        ->deleteJson(route('project-finance.funding-sources.destroy', [$project, $source]), ['code' => '59264018'])
        ->assertOk();
    $this->actingAs($user)
        ->deleteJson(route('project-finance.movements.destroy', [$project, $movement]), ['code' => '59264018'])
        ->assertOk();

    $this->assertDatabaseMissing('project_funding_sources', ['id' => $source->id]);
    $this->assertDatabaseMissing('project_financial_movements', ['id' => $movement->id]);
});

test('all authenticated users can change and read shared financial data', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $project = projectForFinanceTests($owner);
    $movement = $project->financialMovements()->create([
        'recorded_by_user_id' => $owner->id,
        'occurred_on' => '2026-08-20',
        'movement_type' => 'expense',
        'category' => 'services',
        'description' => 'Supervisión',
        'amount' => 100,
    ]);
    $document = $movement->document()->create([
        'original_name' => 'respaldo.pdf',
        'path' => 'finanzas/respaldo.pdf',
        'disk' => 'local',
        'mime_type' => 'application/pdf',
        'size' => 10,
    ]);
    Storage::disk('local')->put($document->path, 'contenido');

    $this->actingAs($other)
        ->putJson(route('project-finance.profile.update', $project), ['allocated_budget' => 10, 'physical_progress' => 10])
        ->assertOk()
        ->assertJsonPath('project.allocated_budget', 10);
    $this->actingAs($other)
        ->get(route('project-finance.documents.preview', [$project, $movement, $document]))
        ->assertOk();
});
