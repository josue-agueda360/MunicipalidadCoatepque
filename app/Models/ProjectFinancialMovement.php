<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectFinancialMovement extends Model
{
    protected $fillable = [
        'recorded_by_user_id',
        'occurred_on',
        'movement_type',
        'category',
        'description',
        'amount',
        'document_number',
        'provider',
    ];

    protected function casts(): array
    {
        return [
            'occurred_on' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function document(): HasOne
    {
        return $this->hasOne(ProjectFinancialDocument::class);
    }
}
