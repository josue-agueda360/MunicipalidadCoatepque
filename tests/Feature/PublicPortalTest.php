<?php

use App\Models\ProjectMonitoring;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('same-origin frames are allowed for embedded application modules', function () {
    $this->get(route('public.portal'))
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

function publicPortalProject(User $user): \App\Models\Project
{
    $folder = $user->projectFolders()->create([
        'name' => 'Obras municipales públicas',
    ]);

    return $folder->projects()->create([
        'user_id' => $user->id,
        'snip' => '458965',
        'name' => 'Proyecto municipal publicado',
        'place' => 'Barrio La Esperanza',
        'latitude' => 14.7048,
        'longitude' => -91.8718,
        'color' => $folder->color,
    ]);
}

test('the enter button opens a public portal without home or user management', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Ingresa')
        ->assertSee('href="'.route('public.portal').'"', false);

    $this->get(route('public.portal'))
        ->assertOk()
        ->assertSee('Consulta pública')
        ->assertSee('Ubicación de proyectos')
        ->assertSee('Monitoreo')
        ->assertSee('Control financiero')
        ->assertDontSee('data-module-target="home"', false)
        ->assertDontSee('data-module-target="users"', false)
        ->assertSee(route('public.project-locations', ['embed' => 1]), false)
        ->assertSee(route('public.project-monitoring.index', ['embed' => 1]), false)
        ->assertSee(route('public.project-finance.index', ['embed' => 1]), false);
});

test('guests can read project location monitoring and finance information', function () {
    $user = User::factory()->create();
    $project = publicPortalProject($user);
    (new ProjectMonitoring)->forceFill([
        'user_id' => $user->id,
        'project_id' => $project->id,
        'project_name' => $project->name,
        'status' => 'in_progress',
        'category' => 'tender',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'location' => $project->place,
        'description' => 'Información pública del monitoreo.',
    ])->save();
    $project->financialProfile()->create([
        'allocated_budget' => 1500000,
        'physical_progress' => 45,
    ]);

    $this->get(route('public.project-locations', ['embed' => 1]))
        ->assertOk()
        ->assertSee('458965-Proyecto municipal publicado')
        ->assertSee('data-public-read-only="true"', false);

    $this->get(route('public.project-monitoring.index', ['embed' => 1]))
        ->assertOk()
        ->assertSee('Proyecto municipal publicado')
        ->assertSee('Información pública del monitoreo.')
        ->assertSee('is-read-only', false)
        ->assertSee('disabled', false);

    $this->get(route('public.project-finance.index', ['embed' => 1]))
        ->assertOk()
        ->assertSee('Proyecto municipal publicado')
        ->assertSee('1500000', false)
        ->assertSee('is-read-only', false)
        ->assertDontSee('id="funding-form"', false)
        ->assertDontSee('<form class="required-document-form"', false)
        ->assertDontSee('Añadir fuente')
        ->assertDontSee('Subir contrato')
        ->assertSee('Consulta el presupuesto, la ejecución y los respaldos publicados.');
});

test('guests cannot modify shared municipal information', function () {
    $user = User::factory()->create();
    $project = publicPortalProject($user);

    $this->postJson(route('project-folders.store'), ['name' => 'No permitido'])
        ->assertUnauthorized();
    $this->patchJson(route('projects.update', $project), [
        'snip' => '458965',
        'name' => 'Cambio no permitido',
        'latitude' => 14.7,
        'longitude' => -91.8,
    ])->assertUnauthorized();
    $this->putJson(route('project-finance.profile.update', $project), [
        'allocated_budget' => 1,
        'physical_progress' => 1,
    ])->assertUnauthorized();

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Proyecto municipal publicado',
    ]);
});
