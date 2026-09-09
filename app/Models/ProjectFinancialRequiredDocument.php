<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFinancialRequiredDocument extends Model
{
    protected $fillable = [
        'document_type',
        'waived',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'waived' => 'boolean',
            'size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (ProjectFinancialRequiredDocument $document): void {
            if ($document->disk && $document->path) {
                Storage::disk($document->disk)->delete($document->path);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
