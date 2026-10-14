<?php

namespace App\Domain\Analytics;

use App\Domain\Geo\PsgcArea;
use App\Domain\Portfolio\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RollupDaily extends Model
{
    use HasFactory;

    protected $table = 'rollup_daily';

    protected $fillable = [
        'project_id',
        'psgc_code',
        'date',
        'total_sites',
        'completed_sites',
        'in_progress_sites',
        'backlog_sites',
        'operational_sites',
        'indicator_actual',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_sites' => 'integer',
            'completed_sites' => 'integer',
            'in_progress_sites' => 'integer',
            'backlog_sites' => 'integer',
            'operational_sites' => 'integer',
            'indicator_actual' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(PsgcArea::class, 'psgc_code', 'code');
    }

    /**
     * Completion percentage for this aggregate slice.
     */
    public function getCompletionRateAttribute(): float
    {
        if ($this->total_sites <= 0) {
            return 0.0;
        }

        return round(($this->completed_sites / $this->total_sites) * 100, 1);
    }

    /**
     * Operational percentage for this aggregate slice.
     */
    public function getOperationalRateAttribute(): float
    {
        if ($this->total_sites <= 0) {
            return 0.0;
        }

        return round(($this->operational_sites / $this->total_sites) * 100, 1);
    }
}
