<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFinancialProfile extends Model
{
    protected $fillable = [
        'allocated_budget',
        'physical_progress',
    ];

    protected function casts(): array
    {
        return [
            'allocated_budget' => 'decimal:2',
            'physical_progress' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
