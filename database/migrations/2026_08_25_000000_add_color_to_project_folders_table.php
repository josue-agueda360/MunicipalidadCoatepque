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
        Schema::table('project_folders', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('name');
        });

        DB::table('project_folders')
            ->orderBy('user_id')
            ->orderBy('id')
            ->get()
            ->groupBy('user_id')
            ->each(function ($folders): void {
                $folders->values()->each(function ($folder, int $index): void {
                    $color = ProjectFolderColor::forIndex($index);

                    DB::table('project_folders')
                        ->where('id', $folder->id)
                        ->update(['color' => $color]);

                    DB::table('projects')
                        ->where('project_folder_id', $folder->id)
                        ->update(['color' => $color]);
                });
            });

        Schema::table('project_folders', function (Blueprint $table) {
            $table->unique(['user_id', 'color']);
        });
    }

    public function down(): void
    {
        Schema::table('project_folders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'color']);
            $table->dropColumn('color');
        });
    }
};
