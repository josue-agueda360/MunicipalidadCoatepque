<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set(
        'security.document_delete_code_hash',
        Hash::make('59264018'),
    );
});

test('the municipal workspace is initialized once and shared by every user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Proyectos municipales')
        ->assertSee('Mejoramiento de calle con pavimento')
        ->assertSee('id="project-snip"', false)
        ->assertSee('pattern="[0-9]{1,6}"', false)
        ->assertSee('id="project-latitude"', false)
        ->assertSee('id="project-longitude"', false)
        ->assertSee('id="project-no-coordinates"', false)
        ->assertSee('No tengo coordenadas')
        ->assertSee('coatepequeProjectCenter = [14.7000, -91.8667]', false)
        ->assertSee('projectLatitudeInput.disabled = useCoatepequeCenter', false)
        ->assertSee('formatCoordinate')
        ->assertSee('parseCoordinate')
        ->assertSee('Editar proyecto')
        ->assertSee('Eliminar proyecto')
        ->assertSee('id="delete-folder-code"', false)
        ->assertSee('id="delete-project-code"', false)
        ->assertDontSee('id="project-security-code"', false)
        ->assertSee('removeProjectLocation(projectId);', false)
        ->assertSee('projectIds.forEach((projectId) => {', false)
        ->assertSee('maxlength="150"', false)
        ->assertSee('novalidate', false)
        ->assertSee('data-add-project', false);

    $this->actingAs($user)->get(route('home'))->assertOk();

    $this->actingAs($otherUser)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Proyectos municipales')
        ->assertSee('Mejoramiento de calle con pavimento');

    expect($user->projectFolders()->count())->toBe(1)
        ->and($user->projects()->count())->toBe(1)
        ->and($otherUser->projectFolders()->count())->toBe(0)
        ->and($otherUser->projects()->count())->toBe(0)
        ->and($user->fresh()->project_workspace_initialized_at)->not->toBeNull()
        ->and($otherUser->fresh()->project_workspace_initialized_at)->not->toBeNull();
});

test('an authenticated user can create a project inside their folder', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Puentes',
    ]);

    $this->actingAs($user)
        ->postJson(route('projects.store', $folder), [
            'name' => 'Puente Las Flores',
            'snip' => '123456',
            'place' => 'Aldea Las Flores',
            'latitude' => 14.7012345,
            'longitude' => -91.8612345,
        ])
        ->assertCreated()
        ->assertJsonPath('project.snip', '123456')
        ->assertJsonPath('project.name', 'Puente Las Flores')
        ->assertJsonPath('project.folder_id', $folder->id);

    $this->assertDatabaseHas('projects', [
        'user_id' => $user->id,
        'project_folder_id' => $folder->id,
        'snip' => '123456',
        'name' => 'Puente Las Flores',
        'place' => 'Aldea Las Flores',
        'color' => $folder->fresh()->color,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('<strong>123456-Puente Las Flores</strong>', false)
        ->assertSee('<small>Aldea Las Flores</small>', false);
});

test('all projects inherit the permanent color of their folder', function () {
    $user = User::factory()->create();
    $firstFolder = $user->projectFolders()->create(['name' => 'Calles']);
    $secondFolder = $user->projectFolders()->create(['name' => 'Puentes']);
    $firstProject = $firstFolder->projects()->create([
        'user_id' => $user->id,
        'name' => 'Calle uno',
        'place' => 'Zona uno',
        'latitude' => 14.70,
        'longitude' => -91.87,
        'color' => '#ffffff',
    ]);
    $secondProject = $firstFolder->projects()->create([
        'user_id' => $user->id,
        'name' => 'Calle dos',
        'place' => 'Zona dos',
        'latitude' => 14.71,
        'longitude' => -91.88,
        'color' => '#000000',
    ]);
    $otherFolderProject = $secondFolder->projects()->create([
        'user_id' => $user->id,
        'name' => 'Puente uno',
        'place' => 'Zona tres',
        'latitude' => 14.72,
        'longitude' => -91.89,
        'color' => '#ffffff',
    ]);

    expect($firstProject->color)->toBe($firstFolder->color)
        ->and($secondProject->color)->toBe($firstFolder->color)
        ->and($otherFolderProject->color)->toBe($secondFolder->color)
        ->and($firstFolder->color)->not->toBe($secondFolder->color);
});

