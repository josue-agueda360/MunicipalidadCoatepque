<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFinancialDocument;
use App\Models\ProjectFinancialMovement;
use App\Models\ProjectFinancialRequiredDocument;
use App\Models\ProjectFundingSource;
use App\Support\DeletionCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProjectFinanceController extends Controller
{
    /** @var array<string, string> */
    public const FUNDING_TYPES = [
        'municipal' => 'Fondos municipales',
        'codede' => 'Aporte del CODEDE',
        'central_government' => 'Gobierno central',
        'donation' => 'Donación',
        'other' => 'Otro',
    ];

    /** @var array<string, string> */
    public const MOVEMENT_TYPES = [
        'expense' => 'Gasto',
        'disbursement' => 'Desembolso',
    ];

    /** @var array<string, string> */
    public const EXPENSE_CATEGORIES = [
        'materials' => 'Materiales',
        'labor' => 'Mano de obra',
        'machinery' => 'Maquinaria',
        'transport' => 'Transporte',
        'supervision' => 'Supervisión',
        'services' => 'Servicios',
        'other' => 'Otros',
    ];

    public function index(Request $request): View
    {
        $readOnly = $request->routeIs('public.*');
        $projects = Project::query()
            ->with([
                'financialProfile',
                'fundingSources' => fn ($query) => $query->latest('id'),
                'financialRequiredDocuments',
                'financialMovements' => fn ($query) => $query
                    ->with(['recordedBy:id,name', 'document'])
                    ->orderByDesc('occurred_on')
                    ->orderByDesc('id'),
            ])
            ->orderByRaw('CASE WHEN snip IS NULL THEN 1 ELSE 0 END')
            ->orderBy('snip')
            ->orderBy('name')
            ->get()
            ->map(fn (Project $project): array => $this->projectPayload($project, $readOnly))
            ->values();

        return view('project-finance', [
            'projects' => $projects,
            'fundingTypes' => self::FUNDING_TYPES,
            'readOnly' => $readOnly,
        ]);
    }

    public function updateProfile(Request $request, Project $project): JsonResponse
    {
        $this->ensureProjectOwnership($request, $project);

        $data = $request->validate([
            'allocated_budget' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'physical_progress' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'allocated_budget.required' => 'Escribe el presupuesto asignado.',
            'allocated_budget.numeric' => 'El presupuesto debe ser un número válido.',
            'allocated_budget.min' => 'El presupuesto no puede ser negativo.',
            'allocated_budget.max' => 'El presupuesto supera el límite permitido.',
            'physical_progress.required' => 'Escribe el avance físico.',
            'physical_progress.numeric' => 'El avance físico debe ser un número válido.',
            'physical_progress.min' => 'El avance físico no puede ser menor que 0%.',
            'physical_progress.max' => 'El avance físico no puede superar 100%.',
        ]);

        $project->financialProfile()->updateOrCreate([], $data);

        return $this->projectResponse(
            $project,
            'Resumen financiero actualizado correctamente.',
        );
    }

    public function storeFundingSource(Request $request, Project $project): JsonResponse
    {
        $this->ensureProjectOwnership($request, $project);

        $data = $request->validate([
            'source_type' => ['required', Rule::in(array_keys(self::FUNDING_TYPES))],
            'custom_name' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
        ], [
            'source_type.required' => 'Selecciona la fuente de financiamiento.',
            'source_type.in' => 'La fuente seleccionada no es válida.',
            'custom_name.max' => 'El nombre puede tener hasta 120 caracteres.',
            'amount.required' => 'Escribe el monto aportado.',
            'amount.numeric' => 'El monto debe ser un número válido.',
            'amount.gt' => 'El monto debe ser mayor que cero.',
        ]);

        if ($data['source_type'] === 'other' && blank($data['custom_name'] ?? null)) {
            throw ValidationException::withMessages([
                'custom_name' => 'Escribe el nombre de la fuente.',
            ]);
        }

        $project->fundingSources()->create([
            'source_type' => $data['source_type'],
            'custom_name' => $data['source_type'] === 'other'
                ? trim((string) $data['custom_name'])
                : null,
            'amount' => $data['amount'],
        ]);

        return $this->projectResponse(
            $project,
            'Fuente de financiamiento agregada correctamente.',
            201,
        );
    }

    public function destroyFundingSource(
        Request $request,
        Project $project,
        ProjectFundingSource $projectFundingSource,
    ): JsonResponse {
        $this->ensureProjectOwnership($request, $project);
        abort_unless($projectFundingSource->project_id === $project->id, 404);
        DeletionCode::validate($request);

        $projectFundingSource->delete();

        return $this->projectResponse(
            $project,
            'Fuente de financiamiento eliminada correctamente.',
        );
    }

    public function storeRequiredDocument(
        Request $request,
        Project $project,
        string $documentType,
    ): JsonResponse {
        $this->ensureProjectOwnership($request, $project);
        abort_unless(in_array($documentType, ['contract', 'budget'], true), 404);

        if (! preg_match('/^\d{1,6}$/', (string) $project->snip)) {
            throw ValidationException::withMessages([
                'file' => 'El proyecto necesita un código SNIP válido para guardar el archivo en IDrive e2.',
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
            'file.required' => 'Selecciona el archivo que deseas subir.',
            'file.file' => 'El archivo seleccionado no es válido.',
            'file.max' => 'El archivo no puede superar 50 MB.',
            'file.mimes' => 'Puedes subir PDF, Word, Excel, imágenes JPG o PNG y archivos ZIP.',
        ]);

        $existing = $project->financialRequiredDocuments()
            ->where('document_type', $documentType)
            ->first();

        if ($existing?->path) {
            throw ValidationException::withMessages([
                'file' => 'Este apartado ya tiene un archivo.',
            ]);
        }

        $uploadedFile = $data['file'];
        $disk = (string) config('filesystems.project_files_disk', 'local');
        $prefix = trim((string) config('filesystems.project_files_prefix', 'project-files'), '/');
        $directory = "{$prefix}/{$project->snip}/finanzas/documentos/{$documentType}";
        $originalName = $this->originalFileName($uploadedFile);
        $path = $uploadedFile->storeAs(
            $directory,
            $this->safeStorageName($originalName),
            $disk,
        );

        abort_if($path === false, 500, 'No se pudo guardar el documento financiero.');

        try {
            $project->financialRequiredDocuments()->updateOrCreate(
                ['document_type' => $documentType],
                [
                    'waived' => false,
                    'original_name' => $originalName,
                    'path' => $path,
                    'disk' => $disk,
                    'mime_type' => $uploadedFile->getMimeType(),
                    'size' => $uploadedFile->getSize(),
                ],
            );
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }

        return $this->projectResponse(
            $project,
            $documentType === 'contract'
                ? 'Contrato guardado correctamente.'
                : 'Presupuesto guardado correctamente.',
            201,
        );
    }

    public function updateContractWaiver(Request $request, Project $project): JsonResponse
    {
        $this->ensureProjectOwnership($request, $project);
        $data = $request->validate([
            'waived' => ['required', 'boolean'],
        ]);

        $contract = $project->financialRequiredDocuments()
            ->where('document_type', 'contract')
            ->first();

        if ($data['waived'] && $contract?->path) {
            throw ValidationException::withMessages([
                'waived' => 'Elimina primero el contrato guardado para marcar esta opción.',
            ]);
        }

        $project->financialRequiredDocuments()->updateOrCreate(
            ['document_type' => 'contract'],
            ['waived' => $data['waived']],
        );

        return $this->projectResponse(
            $project,
            $data['waived']
                ? 'El proyecto quedó marcado sin contrato.'
                : 'Ahora puedes subir el contrato.',
        );
    }

    public function previewRequiredDocument(
        Request $request,
        Project $project,
        ProjectFinancialRequiredDocument $projectFinancialRequiredDocument,
    ): StreamedResponse {
        $this->ensureRequiredDocumentOwnership($request, $project, $projectFinancialRequiredDocument);
        abort_unless(
            $projectFinancialRequiredDocument->disk
                && $projectFinancialRequiredDocument->path
                && Storage::disk($projectFinancialRequiredDocument->disk)->exists($projectFinancialRequiredDocument->path),
            404,
            'El archivo ya no está disponible.',
        );

        return Storage::disk($projectFinancialRequiredDocument->disk)->response(
            $projectFinancialRequiredDocument->path,
            $projectFinancialRequiredDocument->original_name,
            [
                'Content-Type' => $projectFinancialRequiredDocument->mime_type ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    public function downloadRequiredDocument(
        Request $request,
        Project $project,
        ProjectFinancialRequiredDocument $projectFinancialRequiredDocument,
    ): StreamedResponse {
        $this->ensureRequiredDocumentOwnership($request, $project, $projectFinancialRequiredDocument);
        abort_unless(
            $projectFinancialRequiredDocument->disk
                && $projectFinancialRequiredDocument->path
                && Storage::disk($projectFinancialRequiredDocument->disk)->exists($projectFinancialRequiredDocument->path),
            404,
            'El archivo ya no está disponible.',
        );

        return Storage::disk($projectFinancialRequiredDocument->disk)->download(
            $projectFinancialRequiredDocument->path,
            $projectFinancialRequiredDocument->original_name,
        );
    }

    public function destroyRequiredDocument(
        Request $request,
        Project $project,
        ProjectFinancialRequiredDocument $projectFinancialRequiredDocument,
    ): JsonResponse {
        $this->ensureRequiredDocumentOwnership($request, $project, $projectFinancialRequiredDocument);
        DeletionCode::validate($request);
        $projectFinancialRequiredDocument->delete();

        return $this->projectResponse(
            $project,
            'Documento financiero eliminado correctamente.',
        );
    }

    public function storeMovement(Request $request, Project $project): JsonResponse
    {
        $this->ensureProjectOwnership($request, $project);

        $data = $request->validate([
            'occurred_on' => ['required', 'date'],
            'movement_type' => ['required', Rule::in(array_keys(self::MOVEMENT_TYPES))],
            'category' => ['required', Rule::in(array_keys(self::EXPENSE_CATEGORIES))],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'max:150'],
            'file' => [
                'nullable',
                'file',
                'max:51200',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip',
            ],
        ], [
            'occurred_on.required' => 'Selecciona la fecha.',
            'occurred_on.date' => 'La fecha no es válida.',
            'movement_type.required' => 'Selecciona el tipo de movimiento.',
            'movement_type.in' => 'El tipo de movimiento no es válido.',
            'category.required' => 'Selecciona la categoría del gasto.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'description.required' => 'Escribe la descripción del movimiento.',
            'description.max' => 'La descripción puede tener hasta 500 caracteres.',
            'amount.required' => 'Escribe el monto.',
            'amount.numeric' => 'El monto debe ser un número válido.',
            'amount.gt' => 'El monto debe ser mayor que cero.',
            'document_number.max' => 'El número de documento puede tener hasta 100 caracteres.',
            'provider.max' => 'El proveedor puede tener hasta 150 caracteres.',
            'file.max' => 'El archivo de respaldo no puede superar 50 MB.',
            'file.mimes' => 'Puedes subir PDF, Word, Excel, imágenes JPG o PNG y archivos ZIP.',
        ]);

        $uploadedFile = $data['file'] ?? null;

        if ($uploadedFile instanceof UploadedFile && ! preg_match('/^\d{1,6}$/', (string) $project->snip)) {
            throw ValidationException::withMessages([
                'file' => 'El proyecto necesita un código SNIP válido para guardar el respaldo en IDrive e2.',
            ]);
        }

        $storedDisk = null;
        $storedPath = null;

        try {
            $movement = DB::transaction(function () use (
                $request,
                $project,
                $data,
                $uploadedFile,
                &$storedDisk,
                &$storedPath,
            ): ProjectFinancialMovement {
                $movement = $project->financialMovements()->create([
                    'recorded_by_user_id' => $request->user()->id,
                    'occurred_on' => $data['occurred_on'],
                    'movement_type' => $data['movement_type'],
                    'category' => $data['category'],
                    'description' => trim($data['description']),
                    'amount' => $data['amount'],
                    'document_number' => trim((string) ($data['document_number'] ?? '')) ?: null,
                    'provider' => trim((string) ($data['provider'] ?? '')) ?: null,
                ]);

                if ($uploadedFile instanceof UploadedFile) {
                    $storedDisk = (string) config('filesystems.project_files_disk', 'local');
                    $prefix = trim((string) config('filesystems.project_files_prefix', 'project-files'), '/');
                    $directory = "{$prefix}/{$project->snip}/finanzas/{$movement->id}";
                    $originalName = $this->originalFileName($uploadedFile);
                    $storageName = $this->safeStorageName($originalName);
                    $storedPath = $uploadedFile->storeAs($directory, $storageName, $storedDisk);

                    abort_if($storedPath === false, 500, 'No se pudo guardar el respaldo financiero.');

                    $movement->document()->create([
                        'original_name' => $originalName,
                        'path' => $storedPath,
                        'disk' => $storedDisk,
                        'mime_type' => $uploadedFile->getMimeType(),
                        'size' => $uploadedFile->getSize(),
                    ]);
                }

                return $movement;
            });
        } catch (Throwable $exception) {
            if ($storedDisk && $storedPath) {
                Storage::disk($storedDisk)->delete($storedPath);
            }

            throw $exception;
        }

        return $this->projectResponse(
            $project,
            'Movimiento financiero registrado correctamente.',
            201,
        );
    }

    public function destroyMovement(
        Request $request,
        Project $project,
        ProjectFinancialMovement $projectFinancialMovement,
    ): JsonResponse {
        $this->ensureProjectOwnership($request, $project);
        abort_unless($projectFinancialMovement->project_id === $project->id, 404);
        DeletionCode::validate($request);

        $projectFinancialMovement->load('document');
        DB::transaction(function () use ($projectFinancialMovement): void {
            $projectFinancialMovement->document?->delete();
            $projectFinancialMovement->delete();
        });

        return $this->projectResponse(
            $project,
            'Movimiento financiero eliminado correctamente.',
        );
    }

    public function previewDocument(
        Request $request,
        Project $project,
        ProjectFinancialMovement $projectFinancialMovement,
        ProjectFinancialDocument $projectFinancialDocument,
    ): StreamedResponse {
        $this->ensureDocumentOwnership(
            $request,
            $project,
            $projectFinancialMovement,
            $projectFinancialDocument,
        );
        abort_unless(
            Storage::disk($projectFinancialDocument->disk)->exists($projectFinancialDocument->path),
            404,
            'El respaldo ya no está disponible.',
        );

        return Storage::disk($projectFinancialDocument->disk)->response(
            $projectFinancialDocument->path,
            $projectFinancialDocument->original_name,
            [
                'Content-Type' => $projectFinancialDocument->mime_type ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline',
        );
    }

    public function downloadDocument(
        Request $request,
        Project $project,
        ProjectFinancialMovement $projectFinancialMovement,
        ProjectFinancialDocument $projectFinancialDocument,
    ): StreamedResponse {
        $this->ensureDocumentOwnership(
            $request,
            $project,
            $projectFinancialMovement,
            $projectFinancialDocument,
        );
        abort_unless(
            Storage::disk($projectFinancialDocument->disk)->exists($projectFinancialDocument->path),
            404,
            'El respaldo ya no está disponible.',
        );

        return Storage::disk($projectFinancialDocument->disk)->download(
            $projectFinancialDocument->path,
            $projectFinancialDocument->original_name,
        );
    }

    private function projectResponse(Project $project, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'project' => $this->projectPayload($this->loadProject($project)),
        ], $status);
    }

    private function loadProject(Project $project): Project
    {
        return $project->fresh([
            'financialProfile',
            'fundingSources' => fn ($query) => $query->latest('id'),
            'financialRequiredDocuments',
            'financialMovements' => fn ($query) => $query
                ->with(['recordedBy:id,name', 'document'])
                ->orderByDesc('occurred_on')
                ->orderByDesc('id'),
        ]);
    }

    /** @return array<string, mixed> */
    private function projectPayload(Project $project, bool $readOnly = false): array
    {
        $allocated = (float) ($project->financialProfile?->allocated_budget ?? 0);
        $physicalProgress = (float) ($project->financialProfile?->physical_progress ?? 0);
        $executed = round((float) $project->financialMovements->sum('amount'), 2);
        $balance = round($allocated - $executed, 2);
        $financialProgress = $allocated > 0
            ? round(($executed / $allocated) * 100, 2)
            : ($executed > 0 ? 100 : 0);
        $financialStatus = ($allocated <= 0 && $executed > 0) || $financialProgress > 100
            ? 'exceeded'
            : ($financialProgress >= 80 ? 'near_limit' : 'normal');

        $monthly = $project->financialMovements
            ->groupBy(fn (ProjectFinancialMovement $movement): string => $movement->occurred_on->format('Y-m'))
            ->map(fn ($movements, string $period): array => [
                'period' => $period,
                'label' => $this->monthLabel($movements->first()->occurred_on),
                'amount' => round((float) $movements->sum('amount'), 2),
            ])
            ->sortBy('period')
            ->values();

        $difference = round($financialProgress - $physicalProgress, 2);
        $comparisonStatus = abs($difference) <= 15
            ? 'balanced'
            : ($difference > 15 ? 'financial_ahead' : 'physical_ahead');

        return [
            'id' => $project->id,
            'snip' => $project->snip,
            'name' => $project->name,
            'place' => $project->place,
            'allocated_budget' => $allocated,
            'executed_amount' => $executed,
            'available_balance' => $balance,
            'financial_progress' => $financialProgress,
            'physical_progress' => $physicalProgress,
            'financial_status' => $financialStatus,
            'comparison_status' => $comparisonStatus,
            'progress_difference' => $difference,
            'funding_total' => round((float) $project->fundingSources->sum('amount'), 2),
            'funding_sources' => $project->fundingSources->map(fn (ProjectFundingSource $source): array => [
                'id' => $source->id,
                'source_type' => $source->source_type,
                'label' => $source->custom_name ?: self::FUNDING_TYPES[$source->source_type],
                'amount' => (float) $source->amount,
                'delete_url' => route('project-finance.funding-sources.destroy', [$project, $source]),
            ])->values(),
            'movements' => $project->financialMovements->map(fn (ProjectFinancialMovement $movement): array => [
                'id' => $movement->id,
                'occurred_on' => $movement->occurred_on->toDateString(),
                'occurred_on_label' => $movement->occurred_on->format('d/m/Y'),
                'movement_type' => $movement->movement_type,
                'movement_type_label' => self::MOVEMENT_TYPES[$movement->movement_type],
                'category' => $movement->category,
                'category_label' => self::EXPENSE_CATEGORIES[$movement->category],
                'description' => $movement->description,
                'amount' => (float) $movement->amount,
                'document_number' => $movement->document_number,
                'provider' => $movement->provider,
                'recorded_by' => $movement->recordedBy->name,
                'document' => $movement->document ? [
                    'id' => $movement->document->id,
                    'name' => $movement->document->original_name,
                    'size' => $movement->document->size,
                    'preview_url' => route('project-finance.documents.preview', [$project, $movement, $movement->document]),
                    'download_url' => route('project-finance.documents.download', [$project, $movement, $movement->document]),
                ] : null,
                'delete_url' => route('project-finance.movements.destroy', [$project, $movement]),
            ])->values(),
            'monthly_execution' => $monthly,
            'required_documents' => [
                'contract' => $this->requiredDocumentPayload(
                    $project,
                    $project->financialRequiredDocuments->firstWhere('document_type', 'contract'),
                    $readOnly,
                ),
                'budget' => $this->requiredDocumentPayload(
                    $project,
                    $project->financialRequiredDocuments->firstWhere('document_type', 'budget'),
                    $readOnly,
                ),
            ],
            'required_document_upload_urls' => [
                'contract' => route('project-finance.required-documents.store', [$project, 'contract']),
                'budget' => route('project-finance.required-documents.store', [$project, 'budget']),
            ],
            'contract_waiver_url' => route('project-finance.contract-waiver.update', $project),
            'profile_url' => route('project-finance.profile.update', $project),
            'funding_url' => route('project-finance.funding-sources.store', $project),
            'movement_url' => route('project-finance.movements.store', $project),
        ];
    }

    private function ensureProjectOwnership(Request $request, Project $project): void
    {
        // El espacio municipal es compartido entre todos los usuarios autenticados.
    }

    private function ensureDocumentOwnership(
        Request $request,
        Project $project,
        ProjectFinancialMovement $movement,
        ProjectFinancialDocument $document,
    ): void {
        $this->ensureProjectOwnership($request, $project);
        abort_unless($movement->project_id === $project->id, 404);
        abort_unless($document->project_financial_movement_id === $movement->id, 404);
    }

    private function ensureRequiredDocumentOwnership(
        Request $request,
        Project $project,
        ProjectFinancialRequiredDocument $document,
    ): void {
        $this->ensureProjectOwnership($request, $project);
        abort_unless($document->project_id === $project->id, 404);
    }

    /** @return array<string, mixed> */
    private function requiredDocumentPayload(
        Project $project,
        ?ProjectFinancialRequiredDocument $document,
        bool $readOnly = false,
    ): array {
        if ($document === null) {
            return [
                'id' => null,
                'waived' => false,
                'has_file' => false,
                'name' => null,
                'size' => null,
                'preview_url' => null,
                'download_url' => null,
                'delete_url' => null,
            ];
        }

        return [
            'id' => $document->id,
            'waived' => $document->waived,
            'has_file' => filled($document->path),
            'name' => $document->original_name,
            'size' => $document->size,
            'preview_url' => $document->path
                ? route(
                    $readOnly
                        ? 'public.project-finance.required-documents.preview'
                        : 'project-finance.required-documents.preview',
                    [$project, $document],
                )
                : null,
            'download_url' => $document->path
                ? route(
                    $readOnly
                        ? 'public.project-finance.required-documents.download'
                        : 'project-finance.required-documents.download',
                    [$project, $document],
                )
                : null,
            'delete_url' => $document->path
                ? route('project-finance.required-documents.destroy', [$project, $document])
                : null,
        ];
    }

    private function originalFileName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
        $name = mb_substr(trim((string) $name), 0, 255);

        return $name !== '' ? $name : 'respaldo.'.$file->extension();
    }

    private function monthLabel(\Illuminate\Support\Carbon $date): string
    {
        $months = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre',
        ];

        return $months[$date->month].' '.$date->year;
    }

    private function safeStorageName(string $originalName): string
    {
        $extension = preg_replace('/[^A-Za-z0-9]/', '', pathinfo($originalName, PATHINFO_EXTENSION));
        $stem = preg_replace('/[<>:"\/\\\\|?*\x00-\x1F\x7F]/u', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $stem = trim((string) $stem, ' .') ?: 'respaldo';
        $maximumStemLength = 180 - ($extension !== '' ? mb_strlen($extension) + 1 : 0);
        $stem = mb_substr($stem, 0, max(1, $maximumStemLength));

        return $stem.($extension !== '' ? ".{$extension}" : '');
    }
}
