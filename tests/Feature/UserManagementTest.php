<?php

use App\Mail\PasswordRecoveryCodeMail;
use App\Models\ProjectMonitoring;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set(
        'security.user_management_admin_email',
        'josueagueda360@gmail.com',
    );
});

function userManager(): User
{
    return User::factory()->create([
        'name' => 'Josué Agueda',
        'email' => 'josueagueda360@gmail.com',
    ]);
}

function validManagedUser(array $overrides = []): array
{
    return array_merge([
        'name' => 'María López',
        'email' => 'maria.lopez@muni.test',
        'password' => 'ClaveSegura!2026',
        'password_confirmation' => 'ClaveSegura!2026',
    ], $overrides);
}

test('only the configured administrator can open user management', function () {
    $this->get(route('user-management.index'))
        ->assertRedirect(route('login'));

    $ordinaryUser = User::factory()->create([
        'email' => 'otro@muni.test',
    ]);
    $this->actingAs($ordinaryUser)
        ->get(route('user-management.index'))
        ->assertForbidden();

    $this->actingAs(userManager())
        ->get(route('user-management.index'))
        ->assertOk()
        ->assertSee('Crear usuario')
        ->assertSee('Recuperación de contraseña disponible')
        ->assertSee('id="password-rules"', false)
        ->assertSee("event.data?.type === 'theme-changed'", false);
});

test('the users tab and iframe are rendered only for the administrator', function () {
    $ordinaryUser = User::factory()->create([
        'email' => 'empleado@muni.test',
    ]);

    $this->actingAs($ordinaryUser)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('data-module-target="users"', false)
        ->assertDontSee('id="user-management-frame"', false);

    $this->actingAs(userManager())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('data-module-target="users"', false)
        ->assertSee('id="user-management-frame"', false)
        ->assertSee(route('user-management.index', ['embed' => 1]), false)
        ->assertSee("['users', userManagementFrame]", false);
});

test('the administrator can create a user with a strong encrypted password', function () {
    $admin = userManager();

    $this->actingAs($admin)
        ->postJson(route('user-management.store'), validManagedUser())
        ->assertCreated()
        ->assertJsonPath('message', 'Usuario creado correctamente.')
        ->assertJsonPath('user.name', 'María López')
        ->assertJsonPath('user.email', 'maria.lopez@muni.test')
        ->assertJsonMissingPath('user.password');

    $created = User::query()
        ->where('email', 'maria.lopez@muni.test')
        ->firstOrFail();

    expect(Hash::check('ClaveSegura!2026', $created->password))->toBeTrue();
    expect(DB::table('password_histories')->where('user_id', $created->id)->exists())
        ->toBeTrue();
});

test('weak mismatched and duplicate credentials are rejected', function () {
    $admin = userManager();
    User::factory()->create(['email' => 'existente@muni.test']);

    $this->actingAs($admin)
        ->postJson(route('user-management.store'), validManagedUser([
            'email' => 'existente@muni.test',
            'password' => 'debil',
            'password_confirmation' => 'diferente',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('a non administrator cannot create users even with a direct request', function () {
    $ordinaryUser = User::factory()->create([
        'email' => 'empleado@muni.test',
    ]);

    $this->actingAs($ordinaryUser)
        ->postJson(route('user-management.store'), validManagedUser())
        ->assertForbidden();

    $this->assertDatabaseMissing('users', [
        'email' => 'maria.lopez@muni.test',
    ]);
});

test('the administrator can delete a user without deleting municipal records', function () {
    $admin = userManager();
    $managedUser = User::factory()->create([
        'email' => 'usuario.eliminable@muni.test',
    ]);
    $folder = $managedUser->projectFolders()->create([
        'name' => 'Proyectos conservados',
    ]);
    $project = $folder->projects()->create([
        'user_id' => $managedUser->id,
        'snip' => '654321',
        'name' => 'Proyecto que debe conservarse',
        'place' => 'Coatepeque',
        'latitude' => 14.7,
        'longitude' => -91.8,
        'color' => $folder->color,
    ]);
    $monitoring = new ProjectMonitoring;
    $monitoring->forceFill([
        'user_id' => $managedUser->id,
        'project_id' => $project->id,
        'project_name' => $project->name,
        'status' => 'in_progress',
        'category' => 'tender',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'location' => $project->place,
    ])->save();
    $movement = $project->financialMovements()->create([
        'recorded_by_user_id' => $managedUser->id,
        'occurred_on' => '2026-09-03',
        'movement_type' => 'expense',
        'category' => 'materials',
        'description' => 'Compra registrada antes de eliminar la cuenta.',
        'amount' => 125,
    ]);
    DB::table('password_reset_tokens')->insert([
        'email' => $managedUser->email,
        'token' => 'token',
        'created_at' => now(),
    ]);

    $this->actingAs($admin)
        ->deleteJson(route('user-management.destroy', $managedUser))
        ->assertOk()
        ->assertJsonPath('message', 'Usuario eliminado correctamente.');

    $this->assertDatabaseMissing('users', ['id' => $managedUser->id]);
    $this->assertDatabaseHas('project_folders', ['id' => $folder->id, 'user_id' => $admin->id]);
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'user_id' => $admin->id]);
    $this->assertDatabaseHas('project_monitorings', ['id' => $monitoring->id, 'user_id' => $admin->id]);
    $this->assertDatabaseHas('project_financial_movements', ['id' => $movement->id, 'recorded_by_user_id' => $admin->id]);
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $managedUser->email]);
});

test('the administrator account cannot be deleted', function () {
    $admin = userManager();

    $this->actingAs($admin)
        ->deleteJson(route('user-management.destroy', $admin))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'La cuenta del administrador principal no se puede eliminar.');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('a non administrator cannot delete users', function () {
    $ordinaryUser = User::factory()->create(['email' => 'empleado@muni.test']);
    $target = User::factory()->create(['email' => 'objetivo@muni.test']);

    $this->actingAs($ordinaryUser)
        ->deleteJson(route('user-management.destroy', $target))
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $target->id]);
});

test('a managed user can use the existing email recovery flow', function () {
    Mail::fake();
    $admin = userManager();
    $this->actingAs($admin)
        ->postJson(route('user-management.store'), validManagedUser())
        ->assertCreated();

    auth()->logout();

    $this->postJson(route('password.recovery.code'), [
        'email' => 'maria.lopez@muni.test',
    ])->assertOk()
        ->assertJsonPath('code_sent', true);

    Mail::assertSent(PasswordRecoveryCodeMail::class, function ($mail): bool {
        return $mail->hasTo('maria.lopez@muni.test');
    });
});
