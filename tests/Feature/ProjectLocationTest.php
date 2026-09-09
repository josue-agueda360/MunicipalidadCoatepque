<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the project location module is protected by authentication', function () {
    $this->get(route('project-locations'))
        ->assertRedirect(route('login'));
});

test('an authenticated user can open the project location module', function () {
    $user = User::factory()->create();
    $folder = $user->projectFolders()->create([
        'name' => 'Obras privadas',
    ]);
    $folder->projects()->create([
        'user_id' => $user->id,
        'snip' => '456123',
        'name' => 'Mejoramiento vial del centro',
        'place' => 'Zona central',
        'latitude' => 14.7048,
        'longitude' => -91.8718,
        'color' => '#e4a52c',
    ]);

    $this->actingAs($user)
        ->get(route('project-locations'))
        ->assertOk()
        ->assertSee('Ubicación de Proyectos')
        ->assertSee('id="project-location-search"', false)
        ->assertSee('456123-Mejoramiento vial del centro')
        ->assertSee('navigator.geolocation.getCurrentPosition', false)
        ->assertSee('router.project-osrm.org/route/v1/driving', false)
        ->assertSee('https://www.google.com/maps/dir/', false)
        ->assertSee('17_500_000', false)
        ->assertSee('const centerSelectedProject = (project)', false)
        ->assertSee('centerSelectedProject(project);', false)
        ->assertSee('pitch: Cesium.Math.toRadians(-90)', false)
        ->assertSee('municipal_portal_theme', false)
        ->assertSee("event.data?.type === 'theme-changed'", false)
        ->assertSee('html.light-mode .project-finder', false)
        ->assertSee('html.light-mode .finder-search input', false)
        ->assertSee('background: #f8fafb', false)
        ->assertSee('municipal-coatepeque-brand-light.png', false)
        ->assertSee('class="brand__wordmark"', false)
        ->assertDontSee('<a class="brand"', false)
        ->assertSee('Regresar para buscar otro')
        ->assertSee('data-protected-navigation', false)
        ->assertDontSee('Obras privadas')
        ->assertDontSee('Añadir documento');
});

test('the project location module can render inside the home workspace', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('project-locations', ['embed' => 1]))
        ->assertOk()
        ->assertSee('class="is-embedded"', false)
        ->assertSee('id="project-location-map"', false)
        ->assertDontSee('class="portal-header"', false);
});

test('the project location module exposes the shared municipal projects', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownerFolder = $owner->projectFolders()->create([
        'name' => 'Carpeta del propietario',
    ]);
    $otherFolder = $otherUser->projectFolders()->create([
        'name' => 'Carpeta ajena',
    ]);
    $ownerFolder->projects()->create([
        'user_id' => $owner->id,
        'snip' => '101010',
        'name' => 'Proyecto visible',
        'place' => 'Coatepeque',
        'latitude' => 14.70,
        'longitude' => -91.87,
        'color' => '#e4a52c',
    ]);
    $otherFolder->projects()->create([
        'user_id' => $otherUser->id,
        'snip' => '909090',
        'name' => 'Proyecto ajeno',
        'place' => 'Otro lugar',
        'latitude' => 14.71,
        'longitude' => -91.88,
        'color' => '#2a9fd6',
    ]);

    $this->actingAs($owner)
        ->get(route('project-locations'))
        ->assertOk()
        ->assertSee('101010-Proyecto visible')
        ->assertSee('909090-Proyecto ajeno');
});

test('the home navigation contains the project location module', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('project-locations'), false)
        ->assertSee('Ubicación de Proyectos')
        ->assertSee('data-module-target="locations"', false)
        ->assertSee('id="project-locations-frame"', false)
        ->assertSee('id="theme-toggle"', false)
        ->assertSee('theme-toggle__icon--sun', false)
        ->assertSee('theme-toggle__icon--moon', false)
        ->assertSee('municipal_portal_theme', false)
        ->assertSee("{ type: 'theme-changed', theme }", false)
        ->assertSee('html.light-mode .places-panel', false)
        ->assertSee('municipal-coatepeque-brand-light.png', false)
        ->assertSee('Gobierno Municipal')
        ->assertDontSee('<a class="brand"', false)
        ->assertSee('data-module-panel="home"', false)
        ->assertSee('class="header-center"', false)
        ->assertSee('showModule', false)
        ->assertSee('home-module-shown', false);
});
