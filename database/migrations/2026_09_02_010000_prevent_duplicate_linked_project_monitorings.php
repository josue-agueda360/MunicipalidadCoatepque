<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_monitorings', function (Blueprint $table): void {
            $table->unique(
                ['user_id', 'project_id'],
                'project_monitorings_user_project_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('project_monitorings', function (Blueprint $table): void {
            $table->dropUnique('project_monitorings_user_project_unique');
        });
    }
};
