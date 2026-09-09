<?php

use App\Support\ProjectFolderColor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                ->from('project_financial_movements')
                ->whereColumn('project_financial_movements.project_id', 'projects.id'))
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('project_financial_required_documents')
                ->whereColumn('project_financial_required_documents.project_id', 'projects.id'))
            ->delete();

        DB::table('project_folders')
            ->whereRaw('LOWER(name) = ?', ['proyectos municipales'])
            ->where('is_primary', true)
            ->whereNotExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('projects')
                ->whereColumn('projects.project_folder_id', 'project_folders.id'))
            ->delete();

        Schema::table('project_folders', function (Blueprint $table): void {
            $table->dropUnique('project_folders_user_id_name_unique');
            $table->dropUnique('project_folders_user_id_color_unique');
        });

        DB::table('project_folders')
            ->orderBy('id')
            ->get(['id'])
            ->values()
            ->each(function (object $folder, int $index): void {
                $color = ProjectFolderColor::forIndex($index);

                DB::table('project_folders')
                    ->where('id', $folder->id)
                    ->update(['color' => $color]);
                DB::table('projects')
                    ->where('project_folder_id', $folder->id)
                    ->update(['color' => $color]);
            });

        Schema::table('project_folders', function (Blueprint $table): void {
            $table->unique('name', 'project_folders_name_unique');
            $table->unique('color', 'project_folders_color_unique');
        });

        DB::table('project_monitorings')
            ->whereNotNull('project_id')
            ->select('project_id', DB::raw('MIN(id) AS retained_id'))
            ->groupBy('project_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function (object $duplicate): void {
                DB::table('project_monitorings')
                    ->where('project_id', $duplicate->project_id)
                    ->where('id', '<>', $duplicate->retained_id)
                    ->update(['project_id' => null]);
            });

        Schema::table('project_monitorings', function (Blueprint $table): void {
            $table->dropUnique('project_monitorings_user_project_unique');
            $table->unique('project_id', 'project_monitorings_project_unique');
        });
    }

    public function down(): void
    {
        Schema::table('project_monitorings', function (Blueprint $table): void {
            $table->dropUnique('project_monitorings_project_unique');
            $table->unique(
                ['user_id', 'project_id'],
                'project_monitorings_user_project_unique',
            );
        });

        Schema::table('project_folders', function (Blueprint $table): void {
            $table->dropUnique('project_folders_name_unique');
            $table->dropUnique('project_folders_color_unique');
            $table->unique(['user_id', 'name']);
            $table->unique(['user_id', 'color']);
        });
    }
};
