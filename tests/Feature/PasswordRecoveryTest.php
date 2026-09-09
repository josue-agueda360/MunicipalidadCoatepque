<?php

use App\Mail\PasswordRecoveryCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('an unregistered email does not receive a recovery code', function () {
    Mail::fake();

    $this->postJson(route('password.recovery.code'), [
        'email' => 'no-registrado@example.com',
    ])
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'Este correo no está registrado.',
            'registered' => false,
            'code_sent' => false,
        ]);

    Mail::assertNothingSent();
    $this->assertDatabaseCount('password_reset_tokens', 0);
});

test('a registered user receives a hashed six digit recovery code', function () {
    Mail::fake();
    $user = User::factory()->create([
        'email' => 'persona@example.com',
    ]);
    $sentCode = null;

    $this->postJson(route('password.recovery.code'), [
        'email' => 'PERSONA@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Código enviado correctamente.')
        ->assertJsonPath('masked_email', 'pe•••••@example.com')
        ->assertJsonPath('registered', true)
        ->assertJsonPath('code_sent', true)
        ->assertSessionHas('password_recovery.email', $user->email);

    Mail::assertSent(
        PasswordRecoveryCodeMail::class,
        function (PasswordRecoveryCodeMail $mail) use ($user, &$sentCode) {
            $sentCode = $mail->code;

            return $mail->hasTo($user->email);
        },
    );

    expect($sentCode)->toMatch('/^\d{6}$/');

    $reset = DB::table('password_reset_tokens')
        ->where('email', $user->email)
        ->first();

    expect($reset)->not->toBeNull()
        ->and(Hash::check($sentCode, $reset->token))->toBeTrue();
});

test('the recovery code can be verified once', function () {
    Mail::fake();
    $user = User::factory()->create();
    $sentCode = null;

    $this->postJson(route('password.recovery.code'), [
        'email' => $user->email,
    ])->assertOk();

    Mail::assertSent(
        PasswordRecoveryCodeMail::class,
        function (PasswordRecoveryCodeMail $mail) use (&$sentCode) {
            $sentCode = $mail->code;

            return true;
        },
    );

    $this->postJson(route('password.recovery.verify'), [
        'code' => $sentCode,
    ])
        ->assertOk()
        ->assertJson([
            'message' => 'Código verificado correctamente.',
        ])
        ->assertSessionHas(
            'password_recovery_verified.user_id',
            $user->id,
        );

    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => $user->email,
    ]);
});

test('an expired recovery code is rejected', function () {
    $user = User::factory()->create();

    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make('123456'),
        'created_at' => now()->subMinutes(11),
    ]);

    $this->withSession([
        'password_recovery.email' => $user->email,
        'password_recovery.attempts' => 0,
    ])->postJson(route('password.recovery.verify'), [
        'code' => '123456',
    ])
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'El código venció. Solicita uno nuevo.',
        ]);

    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => $user->email,
    ]);
});

test('password reset requires a verified recovery code', function () {
    $this->postJson(route('password.recovery.reset'), [
        'password' => 'NuevaClaveSegura!360',
        'password_confirmation' => 'NuevaClaveSegura!360',
    ])
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'La autorización venció. Solicita un código nuevo.',
        ]);
});

test('a weak or mismatched password is rejected', function () {
    $user = User::factory()->create();

    $this->withSession([
        'password_recovery_verified.user_id' => $user->id,
        'password_recovery_verified.expires_at' => now()
            ->addMinutes(10)
            ->timestamp,
    ])->postJson(route('password.recovery.reset'), [
        'password' => 'debil',
        'password_confirmation' => 'diferente',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('a verified user can set a strong new password', function () {
    $previousPassword = 'ClaveAnterior!360';
    $user = User::factory()->create([
        'password' => Hash::make($previousPassword),
    ]);
    $user->forceFill([
        'active_session_token' => hash('sha256', 'sesion-activa'),
        'active_session_last_seen_at' => now(),
    ])->save();

    $this->withSession([
        'password_recovery_verified.user_id' => $user->id,
        'password_recovery_verified.expires_at' => now()
            ->addMinutes(10)
            ->timestamp,
    ])->postJson(route('password.recovery.reset'), [
        'password' => 'Aa1!',
        'password_confirmation' => 'Aa1!',
    ])
        ->assertOk()
        ->assertJson([
            'message' => 'Contraseña actualizada correctamente.',
        ])
        ->assertSessionMissing('password_recovery_verified');

    $user->refresh();

    expect(Hash::check('Aa1!', $user->password))->toBeTrue()
        ->and($user->active_session_token)->toBeNull()
        ->and($user->active_session_last_seen_at)->toBeNull();

    $storedHistory = DB::table('password_histories')
        ->where('user_id', $user->id)
        ->pluck('password_hash');

    expect($storedHistory->contains(
        fn (string $hash): bool => Hash::check(
            $previousPassword,
            $hash,
        ),
    ))->toBeTrue();
});

test('the current password cannot be reused', function () {
    $currentPassword = 'ClaveActualSegura!360';
    $user = User::factory()->create([
        'password' => Hash::make($currentPassword),
    ]);

    $this->withSession([
        'password_recovery_verified.user_id' => $user->id,
        'password_recovery_verified.expires_at' => now()
            ->addMinutes(10)
            ->timestamp,
    ])->postJson(route('password.recovery.reset'), [
        'password' => $currentPassword,
        'password_confirmation' => $currentPassword,
    ])
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'Esta contraseña ya fue utilizada. Elige una diferente.',
        ]);
});

test('a password from the user history cannot be reused', function () {
    $currentPassword = 'ClaveActual!360';
    $previousPassword = 'ClaveAnterior!360';
    $user = User::factory()->create([
        'password' => Hash::make($currentPassword),
    ]);

    DB::table('password_histories')->insert([
        'user_id' => $user->id,
        'password_hash' => Hash::make($previousPassword),
        'created_at' => now(),
    ]);

    $this->withSession([
        'password_recovery_verified.user_id' => $user->id,
        'password_recovery_verified.expires_at' => now()
            ->addMinutes(10)
            ->timestamp,
    ])->postJson(route('password.recovery.reset'), [
        'password' => $previousPassword,
        'password_confirmation' => $previousPassword,
    ])
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'Esta contraseña ya fue utilizada. Elige una diferente.',
        ]);

    expect(Hash::check(
        $currentPassword,
        $user->fresh()->password,
    ))->toBeTrue();
});
