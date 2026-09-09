<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_financial_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('allocated_budget', 16, 2)->default(0);
            $table->decimal('physical_progress', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('project_funding_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('source_type', 30);
            $table->string('custom_name', 120)->nullable();
            $table->decimal('amount', 16, 2);
            $table->timestamps();

            $table->index(['project_id', 'source_type']);
        });

        Schema::create('project_financial_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->date('occurred_on');
            $table->string('movement_type', 24);
            $table->string('category', 30);
            $table->string('description', 500);
            $table->decimal('amount', 16, 2);
            $table->string('document_number', 100)->nullable();
            $table->string('provider', 150)->nullable();
            $table->timestamps();

            $table->index(['project_id', 'occurred_on']);
            $table->index(['project_id', 'category']);
        });

        Schema::create('project_financial_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_financial_movement_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('disk', 40);
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_financial_documents');
        Schema::dropIfExists('project_financial_movements');
        Schema::dropIfExists('project_funding_sources');
        Schema::dropIfExists('project_financial_profiles');
    }
};
