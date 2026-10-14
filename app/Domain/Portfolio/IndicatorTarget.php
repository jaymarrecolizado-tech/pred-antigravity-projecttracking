<?php

namespace App\Domain\Portfolio;

use App\Domain\Geo\PsgcArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorTarget extends Model
{
    use HasFactory;

    protected $table = 'indicator_targets';

    protected $fillable = [
        'project_id',
        'indicator_key',
        'psgc_code',
        'period',
        'target_value',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
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
}
