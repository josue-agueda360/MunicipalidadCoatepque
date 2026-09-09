<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_financial_required_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 20);
            $table->boolean('waived')->default(false);
            $table->string('original_name')->nullable();
            $table->string('path')->nullable();
            $table->string('disk', 40)->nullable();
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_financial_required_documents');
    }
};
