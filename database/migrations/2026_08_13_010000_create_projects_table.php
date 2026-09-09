<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('project_workspace_initialized_at')->nullable();
        });

        Schema::table('project_folders', function (Blueprint $table): void {
            $table->boolean('is_primary')->default(false);
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_folder_id')
                ->constrained('project_folders')
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('place', 100)->default('Coatepeque');
            $table->string('description', 255)->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('color', 7)->default('#e4a52c');
            $table->timestamps();

            $table->index(['project_folder_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');

        Schema::table('project_folders', function (Blueprint $table): void {
            $table->dropColumn('is_primary');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('project_workspace_initialized_at');
        });
    }
};
