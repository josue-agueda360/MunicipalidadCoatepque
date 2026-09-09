<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a user can sign in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'persona@example.com',
        'password' => Hash::make('Agueda360'),
    ]);

    $this->postJson(route('login.store'), [
        'email' => 'PERSONA@example.com',
        'password' => 'Agueda360',
    ])
        ->assertOk()
        ->assertJson([
            'message' => 'Acceso correcto.',
            'redirect_url' => route('home'),
        ]);

    $this->assertAuthenticatedAs($user);

    $sessionToken = session('active_session_token');

    expect($sessionToken)->toBeString()
        ->and(hash('sha256', $sessionToken))
        ->toBe($user->refresh()->active_session_token)
        ->and($user->active_session_last_seen_at)
        ->not->toBeNull();
});

test('an active account requires a password confirmed session takeover', function () {
    $activeTokenHash = hash('sha256', 'sesion-anterior');
    $user = User::factory()->create([
        'email' => 'persona@example.com',
        'password' => Hash::make('Agueda360'),
    ]);
    $user->forceFill([
        'active_session_token' => $activeTokenHash,
        'active_session_last_seen_at' => now(),
    ])->save();

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Agueda360',
    ])
        ->assertStatus(409)
        ->assertJson([
            'requires_takeover' => true,
            'message' => 'Esta cuenta ya está abierta en otro lugar. ¿Quieres abrirla aquí?',
        ]);

    $this->assertGuest();

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Incorrecta',
        'takeover' => true,
    ])->assertUnprocessable();

    expect($user->refresh()->active_session_token)->toBe($activeTokenHash);

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Agueda360',
        'takeover' => true,
    ])
        ->assertOk()
        ->assertJsonPath('redirect_url', route('home'));

    $this->assertAuthenticatedAs($user);

    $newSessionToken = session('active_session_token');

    expect($newSessionToken)->toBeString()
        ->and(hash('sha256', $newSessionToken))
        ->toBe($user->refresh()->active_session_token)
        ->and($user->active_session_token)
        ->not->toBe($activeTokenHash);
});

test('a replaced session is expelled on its next request', function () {
    $user = User::factory()->create();
    $user->forceFill([
        'active_session_token' => hash('sha256', 'sesion-nueva'),
        'active_session_last_seen_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['active_session_token' => 'sesion-anterior'])
        ->get(route('home'))
        ->assertRedirect(route('login', [
            'sesion' => 'reemplazada',
        ]));

    $this->assertGuest();
});

test('a replaced session activity check tells the open page to close', function () {
    $user = User::factory()->create();
    $user->forceFill([
        'active_session_token' => hash('sha256', 'sesion-nueva'),
        'active_session_last_seen_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['active_session_token' => 'sesion-anterior'])
        ->postJson(route('session.activity'))
        ->assertUnauthorized()
        ->assertJson([
            'session_replaced' => true,
            'message' => 'Esta sesión se cerró porque la cuenta se abrió en otro lugar.',
        ]);

    $this->assertGuest();
});

test('the login view includes the whatsapp style session takeover flow', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Esta cuenta ya está abierta en otro lugar.')
        ->assertSee('¿Quieres abrirla aquí?')
        ->assertSee('Sí, abrir aquí')
        ->assertSee('id="session-takeover-password"', false)
        ->assertSee('takeover: true', false)
        ->assertSee('Por seguridad, escribe la contraseña manualmente.');
});

test('login and protected pages cannot be restored as an authenticated history page', function () {
    $loginResponse = $this->get(route('login'))
        ->assertOk()
        ->assertSee('data-logout-url', false)
        ->assertSee('protected-session-closed')
        ->assertSee('protected-tab-authorized')
        ->assertSee('protected-reload-pending')
        ->assertSee('resetPageFromHistory')
        ->assertSee('historyLogoutPromise')
        ->assertSee("sessionStorage.setItem(tabAuthorizationKey, 'true')", false);

    expect($loginResponse->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->and($loginResponse->headers->get('Pragma'))
        ->toBe('no-cache');

    $user = User::factory()->create();
    $homeResponse = $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('protected-tab-authorized')
        ->assertSee('protected-reload-pending')
        ->assertSee("protectedNavigation?.type === 'reload'", false)
        ->assertSee('validateRestoredSession')
        ->assertSee('markSessionClosed')
        ->assertSee('sessionStorage.removeItem(tabAuthorizationKey)', false)
        ->assertSee("window.addEventListener('pagehide'", false)
        ->assertSee("window.addEventListener('pageshow'", false);

    expect($homeResponse->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->and($homeResponse->headers->get('Pragma'))
        ->toBe('no-cache');
});

test('invalid credentials are rejected', function () {
    $user = User::factory()->create([
        'email' => 'persona@example.com',
        'password' => Hash::make('Agueda360'),
    ]);

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Incorrecta',
    ])
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'Correo o contraseña incorrectos.',
            'remaining_attempts' => 4,
        ]);

    expect($user->refresh()->failed_login_attempts)->toBe(1);
    $this->assertGuest();
});

test('a user is locked for fifteen minutes after five failed attempts', function () {
    $user = User::factory()->create([
        'email' => 'persona@example.com',
        'password' => Hash::make('Agueda360'),
    ]);

    foreach (range(1, 4) as $attempt) {
        $this->postJson(route('login.store'), [
            'email' => 'persona@example.com',
            'password' => 'Incorrecta',
        ])
            ->assertUnprocessable()
            ->assertJson([
                'remaining_attempts' => 5 - $attempt,
            ]);
    }

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Incorrecta',
    ])
        ->assertStatus(429)
        ->assertJson([
            'message' => 'Demasiados intentos fallidos. El acceso está bloqueado durante 15 minutos.',
            'retry_after_seconds' => 900,
        ]);

    $user->refresh();

    expect($user->failed_login_attempts)->toBe(5)
        ->and($user->locked_until)->not->toBeNull();

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Agueda360',
    ])->assertStatus(429);

    $this->assertGuest();

    $this->travel(16)->minutes();

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Agueda360',
    ])->assertOk();

    $user->refresh();

    expect($user->failed_login_attempts)->toBe(0)
        ->and($user->locked_until)->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('changing to valid credentials does not bypass an origin lock', function () {
    $user = User::factory()->create([
        'email' => 'persona@example.com',
        'password' => Hash::make('Agueda360'),
    ]);

    foreach ([
        'equivocado1@example.com',
        'equivocado2@example.com',
        'equivocado3@example.com',
        'equivocado4@example.com',
        'equivocado5@example.com',
    ] as $index => $email) {
        $response = $this->postJson(route('login.store'), [
            'email' => $email,
            'password' => 'Incorrecta',
        ]);

        if ($index < 4) {
            $response->assertUnprocessable();
        } else {
            $response->assertStatus(429);
        }
    }

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Agueda360',
    ])->assertStatus(429);

    $this->assertGuest();

    $this->travel(16)->minutes();

    $this->postJson(route('login.store'), [
        'email' => 'persona@example.com',
        'password' => 'Agueda360',
    ])->assertOk();

    $this->assertAuthenticatedAs($user);
});

