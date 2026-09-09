<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocumentFolder;
use App\Support\DeletionCode;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectDocumentFolderController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->ensureProjectOwnership($request, $project);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail,
                ) use ($project): void {
                    $name = trim((string) $value);

                    if (
                        $name === ''
                        || preg_match('/[\x00-\x1F\x7F]/u', $name) === 1
                    ) {
                        $fail('Escribe un nombre válido para el documento.');

                        return;
                    }

                    if (
                        $project->documentFolders()
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                            ->exists()
                    ) {
                        $fail('Ya existe un documento con ese nombre en este proyecto.');
                    }
                },
            ],
        ], [
            'name.required' => 'Escribe un nombre para el documento.',
            'name.string' => 'El nombre del documento no es válido.',
            'name.max' => 'El nombre puede tener hasta 100 caracteres.',
        ]);

        $documentFolder = DB::transaction(function () use (
            $project,
            $data,
        ): ProjectDocumentFolder {
            $lockedProject = Project::query()
                ->whereKey($project->id)
                ->lockForUpdate()
                ->firstOrFail();
            $nextPosition = ((int) $lockedProject->documentFolders()
                ->max('position')) + 1;

            return $lockedProject->documentFolders()->create([
                'name' => trim($data['name']),
                'position' => $nextPosition,
            ]);
        });

        return response()->json([
            'message' => 'Documento añadido correctamente.',
            'document_folder' => [
                'id' => $documentFolder->id,
                'name' => $documentFolder->name,
                'position' => $documentFolder->position,
            ],
        ], 201);
    }

    public function destroy(
        Request $request,
        Project $project,
        ProjectDocumentFolder $projectDocumentFolder,
    ): JsonResponse {
        $this->ensureProjectOwnership($request, $project);
        abort_unless(
            $projectDocumentFolder->project_id === $project->id,
            404,
        );
        DeletionCode::validate($request);

        DB::transaction(function () use (
            $project,
            $projectDocumentFolder,
        ): void {
            $project->files()
                ->where('slot', $projectDocumentFolder->position)
                ->get()
                ->each
                ->delete();
            $projectDocumentFolder->delete();
        });

        return response()->json([
            'message' => 'Documento eliminado correctamente.',
        ]);
    }

    private function ensureProjectOwnership(
        Request $request,
        Project $project,
    ): void {
        // El espacio municipal es compartido entre todos los usuarios autenticados.
    }
}
