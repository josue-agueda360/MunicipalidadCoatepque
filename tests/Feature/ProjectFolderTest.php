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

test('an authenticated user can create and see a project folder', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('project-folders.store'), [
            'name' => 'Puentes 2025',
        ])
        ->assertCreated()
        ->assertJsonPath('folder.name', 'Puentes 2025')
        ->assertJsonPath(
            'folder.color',
            fn (string $color): bool => preg_match(
                '/^#[0-9a-f]{6}$/i',
                $color,
            ) === 1,
        );

    $this->assertDatabaseHas('project_folders', [
        'user_id' => $user->id,
        'name' => 'Puentes 2025',
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Puentes 2025')
        ->assertSee('folder-add folder-add--new-folder', false)
        ->assertDontSee('id="folder-security-code"', false)
        ->assertSee('Código de seguridad')
        ->assertSee('minimizeOtherProjectFolders', false)
        ->assertSee("folderList.addEventListener('toggle'", false);
});

test('each project folder receives a different permanent color', function () {
    $user = User::factory()->create();
    $firstFolder = $user->projectFolders()->create(['name' => 'Carpeta uno']);
    $secondFolder = $user->projectFolders()->create(['name' => 'Carpeta dos']);
    $thirdFolder = $user->projectFolders()->create(['name' => 'Carpeta tres']);

    expect([$firstFolder->color, $secondFolder->color, $thirdFolder->color])
        ->each->toMatch('/^#[0-9a-f]{6}$/i')
        ->and(collect([
            $firstFolder->color,
            $secondFolder->color,
            $thirdFolder->color,
        ])->unique()->count())->toBe(3);
});

test('project folders are visible to every authenticated user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $owner->projectFolders()->create([
        'name' => 'Carpeta privada',
    ]);

    $this->actingAs($otherUser)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Carpeta privada');
});

test('duplicate and invalid project folder names are rejected', function () {
    $user = User::factory()->create();
    $user->projectFolders()->create([
        'name' => 'Calles',
    ]);

    $this->actingAs($user)
        ->postJson(route('project-folders.store'), [
            'name' => 'calles',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->actingAs($user)
        ->postJson(route('project-folders.store'), [
            'name' => 'Carpeta/inválida',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('a guest cannot create project folders', function () {
    $this->postJson(route('project-folders.store'), [
        'name' => 'Sin autorización',
    ])->assertUnauthorized();
});

test('a user can rename their folder without a security code', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Nombre anterior',
    ]);

    $this->actingAs($user)
        ->patchJson(route('project-folders.update', $folder), [
            'name' => 'Nombre nuevo',
        ])
        ->assertOk()
        ->assertJsonPath('folder.name', 'Nombre nuevo');

    $this->assertDatabaseHas('project_folders', [
        'id' => $folder->id,
        'name' => 'Nombre nuevo',
    ]);

    $this->actingAs($user)
        ->patchJson(route('project-folders.update', $folder), [
            'name' => 'Segundo cambio',
        ])
        ->assertOk()
        ->assertJsonPath('folder.name', 'Segundo cambio');
});

test('a user can delete their own project folder with the deletion code', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Carpeta temporal',
    ]);

    $this->actingAs($user)
        ->deleteJson(route('project-folders.destroy', $folder))
        ->assertUnprocessable()
        ->assertJsonPath(
            'errors.code.0',
            'Escribe el código de seguridad.',
        );

    $this->actingAs($user)
        ->deleteJson(route('project-folders.destroy', $folder), [
            'code' => '11111111',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');

    $this->assertDatabaseHas('project_folders', [
        'id' => $folder->id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('project-folders.destroy', $folder), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('project_folders', [
        'id' => $folder->id,
    ]);
});

test('any authenticated user can maintain a shared folder', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $folder = $owner->projectFolders()->create([
        'name' => 'Carpeta protegida',
    ]);

    $this->actingAs($otherUser)
        ->patchJson(route('project-folders.update', $folder), [
            'name' => 'Carpeta compartida',
        ])
        ->assertOk();

    $this->actingAs($otherUser)
        ->deleteJson(route('project-folders.destroy', $folder), [
            'code' => '59264018',
        ])
        ->assertOk();

    $this->assertDatabaseMissing('project_folders', ['id' => $folder->id]);
});
