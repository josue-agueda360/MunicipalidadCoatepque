<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectLocationController extends Controller
{
    public function __invoke(Request $request): View
    {
        $readOnly = $request->routeIs('public.*');
        $projects = Project::query()
            ->orderByRaw('CASE WHEN snip IS NULL THEN 1 ELSE 0 END')
            ->orderBy('snip')
            ->orderBy('name')
            ->get([
                'id',
                'snip',
                'name',
                'place',
                'latitude',
                'longitude',
                'color',
            ])
            ->map(fn (Project $project): array => [
                'id' => $project->id,
                'snip' => $project->snip,
                'name' => $project->name,
                'place' => $project->place,
                'latitude' => (float) $project->latitude,
                'longitude' => (float) $project->longitude,
                'color' => $project->color ?: '#e4a52c',
            ])
            ->values();

        return view('project-locations', [
            'projects' => $projects,
            'readOnly' => $readOnly,
        ]);
    }
}