test('direct links cannot bypass the authentication filter', function () {
    foreach (['/inicio', '/inicio?modulo=dependencias'] as $url) {
        $this->get($url)
            ->assertRedirect(route('login'));
    }

    $this->assertGuest();
});

test('session activity cannot be reported without authentication', function () {
    $this->post(route('session.activity'))
        ->assertRedirect(route('login'));
});

test('an authenticated session can report activity', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('session.activity'))
        ->assertNoContent();

    expect((int) config('session.lifetime'))->toBe(20);
});

test('an authenticated user can see the geographic projects module', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Lugares y proyectos')
        ->assertSee('Crear carpeta nueva')
        ->assertSee('Cambiar nombre')
        ->assertDontSee('Confirmar código')
        ->assertSee('Eliminar carpeta')
        ->assertDontSee('rename-password-dialog')
        ->assertSee('data-project-folders-url', false)
        ->assertSee('Proyectos municipales')
        ->assertSee('Mejoramiento de calle con pavimento')
        ->assertDontSee('Prevención de inundaciones')
        ->assertDontSee('Ampliación del sistema de agua potable')
        ->assertDontSee('Rehabilitación de cancha comunitaria')
        ->assertDontSee('Mejoramiento de alumbrado público')
        ->assertDontSee('2024')
        ->assertSee('project-map')
        ->assertSee('Globo geográfico municipal 3D')
        ->assertSee('Fuentes')
        ->assertSee('creditContainer')
        ->assertSee('Límites y nombres')
        ->assertSee('Carreteras')
        ->assertSee('World_Boundaries_and_Places')
        ->assertSee('World_Transportation')
        ->assertSee('ArcGisMapServerImageryProvider.fromUrl')
        ->assertSee('ImageryLayer.fromProviderAsync')
        ->assertSee('Cesium.HeightReference.CLAMP_TO_GROUND')
        ->assertSee('flyToBoundingSphere')
        ->assertSee('HeadingPitchRange')
        ->assertSee('projectArrivalTolerance')
        ->assertSee('activeProjectId')
        ->assertSee('Cesium.Cartesian3.clone')
        ->assertSee('keepGlobeVisible')
        ->assertSee('globeCenteringStartAltitude')
        ->assertSee('globeCenteringAltitude')
        ->assertSee('minimumGlobeAlignment')
        ->assertSee('cameraController.enableTilt = centeringProgress < 0.98', false)
        ->assertSee('correctionAmount')
        ->assertSee('scene.postRender.addEventListener(keepGlobeVisible)', false)
        ->assertSee('cameraController.enableLook = false', false)
        ->assertSee('cameraController.zoomEventTypes', false)
        ->assertSee('cameraController.tiltEventTypes', false)
        ->assertSee('Cesium.CameraEventType.RIGHT_DRAG', false)
        ->assertSee('maximumTiltAngle')
        ->assertSee('maximumGlobeAltitude')
        ->assertSee('cesium@1.143.0')
        ->assertSee('Cerrar sesión')
        ->assertDontSee('Tu Muni')
        ->assertDontSee('Dependencias')
        ->assertDontSee('Tu Ciudad')
        ->assertDontSee('Historia')
        ->assertDontSee('Información Pública')
        ->assertSee('images/municipal-coatepeque-header.png');
});

test('an authenticated user can sign out and return to login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $this->postJson(route('session.activity'))->assertUnauthorized();
});
