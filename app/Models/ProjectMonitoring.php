<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMonitoring extends Model
{
    /** @var array<string, string> */
    public const STATUSES = [
        'planning' => 'Planificación',
        'in_progress' => 'En ejecución',
        'finished' => 'Finalizado',
        'closed' => 'Cerrado',
    ];

    /** @var array<string, string> */
    public const CATEGORIES = [
        'tender' => 'Licitación',
        'quotation' => 'Cotización',
    ];

    protected $fillable = [
        'project_id',
        'project_name',
        'status',
        'category',
        'starts_on',
        'ends_on',
        'location',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
