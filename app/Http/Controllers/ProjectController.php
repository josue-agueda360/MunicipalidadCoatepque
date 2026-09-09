<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFolder;
use App\Support\DeletionCode;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function store(
        Request $request,
        ProjectFolder $projectFolder,
    ): JsonResponse {
        $data = $this->validatedProjectData($request, $projectFolder);

        try {
            $project = $projectFolder->projects()->create([
                'user_id' => $request->user()->id,
                ...$data,
                'description' => 'Proyecto municipal registrado.',
            ]);
        } catch (UniqueConstraintViolationException) {
            $this->throwSnipConflict();
        }

        return response()->json([
            'message' => 'Proyecto creado correctamente.',
            'project' => $this->projectPayload($project),
        ], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $this->validatedProjectData(
            $request,
            $project->folder,
            $project,
        );

        try {
            $project->update($data);
        } catch (UniqueConstraintViolationException) {
            $this->throwSnipConflict();
        }

        return response()->json([
            'message' => 'Proyecto actualizado correctamente.',
            'project' => $this->projectPayload($project),
        ]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        DeletionCode::validate($request);
        $project->delete();

        return response()->json([
            'message' => 'Proyecto eliminado correctamente.',
        ]);
    }

    /**
     * @return array{
     *     snip: string,
     *     name: string,
     *     place: string,
     *     latitude: float|int|string,
     *     longitude: float|int|string
     * }
     */
    private function validatedProjectData(
        Request $request,
        ProjectFolder $projectFolder,
        ?Project $ignoredProject = null,
    ): array {
        $data = $request->validate([
            'snip' => [
                'required',
                'string',
                'regex:/^\d{1,6}$/',
                Rule::unique('projects', 'snip')->ignore($ignoredProject?->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail,
                ) use ($projectFolder, $ignoredProject): void {
                    $name = trim((string) $value);

                    if ($name === '' || preg_match('/[\x00-\x1F]/u', $name) === 1) {
                        $fail('Escribe un nombre válido para el proyecto.');

                        return;
                    }

                    $query = $projectFolder->projects()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);

                    if ($ignoredProject !== null) {
                        $query->whereKeyNot($ignoredProject->id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe un proyecto con ese nombre en esta carpeta.');
                    }
                },
            ],
            'place' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'snip.required' => 'Escribe el código SNIP.',
            'snip.string' => 'El SNIP debe escribirse con números.',
            'snip.regex' => 'El SNIP solo puede contener números y un máximo de 6 dígitos.',
            'snip.unique' => 'Ese SNIP ya está creado.',
            'name.required' => 'Escribe un nombre para el proyecto.',
            'name.string' => 'El nombre del proyecto no es válido.',
            'name.max' => 'El nombre puede tener hasta 150 caracteres.',
            'place.string' => 'El lugar o sector no es válido.',
            'place.max' => 'La ubicación puede tener hasta 100 caracteres.',
            'latitude.required' => 'Escribe la latitud del proyecto.',
            'latitude.numeric' => 'La latitud debe ser un número válido.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.required' => 'Escribe la longitud del proyecto.',
            'longitude.numeric' => 'La longitud debe ser un número válido.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
        ]);

        return [
            'snip' => $data['snip'],
            'name' => trim($data['name']),
            'place' => trim($data['place'] ?? '') ?: 'Coatepeque',
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ];
    }

    /** @return array{id: int, folder_id: int, snip: ?string, name: string, place: string, color: string} */
    private function projectPayload(Project $project): array
    {
        return [
            'id' => $project->id,
            'folder_id' => $project->project_folder_id,
            'snip' => $project->snip,
            'name' => $project->name,
            'place' => $project->place,
            'color' => $project->color,
        ];
    }

    private function throwSnipConflict(): never
    {
        throw ValidationException::withMessages([
            'snip' => 'Ese SNIP ya está creado.',
        ]);
    }
}
