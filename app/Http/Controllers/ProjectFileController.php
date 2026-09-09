<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Support\DeletionCode;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProjectFileController extends Controller
{
    public function store(
        Request $request,
        Project $project,
        string $slot,
    ): JsonResponse {
        $this->ensureProjectOwnership($request, $project);

        if (! preg_match('/^\d{1,6}$/', (string) $project->snip)) {
            throw ValidationException::withMessages([
                'file' => 'El proyecto necesita un código SNIP válido antes de subir archivos.',
            ]);
        }

        $slotNumber = $this->validatedSlot($slot);

        abort_unless(
            $project->documentFolders()
                ->where('position', $slotNumber)
                ->exists(),
            404,
        );

        if ($project->files()->where('slot', $slotNumber)->exists()) {
            throw ValidationException::withMessages([
                'file' => 'Esta carpeta ya tiene un archivo.',
            ]);
        }

        $data = $request->validate([
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip',
            ],
        ], [
            'file.required' => 'Selecciona un archivo.',
            'file.file' => 'El archivo seleccionado no es válido.',
            'file.max' => 'El archivo no puede superar 50 MB.',
            'file.mimes' => 'Puedes subir PDF, Word, Excel, imágenes JPG o PNG y archivos ZIP.',
        ]);

        $uploadedFile = $data['file'];
        $originalName = basename(str_replace(
            '\\',
            '/',
            $uploadedFile->getClientOriginalName(),
        ));
        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName);
        $originalName = mb_substr(trim((string) $originalName), 0, 255);

        if ($originalName === '') {
            $originalName = 'archivo.'.$uploadedFile->extension();
        }

        $disk = (string) config('filesystems.project_files_disk', 'local');
        $prefix = trim(
            (string) config(
                'filesystems.project_files_prefix',
                'project-files',
            ),
            '/',
        );
        $directory = "{$prefix}/{$project->snip}";
        $storageName = $this->availableStorageName(
            $disk,
            $directory,
            $originalName,
        );
        $path = $uploadedFile->storeAs(
            $directory,
            $storageName,
            $disk,
        );

        abort_if($path === false, 500, 'No se pudo guardar el archivo.');

        try {
            $projectFile = $project->files()->create([
                'slot' => $slotNumber,
                'original_name' => $originalName,
                'path' => $path,
                'disk' => $disk,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
            ]);
        } catch (UniqueConstraintViolationException) {
            Storage::disk($disk)->delete($path);

            throw ValidationException::withMessages([
                'file' => 'Esta carpeta ya tiene un archivo.',
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        return response()->json([
            'message' => 'Archivo guardado correctamente.',
            'file' => $this->filePayload($project, $projectFile),
        ], 201);
    }

    public function storeLink(
        Request $request,
        Project $project,
        string $slot,
    ): JsonResponse {
        $this->ensureProjectOwnership($request, $project);
        $slotNumber = $this->validatedSlot($slot);

        abort_unless(
            $project->documentFolders()
                ->where('position', $slotNumber)
                ->exists(),
            404,
        );

        if ($project->files()->where('slot', $slotNumber)->exists()) {
            throw ValidationException::withMessages([
                'url' => 'Esta carpeta ya tiene un archivo o enlace.',
            ]);
        }

        $data = $request->validate([
            'url' => [
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $url = trim((string) $value);
                    $parts = parse_url($url);
                    $host = strtolower((string) ($parts['host'] ?? ''));

                    if (
                        filter_var($url, FILTER_VALIDATE_URL) === false
                        || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
                        || $host !== 'drive.google.com'
                    ) {
                        $fail('Pega un enlace válido y seguro de Google Drive.');
                    }
                },
            ],
        ], [
            'url.required' => 'Pega el enlace de Google Drive.',
            'url.string' => 'El enlace de Google Drive no es válido.',
            'url.max' => 'El enlace es demasiado largo.',
        ]);

        try {
            $projectFile = $project->files()->create([
                'slot' => $slotNumber,
                'original_name' => 'Documento de Google Drive',
                'path' => null,
                'disk' => null,
                'mime_type' => 'text/uri-list',
                'size' => null,
                'external_url' => trim($data['url']),
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'url' => 'Esta carpeta ya tiene un archivo o enlace.',
            ]);
        }

        return response()->json([
            'message' => 'Enlace guardado correctamente.',
            'file' => $this->filePayload($project, $projectFile),
        ], 201);
    }

    public function download(
        Request $request,
        Project $project,
        ProjectFile $projectFile,
    ): StreamedResponse|RedirectResponse {
        $this->ensureFileOwnership($request, $project, $projectFile);

        if ($projectFile->external_url !== null) {
            return redirect()->away(
                $this->googleDriveDownloadUrl($projectFile->external_url),
            );
        }

        abort_unless(
            Storage::disk($projectFile->disk)->exists($projectFile->path),
            404,
            'El archivo ya no está disponible.',
        );

        return Storage::disk($projectFile->disk)->download(
            $projectFile->path,
            $projectFile->original_name,
        );
    }

    public function preview(
        Request $request,
        Project $project,
        ProjectFile $projectFile,
    ): StreamedResponse|RedirectResponse {
        $this->ensureFileOwnership($request, $project, $projectFile);

        if ($projectFile->external_url !== null) {
            return redirect()->away($projectFile->external_url);
        }

        abort_unless(
            Storage::disk($projectFile->disk)->exists($projectFile->path),
            404,
            'El archivo ya no está disponible.',
        );

        return Storage::disk($projectFile->disk)->response(
            $projectFile->path,
            $projectFile->original_name,
            [
                'Content-Type' => $projectFile->mime_type
                    ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    public function destroy(
        Request $request,
        Project $project,
        ProjectFile $projectFile,
    ): JsonResponse {
        $this->ensureFileOwnership($request, $project, $projectFile);

        DeletionCode::validate($request);

        $projectFile->delete();

        return response()->json([
            'message' => 'Archivo eliminado correctamente.',
        ]);
    }

    private function validatedSlot(string $slot): int
    {
        abort_unless(ctype_digit($slot), 404);

        $slotNumber = (int) $slot;
        abort_unless($slotNumber >= 1, 404);

        return $slotNumber;
    }

    private function ensureProjectOwnership(
        Request $request,
        Project $project,
    ): void {
        // El espacio municipal es compartido entre todos los usuarios autenticados.
    }

    private function ensureFileOwnership(
        Request $request,
        Project $project,
        ProjectFile $projectFile,
    ): void {
        $this->ensureProjectOwnership($request, $project);
        abort_unless($projectFile->project_id === $project->id, 404);
    }

    private function availableStorageName(
        string $disk,
        string $directory,
        string $originalName,
    ): string {
        $safeName = $this->safeStorageName($originalName);
        $storage = Storage::disk($disk);

        if (! $storage->exists("{$directory}/{$safeName}")) {
            return $safeName;
        }

        $extension = pathinfo($safeName, PATHINFO_EXTENSION);
        $stem = pathinfo($safeName, PATHINFO_FILENAME);

        for ($number = 2; $number <= 9999; $number++) {
            $candidate = $stem." ({$number})";

            if ($extension !== '') {
                $candidate .= ".{$extension}";
            }

            if (! $storage->exists("{$directory}/{$candidate}")) {
                return $candidate;
            }
        }

        return $stem.'-'.bin2hex(random_bytes(6))
            .($extension !== '' ? ".{$extension}" : '');
    }

    private function safeStorageName(string $originalName): string
    {
        $extension = preg_replace(
            '/[^A-Za-z0-9]/',
            '',
            pathinfo($originalName, PATHINFO_EXTENSION),
        );
        $stem = preg_replace(
            '/[<>:"\/\\\\|?*\x00-\x1F\x7F]/u',
            '_',
            pathinfo($originalName, PATHINFO_FILENAME),
        );
        $stem = trim((string) $stem, ' .');
        $stem = $stem !== '' ? $stem : 'archivo';
        $maximumStemLength = 180 - ($extension !== ''
            ? mb_strlen($extension) + 1
            : 0);
        $stem = mb_substr($stem, 0, max(1, $maximumStemLength));

        return $stem.($extension !== '' ? ".{$extension}" : '');
    }

    /** @return array{id: int, slot: int, name: string, size: ?int, is_link: bool, preview_url: string, download_url: string} */
    private function filePayload(
        Project $project,
        ProjectFile $projectFile,
    ): array {
        return [
            'id' => $projectFile->id,
            'slot' => $projectFile->slot,
            'name' => $projectFile->original_name,
            'size' => $projectFile->size,
            'is_link' => $projectFile->external_url !== null,
            'preview_url' => route(
                'project-files.preview',
                [$project, $projectFile],
            ),
            'download_url' => route(
                'project-files.download',
                [$project, $projectFile],
            ),
        ];
    }

    private function googleDriveDownloadUrl(string $url): string
    {
        if (preg_match('~/file/d/([^/]+)~', $url, $matches) === 1) {
            return 'https://drive.google.com/uc?export=download&id='.
                rawurlencode($matches[1]);
        }

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (isset($query['id']) && is_string($query['id'])) {
            return 'https://drive.google.com/uc?export=download&id='.
                rawurlencode($query['id']);
        }

        return $url;
    }
}
