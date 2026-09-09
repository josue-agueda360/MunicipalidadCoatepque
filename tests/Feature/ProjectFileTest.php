<?php

use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set(
        'security.document_delete_code_hash',
        Hash::make('59264018'),
    );
});

function projectForFileTests(User $user)
{
    $folder = $user->projectFolders()->create([
        'name' => 'Expedientes',
    ]);

    return $folder->projects()->create([
        'user_id' => $user->id,
        'snip' => '456789',
        'name' => 'Proyecto documental',
        'place' => 'Coatepeque',
        'latitude' => 14.7,
        'longitude' => -91.86,
        'color' => '#e4a52c',
    ]);
}

test('the project view contains persistent document folders and the add control', function () {
    $user = User::factory()->create();
    $project = projectForFileTests($user);

    expect($project->documentFolders()->count())->toBe(5);
    expect($project->documentFolders()->pluck('name')->all())->toBe([
        'Carpeta 1',
        'Carpeta 2',
        'Carpeta 3',
        'Carpeta 4',
        'Carpeta 5',
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('id="project-files-panel"', false)
        ->assertSee('id="project-file-list"', false)
        ->assertSee('id="add-project-document"', false)
        ->assertSee('Añadir documento')
        ->assertSee('id="create-project-document-dialog"', false)
        ->assertSee('id="project-document-context-menu"', false)
        ->assertSee('id="delete-project-document-dialog"', false)
        ->assertSee('Código de seguridad')
        ->assertSee('for (const documentFolder of documentFolders)', false)
        ->assertSee("document.createElement('article')", false)
        ->assertSee("head.setAttribute('aria-expanded', 'true')", false)
        ->assertSee("folderIcon.className = 'project-file-slot__folder'", false)
        ->assertSee('project-file-slot__status--complete', false)
        ->assertSee('project-file-slot__status--missing', false)
        ->assertSee("status.textContent = storedFile ? '✓' : '!'", false)
        ->assertSee('project-completion-status--missing', false)
        ->assertSee('updateProjectCompletionStatus', false)
        ->assertSee('Faltan 5 archivos por subir')
        ->assertSee('project.document_folders ??= [];', false)
        ->assertSee("count.textContent = complete ? '' : String(missingFiles);", false)
        ->assertSee("icon.textContent = complete ? '✓' : '!';", false)
        ->assertSee('project-completion-status__count', false)
        ->assertSee('project-completion-status__icon', false)
        ->assertSee("preview.textContent = '👁 Ver'", false)
        ->assertSee('id="create-project-link-dialog"', false)
        ->assertSee("'🔗 Link '", false)
        ->assertSee('data-project-link-slot', false)
        ->assertSee('Link de Google Drive guardado correctamente.')
        ->assertSee('showProjectLocations(projectId);', false)
        ->assertSee('entityId === selectedProjectId', false)
        ->assertSee('Un archivo por carpeta · máximo 50 MB.')
        ->assertSee('title.textContent = documentFolder.name;', false);
});

test('authenticated users can add named document folders to a shared project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = projectForFileTests($user);

    foreach (range(1, 8) as $number) {
        $this->actingAs($user)
            ->postJson(route('project-document-folders.store', $project), [
                'name' => "Documento adicional {$number}",
            ])
            ->assertCreated()
            ->assertJsonPath('document_folder.position', 5 + $number)
            ->assertJsonPath(
                'document_folder.name',
                "Documento adicional {$number}",
            );
    }

    expect($project->documentFolders()->count())->toBe(13);

    $this->actingAs($user)
        ->postJson(route('project-document-folders.store', $project), [
            'name' => 'documento ADICIONAL 1',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->actingAs($otherUser)
        ->postJson(route('project-document-folders.store', $project), [
            'name' => 'Documento compartido',
        ])
        ->assertCreated()
        ->assertJsonPath('document_folder.position', 14);
});

test('a project is marked complete only when its five folders have files', function () {
    $user = User::factory()->create();
    $project = projectForFileTests($user);

    foreach (range(1, 5) as $slot) {
        $project->files()->create([
            'slot' => $slot,
            'original_name' => "documento-{$slot}.pdf",
            'path' => "project-files/prueba/documento-{$slot}.pdf",
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size' => 100,
        ]);
    }

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(
            'project-completion-status project-completion-status--complete',
            false,
        )
        ->assertSee('Documentación completa');
});

test('a newly added empty document folder updates the missing file status', function () {
    $user = User::factory()->create();
    $project = projectForFileTests($user);

    foreach (range(1, 5) as $slot) {
        $project->files()->create([
            'slot' => $slot,
            'original_name' => "documento-{$slot}.pdf",
            'path' => "project-files/prueba/documento-{$slot}.pdf",
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size' => 100,
        ]);
    }

    $project->documentFolders()->create([
        'name' => 'Contrato final',
        'position' => 6,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Falta 1 archivo por subir')
        ->assertDontSee(
            'project-completion-status project-completion-status--complete',
            false,
        );
});

test('an authenticated owner can upload one file to each project slot', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $project = projectForFileTests($user);

    $response = $this->actingAs($user)
        ->post(route('project-files.store', [$project, 1]), [
            'file' => UploadedFile::fake()->create(
                'planificacion.pdf',
                120,
                'application/pdf',
            ),
        ])
        ->assertCreated()
        ->assertJsonPath('file.slot', 1)
        ->assertJsonPath('file.name', 'planificacion.pdf');

    $projectFile = ProjectFile::query()->firstOrFail();

    $this->assertDatabaseHas('project_files', [
        'project_id' => $project->id,
        'slot' => 1,
        'original_name' => 'planificacion.pdf',
    ]);
    Storage::disk('local')->assertExists($projectFile->path);
    expect($response->json('file.download_url'))->toContain(
        "/proyectos/{$project->id}/archivos/{$projectFile->id}",
    );
    expect($response->json('file.preview_url'))->toContain(
        "/proyectos/{$project->id}/archivos/{$projectFile->id}/ver",
    );

    $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post(route('project-files.store', [$project, 1]), [
            'file' => UploadedFile::fake()->create(
                'repetido.pdf',
                20,
                'application/pdf',
            ),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    expect($project->files()->count())->toBe(1);
});

test('project documents use the configured cloud disk and prefix', function () {
    Storage::fake('s3');
    config()->set('filesystems.project_files_disk', 's3');
    config()->set(
        'filesystems.project_files_prefix',
        'ArchivosMunicipalidad',
    );

    $user = User::factory()->create();
    $project = projectForFileTests($user);

    $this->actingAs($user)
        ->post(route('project-files.store', [$project, 1]), [
            'file' => UploadedFile::fake()->create(
                'expediente.pdf',
                120,
                'application/pdf',
            ),
        ])
        ->assertCreated();

    $projectFile = ProjectFile::query()->firstOrFail();

    expect($projectFile->disk)->toBe('s3');
    expect($projectFile->path)->toBe(
        "ArchivosMunicipalidad/{$project->snip}/expediente.pdf",
    );
    expect($projectFile->path)->not->toContain(
        "ArchivosMunicipalidad/{$user->id}/{$project->id}/",
    );
    Storage::disk('s3')->assertExists($projectFile->path);

    $this->actingAs($user)
        ->post(route('project-files.store', [$project, 2]), [
            'file' => UploadedFile::fake()->create(
                'expediente.pdf',
                120,
                'application/pdf',
            ),
        ])
        ->assertCreated();

    $secondProjectFile = ProjectFile::query()
        ->where('slot', 2)
        ->firstOrFail();

    expect($secondProjectFile->path)->toBe(
        "ArchivosMunicipalidad/{$project->snip}/expediente (2).pdf",
    );
    Storage::disk('s3')->assertExists($secondProjectFile->path);
});

test('project document uploads allow up to fifty megabytes', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $project = projectForFileTests($user);

    $this->actingAs($user)
        ->post(route('project-files.store', [$project, 1]), [
            'file' => UploadedFile::fake()->create(
                'archivo-50mb.pdf',
                51200,
                'application/pdf',
            ),
        ])
        ->assertCreated();

    $this->actingAs($user)
        ->postJson(route('project-files.store', [$project, 2]), [
            'file' => UploadedFile::fake()->create(
                'archivo-mayor.pdf',
                51201,
                'application/pdf',
            ),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');

    $this->actingAs($user)
        ->postJson(route('project-files.store', [$project, 999]), [
            'file' => UploadedFile::fake()->create(
                'sin-carpeta.pdf',
                10,
                'application/pdf',
            ),
        ])
        ->assertNotFound();
});

test('an owner can save view download and delete a google drive link', function () {
    $user = User::factory()->create();
    $project = projectForFileTests($user);
    $driveUrl = 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view?usp=sharing';

    $response = $this->actingAs($user)
        ->postJson(route('project-files.links.store', [$project, 1]), [
            'url' => $driveUrl,
        ])
        ->assertCreated()
        ->assertJsonPath('file.slot', 1)
        ->assertJsonPath('file.name', 'Documento de Google Drive')
        ->assertJsonPath('file.is_link', true)
        ->assertJsonPath('file.size', null);

    $projectFile = ProjectFile::query()->firstOrFail();

    $this->assertDatabaseHas('project_files', [
        'project_id' => $project->id,
        'slot' => 1,
        'external_url' => $driveUrl,
        'path' => null,
        'disk' => null,
        'size' => null,
    ]);

    $this->actingAs($user)
        ->get(route('project-files.preview', [$project, $projectFile]))
        ->assertRedirect($driveUrl);

    $this->actingAs($user)
        ->get(route('project-files.download', [$project, $projectFile]))
        ->assertRedirect(
            'https://drive.google.com/uc?export=download&id=1AbCdEfGhIjKlMnOp',
        );

    expect($response->json('file.preview_url'))->toContain(
        "/proyectos/{$project->id}/archivos/{$projectFile->id}/ver",
    );

    $this->actingAs($user)
        ->deleteJson(route('project-files.destroy', [$project, $projectFile]), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('project_files', ['id' => $projectFile->id]);
});

test('drive links are validated and shared with authenticated users', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = projectForFileTests($owner);

    $this->actingAs($owner)
        ->postJson(route('project-files.links.store', [$project, 1]), [
            'url' => 'https://example.com/documento-grande.pdf',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('url');

    $this->actingAs($owner)
        ->postJson(route('project-files.links.store', [$project, 1]), [
            'url' => 'https://drive.google.com/file/d/DocumentoValido/view',
        ])
        ->assertCreated();

    $this->actingAs($owner)
        ->postJson(route('project-files.links.store', [$project, 1]), [
            'url' => 'https://drive.google.com/file/d/DocumentoRepetido/view',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('url');

    $this->actingAs($otherUser)
        ->postJson(route('project-files.links.store', [$project, 2]), [
            'url' => 'https://drive.google.com/file/d/DocumentoCompartido/view',
        ])
        ->assertCreated();
});

test('all authenticated users can upload and read shared project files', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = projectForFileTests($owner);

    $this->actingAs($otherUser)
        ->post(route('project-files.store', [$project, 1]), [
            'file' => UploadedFile::fake()->create(
                'compartido.pdf',
                20,
                'application/pdf',
            ),
        ])
        ->assertCreated();

    $projectFile = $project->files()->firstOrFail();
    $otherSessionToken = session('active_session_token');

    $this->flushSession();

    $this->actingAs($owner)
        ->get(route('project-files.download', [$project, $projectFile]))
        ->assertOk()
        ->assertDownload('compartido.pdf');

    $previewResponse = $this->actingAs($owner)
        ->get(route('project-files.preview', [$project, $projectFile]))
        ->assertOk();

    expect($previewResponse->headers->get('content-disposition'))
        ->toContain('inline');

    $this->flushSession();

    $this->actingAs($otherUser)
        ->withSession(['active_session_token' => $otherSessionToken])
        ->get(route('project-files.download', [$project, $projectFile]))
        ->assertOk()
        ->assertDownload('compartido.pdf');
});

test('deleting a project file requires the configured deletion code', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $project = projectForFileTests($user);

    $this->actingAs($user)
        ->post(route('project-files.store', [$project, 2]), [
            'file' => UploadedFile::fake()->create(
                'presupuesto.xlsx',
                25,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ),
        ])
        ->assertCreated();

    $projectFile = $project->files()->firstOrFail();

    $this->actingAs($user)
        ->deleteJson(route('project-files.destroy', [$project, $projectFile]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    $this->actingAs($user)
        ->deleteJson(route('project-files.destroy', [$project, $projectFile]), [
            'code' => '11111111',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    Storage::disk('local')->assertExists($projectFile->path);

    $this->actingAs($user)
        ->deleteJson(route('project-files.destroy', [$project, $projectFile]), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('project_files', ['id' => $projectFile->id]);
    Storage::disk('local')->assertMissing($projectFile->path);
});

test('right click document folders can be deleted with the deletion code', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $project = projectForFileTests($user);
    $documentFolder = $project->documentFolders()->create([
        'name' => 'Documento temporal',
        'position' => 6,
    ]);

    $this->actingAs($user)
        ->post(route('project-files.store', [$project, 6]), [
            'file' => UploadedFile::fake()->create(
                'temporal.pdf',
                20,
                'application/pdf',
            ),
        ])
        ->assertCreated();

    $projectFile = $project->files()->where('slot', 6)->firstOrFail();

    $this->actingAs($user)
        ->deleteJson(route('project-document-folders.destroy', [
            $project,
            $documentFolder,
        ]), [
            'code' => '11111111',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    Storage::disk('local')->assertExists($projectFile->path);

    $this->actingAs($user)
        ->deleteJson(route('project-document-folders.destroy', [
            $project,
            $documentFolder,
        ]), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('project_document_folders', [
        'id' => $documentFolder->id,
    ]);
    $this->assertDatabaseMissing('project_files', ['id' => $projectFile->id]);
    Storage::disk('local')->assertMissing($projectFile->path);
    expect($project->documentFolders()->count())->toBe(5);
});

test('deleting a project also removes its stored files', function () {
    Storage::fake('local');
    $user = User::factory()->create([
        'password' => Hash::make('ClaveProyecto1!'),
    ]);
    $project = projectForFileTests($user);

    $this->actingAs($user)
        ->post(route('project-files.store', [$project, 3]), [
            'file' => UploadedFile::fake()->create(
                'evidencia.jpg',
                20,
                'image/jpeg',
            ),
        ])
        ->assertCreated();

    $projectFile = $project->files()->firstOrFail();

    $this->actingAs($user)
        ->deleteJson(route('projects.destroy', $project), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('project_files', ['id' => $projectFile->id]);
    Storage::disk('local')->assertMissing($projectFile->path);
});
