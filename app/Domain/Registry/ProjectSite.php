<?php

namespace App\Domain\Registry;

use App\Domain\Portfolio\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSite extends Model
{
    use HasFactory;

    protected $table = 'project_sites';

    protected $fillable = [
        'project_id',
        'location_id',
        'institution_id',
        'site_code',
        'current_status',
        'assigned_encoder_id',
        'target_completion_date',
        'custom_attributes',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'custom_attributes' => 'array',
            'target_completion_date' => 'date',
            'enrolled_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function assignedEncoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_encoder_id');
    }

    /**
     * Resolve effective coordinates for map visualization and spatial checks.
     */
    public function getCoordinates(): ?array
    {
        if ($this->location && $this->location->lat && $this->location->lng) {
            return [
                'lat' => (float) $this->location->lat,
                'lng' => (float) $this->location->lng,
            ];
        }

        if ($this->institution && $this->institution->primaryLocation) {
            return [
                'lat' => (float) $this->institution->primaryLocation->lat,
                'lng' => (float) $this->institution->primaryLocation->lng,
            ];
        }

        return null;
    }

    public function interventions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Domain\Reporting\Intervention::class);
    }

    /**
     * Resolve the primary PSGC code (barangay or institution level).
     */
    public function resolvePsgcCode(): ?string
    {
        if ($this->location && $this->location->psgc_barangay) {
            return $this->location->psgc_barangay;
        }

        if ($this->institution && $this->institution->psgc_code) {
            return $this->institution->psgc_code;
        }

        return null;
    }
}
