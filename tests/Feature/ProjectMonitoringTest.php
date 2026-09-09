<?php

use App\Models\ProjectMonitoring;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function monitoringProjectFor(User $user, array $attributes = []): \App\Models\Project
{
    $folder = $user->projectFolders()->create([
        'name' => $attributes['folder_name'] ?? 'Proyectos de prueba',
    ]);

    return $folder->projects()->create(array_merge([
        'user_id' => $user->id,
        'snip' => '458965',
        'name' => 'Mejoramiento del sistema de agua potable',
        'place' => 'Barrio La Esperanza',
        'latitude' => 14.7048,
        'longitude' => -91.8718,
        'color' => '#e4a52c',
    ], collect($attributes)->except('folder_name')->all()));
}

function validMonitoringPayload(array $overrides = []): array
{
    return array_merge([
        'project_id' => null,
        'project_name' => 'Proyecto exclusivo de monitoreo',
        'status' => 'planning',
        'category' => 'tender',
        'starts_on' => '2026-09-01',
        'ends_on' => '2026-12-15',
        'location' => 'Coatepeque',
        'description' => 'Proceso en preparación.',
    ], $overrides);
}

test('the monitoring module is protected by authentication', function () {
    $this->get(route('project-monitoring.index'))
        ->assertRedirect(route('login'));

    $this->postJson(
        route('project-monitoring.store'),
        validMonitoringPayload(),
    )->assertUnauthorized();
});

test('an authenticated user sees all shared projects in monitoring', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    monitoringProjectFor($user);
    monitoringProjectFor($otherUser, [
        'folder_name' => 'Carpeta ajena',
        'snip' => '909090',
        'name' => 'Proyecto que no debe aparecer',
    ]);
    $user->projectMonitorings()->create([
        'project_name' => 'Registro visible para cambiar estado',
        'status' => 'in_progress',
        'category' => 'tender',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'location' => 'Coatepeque',
    ]);

    $this->actingAs($user)
        ->get(route('project-monitoring.index', ['embed' => 1]))
        ->assertOk()
        ->assertSee('class="is-embedded"', false)
        ->assertSee('Monitoreo de proyectos')
        ->assertSee('458965')
        ->assertSee('Mejoramiento del sistema de agua potable')
        ->assertSee('909090')
        ->assertSee('Proyecto que no debe aparecer')
        ->assertSee('name="project_id"', false)
        ->assertSee('name="project_name"', false)
        ->assertSee('name="status"', false)
        ->assertSee('name="category"', false)
        ->assertSee('name="starts_on"', false)
        ->assertSee('name="ends_on"', false)
        ->assertSee('name="location"', false)
        ->assertSee('name="description"', false)
        ->assertSee('id="monitoring-status-filter"', false)
        ->assertSee('data-monitoring-status', false)
        ->assertSee('municipal_portal_theme', false)
        ->assertSee("event.data?.type === 'theme-changed'", false)
        ->assertSee('html.light-mode .panel', false)
        ->assertSee('.record-card[open] .record-title strong', false)
        ->assertSee('white-space: normal', false)
        ->assertSee('Planificación')
        ->assertSee('En ejecución')
        ->assertSee('Finalizado')
        ->assertSee('Cerrado')
        ->assertSee('Licitación')
        ->assertSee('Cotización');
});

