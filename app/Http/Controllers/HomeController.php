<?php

namespace App\Http\Controllers;

use App\Models\ProjectFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->project_workspace_initialized_at === null) {
            DB::transaction(function () use ($user): void {
                $lockedUser = $user->newQuery()
                    ->lockForUpdate()
                    ->findOrFail($user->id);

                if ($lockedUser->project_workspace_initialized_at !== null) {
                    return;
                }

                if (ProjectFolder::query()->doesntExist()) {
                    $primaryFolder = $lockedUser->projectFolders()->create([
                        'name' => 'Proyectos municipales',
                        'is_primary' => true,
                    ]);

                    $primaryFolder->projects()->create([
                        'user_id' => $lockedUser->id,
                        'name' => 'Mejoramiento de calle con pavimento',
                        'place' => 'Barrio La Esperanza',
                        'description' => 'Intervención vial registrada en la carpeta municipal.',
                        'latitude' => 14.7048,
                        'longitude' => -91.8718,
                        'color' => $primaryFolder->color,
                    ]);
                }

                $lockedUser->forceFill([
                    'project_workspace_initialized_at' => now(),
                ])->save();
            });
        }

        $projectFolders = ProjectFolder::query()
            ->with(['projects' => fn ($query) => $query
                ->orderBy('id')
                ->with([
                    'files' => fn ($fileQuery) => $fileQuery->orderBy('slot'),
                    'documentFolders',
                ])])
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();

        $mapProjects = [];

        foreach ($projectFolders as $folder) {
            foreach ($folder->projects as $index => $project) {
                $mapProjects[(string) $project->id] = [
                    'code' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'snip' => $project->snip,
                    'color' => $project->color,
                    'title' => $project->name,
                    'place' => $project->place,
                    'description' => $project->description ?? 'Proyecto municipal registrado.',
                    'coordinates' => [$project->latitude, $project->longitude],
                    'document_folders' => $project->documentFolders->map(
                        fn ($documentFolder) => [
                            'id' => $documentFolder->id,
                            'name' => $documentFolder->name,
                            'position' => $documentFolder->position,
                        ],
                    )->values()->all(),
                    'files' => $project->files->map(fn ($file) => [
                        'id' => $file->id,
                        'slot' => $file->slot,
                        'name' => $file->original_name,
                        'size' => $file->size,
                        'is_link' => $file->external_url !== null,
                        'preview_url' => route(
                            'project-files.preview',
                            [$project, $file],
                        ),
                        'download_url' => route(
                            'project-files.download',
                            [$project, $file],
                        ),
                    ])->values()->all(),
                ];
            }
        }

        return view('home', [
            'projectFolders' => $projectFolders,
            'mapProjects' => $mapProjects,
        ]);
    }
}
