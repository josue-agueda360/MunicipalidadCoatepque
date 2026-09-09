<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'snip',
        'name',
        'place',
        'description',
        'latitude',
        'longitude',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project): void {
            $folderColor = ProjectFolder::query()
                ->whereKey($project->project_folder_id)
                ->value('color');

            if (is_string($folderColor) && $folderColor !== '') {
                $project->color = $folderColor;
            }
        });

        static::created(function (Project $project): void {
            $project->documentFolders()->createMany(
                collect(range(1, 5))
                    ->map(fn (int $position): array => [
                        'name' => "Carpeta {$position}",
                        'position' => $position,
                    ])
                    ->all(),
            );
        });

        static::deleting(function (Project $project): void {
            $project->files()->get()->each->delete();
            $project->financialMovements()
                ->with('document')
                ->get()
                ->each(function (ProjectFinancialMovement $movement): void {
                    $movement->document?->delete();
                });
            $project->financialRequiredDocuments()->get()->each->delete();
        });
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ProjectFolder::class, 'project_folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function documentFolders(): HasMany
    {
        return $this->hasMany(ProjectDocumentFolder::class)
            ->orderBy('position');
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(ProjectMonitoring::class);
    }

    public function financialProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectFinancialProfile::class);
    }

    public function fundingSources(): HasMany
    {
        return $this->hasMany(ProjectFundingSource::class);
    }

    public function financialMovements(): HasMany
    {
        return $this->hasMany(ProjectFinancialMovement::class);
    }

    public function financialRequiredDocuments(): HasMany
    {
        return $this->hasMany(ProjectFinancialRequiredDocument::class);
    }
}
