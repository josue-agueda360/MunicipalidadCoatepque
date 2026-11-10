<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_monitorings', function (Blueprint $table) {
            $table->string('project_name', 200)->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_monitorings', function (Blueprint $table) {
            $table->string('project_name', 150)->change();
        });
    }
};