test('project names must be unique inside the same folder', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Calles',
    ]);
    $folder->projects()->create([
        'user_id' => $user->id,
        'name' => 'Pavimentación central',
        'place' => 'Centro',
        'latitude' => 14.7,
        'longitude' => -91.86,
        'color' => '#e4a52c',
    ]);

    $this->actingAs($user)
        ->postJson(route('projects.store', $folder), [
            'name' => 'pavimentación central',
            'snip' => '654321',
            'place' => 'Otro sector',
            'latitude' => 14.71,
            'longitude' => -91.87,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('a project name accepts 150 characters but rejects 151', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Proyectos extensos',
    ]);
    $validName = str_repeat('A', 150);

    $this->actingAs($user)
        ->postJson(route('projects.store', $folder), [
            'name' => $validName,
            'snip' => '150150',
            'latitude' => 14.7,
            'longitude' => -91.86,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('projects', [
        'name' => $validName,
        'snip' => '150150',
    ]);

    $this->actingAs($user)
        ->postJson(route('projects.store', $folder), [
            'name' => str_repeat('B', 151),
            'snip' => '151151',
            'latitude' => 14.7,
            'longitude' => -91.86,
        ])
        ->assertUnprocessable()
        ->assertJsonPath(
            'errors.name.0',
            'El nombre puede tener hasta 150 caracteres.',
        );
});

test('snip accepts only up to six digits and is unique across the system', function () {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();
    $firstFolder = $firstUser->projectFolders()->create([
        'name' => 'Primeros proyectos',
    ]);
    $secondFolder = $secondUser->projectFolders()->create([
        'name' => 'Otros proyectos',
    ]);

    $invalidSnips = ['12A456', '1234567'];

    foreach ($invalidSnips as $snip) {
        $this->actingAs($firstUser)
            ->postJson(route('projects.store', $firstFolder), [
                'name' => "Proyecto {$snip}",
                'snip' => $snip,
                'latitude' => 14.7,
                'longitude' => -91.86,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('snip');
    }

    $this->actingAs($firstUser)
        ->postJson(route('projects.store', $firstFolder), [
            'name' => 'Proyecto con SNIP',
            'snip' => '001234',
            'latitude' => 14.7,
            'longitude' => -91.86,
        ])
        ->assertCreated();

    $this->actingAs($secondUser)
        ->postJson(route('projects.store', $secondFolder), [
            'name' => 'SNIP repetido',
            'snip' => '001234',
            'latitude' => 14.71,
            'longitude' => -91.87,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('snip')
        ->assertJsonPath('errors.snip.0', 'Ese SNIP ya está creado.');
});

test('any authenticated user can create a project inside a shared folder', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $folder = $owner->projectFolders()->create([
        'name' => 'Carpeta privada',
    ]);

    $this->actingAs($otherUser)
        ->postJson(route('projects.store', $folder), [
            'name' => 'Proyecto compartido',
            'snip' => '741963',
            'latitude' => 14.7,
            'longitude' => -91.86,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('projects', [
        'user_id' => $otherUser->id,
        'project_folder_id' => $folder->id,
        'name' => 'Proyecto compartido',
    ]);
});

test('a user can update only one project without changing its folder', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Obras activas',
    ]);
    $project = $folder->projects()->create([
        'user_id' => $user->id,
        'snip' => '145236',
        'name' => 'Proyecto anterior',
        'place' => 'Sector anterior',
        'latitude' => 14.7,
        'longitude' => -91.86,
        'color' => '#e4a52c',
    ]);

    $this->actingAs($user)
        ->patchJson(route('projects.update', $project), [
            'snip' => '145236',
            'name' => 'Proyecto actualizado',
            'place' => 'Sector nuevo',
            'latitude' => 14.7123,
            'longitude' => -91.8754,
        ])
        ->assertOk()
        ->assertJsonPath('project.name', 'Proyecto actualizado');

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'project_folder_id' => $folder->id,
        'name' => 'Proyecto actualizado',
        'place' => 'Sector nuevo',
    ]);
    $this->assertDatabaseHas('project_folders', [
        'id' => $folder->id,
        'name' => 'Obras activas',
    ]);
});

test('deleting one project requires the deletion code and keeps its folder and siblings', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Proyectos separados',
    ]);
    $project = $folder->projects()->create([
        'user_id' => $user->id,
        'snip' => '111222',
        'name' => 'Proyecto a eliminar',
        'place' => 'Coatepeque',
        'latitude' => 14.7,
        'longitude' => -91.86,
        'color' => '#e4a52c',
    ]);
    $sibling = $folder->projects()->create([
        'user_id' => $user->id,
        'snip' => '333444',
        'name' => 'Proyecto que permanece',
        'place' => 'Coatepeque',
        'latitude' => 14.71,
        'longitude' => -91.87,
        'color' => '#2a9fd6',
    ]);

    $this->actingAs($user)
        ->deleteJson(route('projects.destroy', $project))
        ->assertUnprocessable()
        ->assertJsonPath(
            'errors.code.0',
            'Escribe el código de seguridad.',
        );

    $this->actingAs($user)
        ->deleteJson(route('projects.destroy', $project), [
            'code' => '11111111',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    $this->assertDatabaseHas('projects', ['id' => $project->id]);

    $this->actingAs($user)
        ->deleteJson(route('projects.destroy', $project), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    $this->assertDatabaseHas('projects', ['id' => $sibling->id]);
    $this->assertDatabaseHas('project_folders', ['id' => $folder->id]);
});

test('any authenticated user can update and delete a shared project', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create([
        'password' => Hash::make('ClaveAjena1!'),
    ]);
    $folder = $owner->projectFolders()->create([
        'name' => 'Privada',
    ]);
    $project = $folder->projects()->create([
        'user_id' => $owner->id,
        'snip' => '987654',
        'name' => 'Proyecto privado',
        'place' => 'Coatepeque',
        'latitude' => 14.7,
        'longitude' => -91.86,
        'color' => '#e4a52c',
    ]);

    $this->actingAs($otherUser)
        ->patchJson(route('projects.update', $project), [
            'snip' => '987654',
            'name' => 'Proyecto compartido actualizado',
            'latitude' => 14.71,
            'longitude' => -91.87,
        ])
        ->assertOk();

    $this->actingAs($otherUser)
        ->deleteJson(route('projects.destroy', $project), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

test('deleting a folder with the deletion code also deletes its projects', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Temporal',
    ]);
    $project = $folder->projects()->create([
        'user_id' => $user->id,
        'name' => 'Proyecto temporal',
        'place' => 'Coatepeque',
        'latitude' => 14.7,
        'longitude' => -91.86,
        'color' => '#e4a52c',
    ]);

    $this->actingAs($user)
        ->deleteJson(route('project-folders.destroy', $folder), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});

test('a guest cannot create projects', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Protegida',
    ]);

    $this->postJson(route('projects.store', $folder), [
        'name' => 'Sin sesión',
        'latitude' => 14.7,
        'longitude' => -91.86,
    ])->assertUnauthorized();
});
