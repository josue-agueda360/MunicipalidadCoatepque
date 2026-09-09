<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    protected $fillable = [
        'slot',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'external_url',
    ];

    protected function casts(): array
    {
        return [
            'slot' => 'integer',
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (ProjectFile $projectFile): void {
            if ($projectFile->disk && $projectFile->path) {
                Storage::disk($projectFile->disk)->delete($projectFile->path);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
