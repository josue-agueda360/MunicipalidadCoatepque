<?php

namespace App\Models;

use App\Support\ProjectFolderColor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectFolder extends Model
{
    protected $fillable = [
        'name',
        'color',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProjectFolder $projectFolder): void {
            if (
                $projectFolder->user_id !== null
                && blank($projectFolder->color)
            ) {
                $projectFolder->color = ProjectFolderColor::nextForWorkspace();
            }
        });

        static::deleting(function (ProjectFolder $projectFolder): void {
            $projectFolder->projects()->get()->each->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
