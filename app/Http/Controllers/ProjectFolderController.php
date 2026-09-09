<?php

namespace App\Http\Controllers;

use App\Models\ProjectFolder;
use App\Support\DeletionCode;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectFolderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $name = $this->validatedName($request);
        $user = $request->user();

        $folder = $user->projectFolders()->create([
            'name' => $name,
        ]);

        return response()->json([
            'message' => 'Carpeta creada correctamente.',
            'folder' => [
                'id' => $folder->id,
                'name' => $folder->name,
                'color' => $folder->color,
            ],
        ], 201);
    }

    public function update(
        Request $request,
        ProjectFolder $projectFolder,
    ): JsonResponse {
        $name = $this->validatedName($request, $projectFolder->id);

        $projectFolder->update([
            'name' => $name,
        ]);

        return response()->json([
            'message' => 'Nombre actualizado correctamente.',
            'folder' => [
                'id' => $projectFolder->id,
                'name' => $projectFolder->name,
            ],
        ]);
    }

    public function destroy(
        Request $request,
        ProjectFolder $projectFolder,
    ): JsonResponse {
        DeletionCode::validate($request);
        $projectFolder->delete();

        return response()->json([
            'message' => 'Carpeta eliminada correctamente.',
        ]);
    }

    private function validatedName(
        Request $request,
        ?int $ignoredFolderId = null,
    ): string {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail,
                ) use ($ignoredFolderId): void {
                    $name = trim((string) $value);

                    if (in_array($name, ['.', '..'], true)) {
                        $fail('Escribe un nombre válido para la carpeta.');

                        return;
                    }

                    if (
                        strpbrk($name, '\\/:*?"<>|') !== false
                        || preg_match('/[\x00-\x1F]/u', $name) === 1
                    ) {
                        $fail('El nombre contiene un carácter no permitido.');

                        return;
                    }

                    if (preg_match('/[. ]$/u', (string) $value) === 1) {
                        $fail('El nombre no puede terminar con un punto o espacio.');

                        return;
                    }

                    $query = ProjectFolder::query()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);

                    if ($ignoredFolderId !== null) {
                        $query->whereKeyNot($ignoredFolderId);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe una carpeta con ese nombre.');
                    }
                },
            ],
        ], [
            'name.required' => 'Escribe un nombre para la carpeta.',
            'name.string' => 'El nombre de la carpeta no es válido.',
            'name.max' => 'El nombre puede tener hasta 80 caracteres.',
        ]);

        return trim($data['name']);
    }
}
