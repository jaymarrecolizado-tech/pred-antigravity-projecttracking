<?php

namespace App\Domain\Reporting;

use App\Domain\Registry\ProjectSite;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

class Intervention extends Model
{
    use HasFactory;

    protected $table = 'interventions';

    protected $fillable = [
        'project_site_id',
        'intervention_type',
        'previous_status',
        'new_status',
        'occurred_at',
        'created_by',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Enforce append-only integrity
        static::updating(function () {
            throw new LogicException('Intervention records are strictly append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new LogicException('Intervention records are strictly append-only and cannot be deleted.');
        });
    }

    public function projectSite(): BelongsTo
    {
        return $this->belongsTo(ProjectSite::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fieldReport(): HasOne
    {
        return $this->hasOne(FieldReport::class);
    }
}
