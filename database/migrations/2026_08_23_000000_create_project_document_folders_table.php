<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_files', function (Blueprint $table): void {
            $table->unsignedInteger('slot')->change();
        });

        Schema::create('project_document_folders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['project_id', 'position']);
        });

        $now = now();

        DB::table('projects')
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($projects) use ($now): void {
                $rows = [];

                foreach ($projects as $project) {
                    foreach (range(1, 5) as $position) {
                        $rows[] = [
                            'project_id' => $project->id,
                            'name' => "Carpeta {$position}",
                            'position' => $position,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($rows !== []) {
                    DB::table('project_document_folders')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_document_folders');

        Schema::table('project_files', function (Blueprint $table): void {
            $table->unsignedTinyInteger('slot')->change();
        });
    }
};
