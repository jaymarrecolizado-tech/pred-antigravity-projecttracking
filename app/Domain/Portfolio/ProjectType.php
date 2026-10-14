<?php

namespace App\Domain\Portfolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectType extends Model
{
    use HasFactory;

    protected $table = 'project_types';

    protected $fillable = [
        'name',
        'kind',
        'marker_shape',
        'color_code',
        'description',
    ];

    public function statusDefs(): HasMany
    {
        return $this->hasMany(StatusDef::class)->orderBy('sort_order');
    }

    public function interventionTypes(): HasMany
    {
        return $this->hasMany(InterventionType::class);
    }

    public function fieldDefs(): HasMany
    {
        return $this->hasMany(FieldDef::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function initialStatus(): ?StatusDef
    {
        return $this->statusDefs()->first();
    }

    public function completedStatuses(): array
    {
        return $this->statusDefs()->where('is_completed', true)->pluck('key')->toArray();
    }
}
