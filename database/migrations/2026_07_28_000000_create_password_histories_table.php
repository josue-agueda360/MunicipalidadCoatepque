<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('password_hash');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'password_hash']);
        });

        DB::table('users')
            ->select(['id', 'password'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                $createdAt = now();
                $rows = $users->map(fn ($user): array => [
                    'user_id' => $user->id,
                    'password_hash' => $user->password,
                    'created_at' => $createdAt,
                ])->all();

                DB::table('password_histories')->insertOrIgnore($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_histories');
    }
};
