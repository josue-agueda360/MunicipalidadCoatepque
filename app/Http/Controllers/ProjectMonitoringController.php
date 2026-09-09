<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMonitoring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $readOnly = $request->routeIs('public.*');
        $projects = Project::query()
            ->whereDoesntHave('monitorings')
            ->orderByRaw('CASE WHEN snip IS NULL THEN 1 ELSE 0 END')
            ->orderBy('snip')
            ->orderBy('name')
            ->get(['id', 'snip', 'name', 'place'])
            ->map(fn (Project $project): array => [
                'id' => $project->id,
                'snip' => $project->snip,
                'name' => $project->name,
                'place' => $project->place,
            ])
            ->values();

        $records = ProjectMonitoring::query()
            ->with('project:id,snip')
            ->latest('id')
            ->get()
            ->map(fn (ProjectMonitoring $record): array => $this->payload($record))
            ->values();

        return view('project-monitoring', [
            'projects' => $projects,
            'records' => $records,
            'statuses' => ProjectMonitoring::STATUSES,
            'categories' => ProjectMonitoring::CATEGORIES,
            'readOnly' => $readOnly,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'project_id' => [
                'nullable',
                'integer',
                Rule::unique('project_monitorings', 'project_id'),
            ],
            'project_name' => ['required', 'string', 'max:150'],
            'status' => [
                'required',
                Rule::in(array_keys(ProjectMonitoring::STATUSES)),
            ],
            'category' => [
                'required',
                Rule::in(array_keys(ProjectMonitoring::CATEGORIES)),
            ],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'location' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
        ], [
            'project_name.required' => 'Escribe el nombre del proyecto.',
            'project_name.max' => 'El nombre puede tener hasta 150 caracteres.',
            'project_id.unique' => 'Este proyecto ya tiene un registro de monitoreo.',
            'status.required' => 'Selecciona el estado del proyecto.',
            'status.in' => 'El estado seleccionado no es válido.',
            'category.required' => 'Selecciona la categoría.',
            'category.in' => 'La categoría seleccionada no es válida.',
            'starts_on.required' => 'Selecciona la fecha de inicio.',
            'starts_on.date' => 'La fecha de inicio no es válida.',
            'ends_on.required' => 'Selecciona la fecha de finalización.',
            'ends_on.date' => 'La fecha de finalización no es válida.',
            'ends_on.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha de inicio.',
            'location.required' => 'Escribe la ubicación del proyecto.',
            'location.max' => 'La ubicación puede tener hasta 150 caracteres.',
            'description.max' => 'La descripción puede tener hasta 3000 caracteres.',
        ]);

        $project = null;

        if (filled($data['project_id'] ?? null)) {
            $project = Project::query()
                ->find($data['project_id']);

            if ($project === null) {
                throw ValidationException::withMessages([
                    'project_id' => 'El proyecto seleccionado no está disponible.',
                ]);
            }
        }

        $projectName = $project?->name
            ?? trim((string) $data['project_name']);
        $location = trim((string) $data['location']);

        if ($projectName === '') {
            throw ValidationException::withMessages([
                'project_name' => 'Escribe el nombre del proyecto.',
            ]);
        }

        if ($location === '') {
            throw ValidationException::withMessages([
                'location' => 'Escribe la ubicación del proyecto.',
            ]);
        }

        $record = DB::transaction(function () use (
            $request,
            $data,
            $project,
            $projectName,
            $location,
        ): ProjectMonitoring {
            return $request->user()->projectMonitorings()->create([
                'project_id' => $project?->id,
                'project_name' => $projectName,
                'status' => $data['status'],
                'category' => $data['category'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
                'location' => $location,
                'description' => trim((string) ($data['description'] ?? ''))
                    ?: null,
            ]);
        });

        $record->setRelation('project', $project);

        return response()->json([
            'message' => 'Registro de monitoreo guardado correctamente.',
            'record' => $this->payload($record),
        ], 201);
    }

    public function updateStatus(
        Request $request,
        ProjectMonitoring $projectMonitoring,
    ): JsonResponse {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(array_keys(ProjectMonitoring::STATUSES)),
            ],
        ], [
            'status.required' => 'Selecciona el nuevo estado del proyecto.',
            'status.in' => 'El estado seleccionado no es válido.',
        ]);

        $projectMonitoring->update([
            'status' => $data['status'],
        ]);
        $projectMonitoring->load('project:id,snip');

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'record' => $this->payload($projectMonitoring),
        ]);
    }

    /**
     * @return array{
     *     id: int,
     *     project_id: ?int,
     *     project_name: string,
     *     snip: ?string,
     *     linked: bool,
     *     status: string,
     *     status_label: string,
     *     category: string,
     *     category_label: string,
     *     starts_on: string,
     *     ends_on: string,
     *     starts_on_label: string,
     *     ends_on_label: string,
     *     location: string,
     *     description: ?string
     * }
     */
    private function payload(ProjectMonitoring $record): array
    {
        return [
            'id' => $record->id,
            'project_id' => $record->project_id,
            'project_name' => $record->project_name,
            'snip' => $record->project?->snip,
            'linked' => $record->project_id !== null,
            'status' => $record->status,
            'status_label' => ProjectMonitoring::STATUSES[$record->status],
            'category' => $record->category,
            'category_label' => ProjectMonitoring::CATEGORIES[$record->category],
            'starts_on' => $record->starts_on->toDateString(),
            'ends_on' => $record->ends_on->toDateString(),
            'starts_on_label' => $record->starts_on->format('d/m/Y'),
            'ends_on_label' => $record->ends_on->format('d/m/Y'),
            'location' => $record->location,
            'description' => $record->description,
        ];
    }
}