test('an owner can change a saved monitoring status', function () {
    $user = User::factory()->create();
    $record = $user->projectMonitorings()->create([
        'project_name' => 'Construcción del mercado municipal',
        'status' => 'in_progress',
        'category' => 'tender',
        'starts_on' => '2026-02-01',
        'ends_on' => '2026-11-30',
        'location' => 'Zona central',
    ]);

    $this->actingAs($user)
        ->patchJson(route('project-monitoring.status', $record), [
            'status' => 'finished',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Estado actualizado correctamente.')
        ->assertJsonPath('record.status', 'finished')
        ->assertJsonPath('record.status_label', 'Finalizado');

    $this->assertDatabaseHas('project_monitorings', [
        'id' => $record->id,
        'user_id' => $user->id,
        'status' => 'finished',
    ]);
});

test('monitoring status changes are shared and still validate allowed values', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $record = $owner->projectMonitorings()->create([
        'project_name' => 'Proyecto protegido',
        'status' => 'planning',
        'category' => 'quotation',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-10-01',
        'location' => 'Coatepeque',
    ]);

    $this->actingAs($otherUser)
        ->patchJson(route('project-monitoring.status', $record), [
            'status' => 'closed',
        ])
        ->assertOk()
        ->assertJsonPath('record.status', 'closed');

    $this->actingAs($owner)
        ->patchJson(route('project-monitoring.status', $record), [
            'status' => 'invalid-status',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');

    expect($record->fresh()->status)->toBe('closed');
});

test('a monitoring record can be linked to an existing owned project', function () {
    $user = User::factory()->create();
    $project = monitoringProjectFor($user);

    $this->actingAs($user)
        ->postJson(route('project-monitoring.store'), validMonitoringPayload([
            'project_id' => $project->id,
            'project_name' => 'Nombre alterado desde el navegador',
            'status' => 'in_progress',
            'category' => 'quotation',
            'location' => 'Zona central',
        ]))
        ->assertCreated()
        ->assertJsonPath('record.project_id', $project->id)
        ->assertJsonPath('record.project_name', $project->name)
        ->assertJsonPath('record.snip', '458965')
        ->assertJsonPath('record.linked', true)
        ->assertJsonPath('record.status_label', 'En ejecución')
        ->assertJsonPath('record.category_label', 'Cotización');

    $this->assertDatabaseHas('project_monitorings', [
        'user_id' => $user->id,
        'project_id' => $project->id,
        'project_name' => $project->name,
        'status' => 'in_progress',
        'category' => 'quotation',
    ]);
});

test('a linked project is no longer suggested and cannot be linked twice', function () {
    $user = User::factory()->create();
    $linkedProject = monitoringProjectFor($user);
    $availableProject = monitoringProjectFor($user, [
        'folder_name' => 'Segunda carpeta',
        'snip' => '741852',
        'name' => 'Proyecto todavía disponible',
    ]);
    $user->projectMonitorings()->create([
        'project_id' => $linkedProject->id,
        'project_name' => $linkedProject->name,
        'status' => 'in_progress',
        'category' => 'tender',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'location' => $linkedProject->place,
    ]);

    $response = $this->actingAs($user)
        ->get(route('project-monitoring.index'))
        ->assertOk()
        ->assertSee('let availableProjects', false)
        ->assertSee('availableProjects = availableProjects.filter', false);

    expect($response->viewData('projects')->pluck('id')->all())
        ->toBe([$availableProject->id]);

    $this->actingAs($user)
        ->postJson(route('project-monitoring.store'), validMonitoringPayload([
            'project_id' => $linkedProject->id,
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('project_id');

    expect($user->projectMonitorings()->where('project_id', $linkedProject->id)->count())
        ->toBe(1);
});

test('a manually typed name stays only in monitoring', function () {
    $user = User::factory()->create();
    monitoringProjectFor($user);
    $projectCount = $user->projects()->count();

    $this->actingAs($user)
        ->postJson(route('project-monitoring.store'), validMonitoringPayload([
            'project_name' => 'Nueva licitación sin proyecto registrado',
        ]))
        ->assertCreated()
        ->assertJsonPath('record.project_id', null)
        ->assertJsonPath(
            'record.project_name',
            'Nueva licitación sin proyecto registrado',
        )
        ->assertJsonPath('record.linked', false);

    expect($user->projects()->count())->toBe($projectCount);

    $this->assertDatabaseHas('project_monitorings', [
        'user_id' => $user->id,
        'project_id' => null,
        'project_name' => 'Nueva licitación sin proyecto registrado',
    ]);
});

test('a user can link monitoring to any shared project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherProject = monitoringProjectFor($otherUser);

    $this->actingAs($user)
        ->postJson(route('project-monitoring.store'), validMonitoringPayload([
            'project_id' => $otherProject->id,
        ]))
        ->assertCreated()
        ->assertJsonPath('record.project_id', $otherProject->id);

    expect(ProjectMonitoring::query()->count())->toBe(1);
});

test('monitoring validates states categories dates and blank text', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('project-monitoring.store'), validMonitoringPayload([
            'project_name' => '   ',
            'status' => 'unknown',
            'category' => 'purchase',
            'starts_on' => '2026-09-10',
            'ends_on' => '2026-09-01',
            'location' => '   ',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'project_name',
            'status',
            'category',
            'ends_on',
            'location',
        ]);

    expect(ProjectMonitoring::query()->count())->toBe(0);
});

test('saved monitoring records are visible to every authenticated user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $owner->projectMonitorings()->create([
        'project_name' => 'Registro privado del propietario',
        'status' => 'finished',
        'category' => 'tender',
        'starts_on' => '2026-01-10',
        'ends_on' => '2026-08-31',
        'location' => 'Coatepeque',
        'description' => 'Visible para el propietario.',
    ]);

    $this->actingAs($otherUser)
        ->get(route('project-monitoring.index'))
        ->assertOk()
        ->assertSee('Registro privado del propietario')
        ->assertSee('Finalizado')
        ->assertSee('Visible para el propietario.');

    $this->actingAs($owner)
        ->get(route('project-monitoring.index'))
        ->assertOk()
        ->assertSee('Registro privado del propietario')
        ->assertSee('Finalizado')
        ->assertSee('Visible para el propietario.');
});

test('the home navigation contains monitoring as a third module', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Ubicación de Proyectos')
        ->assertSee('Monitoreo')
        ->assertSee('data-module-target="monitoring"', false)
        ->assertSee('data-module-panel="monitoring"', false)
        ->assertSee('id="project-monitoring-frame"', false)
        ->assertSee(
            route('project-monitoring.index', ['embed' => 1]),
            false,
        )
        ->assertSee("['monitoring', projectMonitoringFrame]", false);
});
