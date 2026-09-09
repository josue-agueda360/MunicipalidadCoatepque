<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectFinancialDocument extends Model
{
    protected $fillable = [
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    protected static function booted(): void
    {
        static::deleted(function (ProjectFinancialDocument $document): void {
            Storage::disk($document->disk)->delete($document->path);
        });
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(
            ProjectFinancialMovement::class,
            'project_financial_movement_id',
        );
    }
}
