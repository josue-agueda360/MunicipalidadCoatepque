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
            $table->string('path')->nullable()->change();
            $table->string('disk', 40)->nullable()->change();
            $table->unsignedBigInteger('size')->nullable()->change();
            $table->text('external_url')->nullable();
        });
    }

    public function down(): void
    {
        DB::table('project_files')->whereNull('path')->delete();

        Schema::table('project_files', function (Blueprint $table): void {
            $table->dropColumn('external_url');
            $table->string('path')->nullable(false)->change();
            $table->string('disk', 40)->default('local')->nullable(false)->change();
            $table->unsignedBigInteger('size')->nullable(false)->change();
        });
    }
};
