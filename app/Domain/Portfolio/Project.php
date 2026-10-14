<?php

namespace App\Domain\Portfolio;

use App\Domain\Geo\PsgcArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'code',
        'name',
        'program_id',
        'project_type_id',
        'lifecycle',
        'budget',
        'funding_source',
        'start_date',
        'end_date',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(ProjectScope::class);
    }

    public function indicatorTargets(): HasMany
    {
        return $this->hasMany(IndicatorTarget::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(\App\Domain\Registry\ProjectSite::class);
    }

    public function dailyRollups(): HasMany
    {
        return $this->hasMany(\App\Domain\Analytics\RollupDaily::class);
    }
}
