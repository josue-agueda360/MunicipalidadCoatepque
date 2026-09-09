<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')
            ->whereNull('snip')
            ->where('name', 'Mejoramiento de calle con pavimento')
            ->where('place', 'Barrio La Esperanza')
            ->where('description', 'Intervención vial registrada en la carpeta municipal.')
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('project_files')
                ->whereColumn('project_files.project_id', 'projects.id'))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('project_monitorings')
                ->whereColumn('project_monitorings.project_id', 'projects.id'))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('project_funding_sources')
                ->whereColumn('project_funding_sources.project_id', 'projects.id'))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('project_financial_movements')
                ->whereColumn('project_financial_movements.project_id', 'projects.id'))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('project_financial_profiles')
                ->whereColumn('project_financial_profiles.project_id', 'projects.id')
                ->where(fn ($profile) => $profile
                    ->where('allocated_budget', '>', 0)
                    ->orWhere('physical_progress', '>', 0)))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('project_financial_required_documents')
                ->whereColumn('project_financial_required_documents.project_id', 'projects.id')
                ->where(fn ($document) => $document
                    ->whereNotNull('path')
                    ->orWhere('waived', true)))
            ->delete();

        DB::table('project_folders')
            ->whereRaw('LOWER(name) = ?', ['proyectos municipales'])
            ->where('is_primary', true)
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('projects')
                ->whereColumn('projects.project_folder_id', 'project_folders.id'))
            ->delete();
    }

    public function down(): void
    {
        // Los proyectos de demostración sin uso no contienen información que restaurar.
    }
};
