<?php

namespace App\Domain\Registry;

use App\Domain\Geo\PsgcArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'name',
        'category',
        'psgc_barangay',
        'lat',
        'lng',
        'elevation_m',
        'is_verified',
        'address_details',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'elevation_m' => 'float',
            'is_verified' => 'boolean',
        ];
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(PsgcArea::class, 'psgc_barangay', 'code');
    }

    public function projectSites(): HasMany
    {
        return $this->hasMany(ProjectSite::class);
    }

    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class, 'primary_location_id');
    }

    /**
     * Resolve the full geographic breadcrumb hierarchy (Province > Municipality > Barangay).
     */
    public function getHierarchyAttribute(): array
    {
        $bgy = $this->barangay;
        if (!$bgy) {
            return [];
        }

        $mun = $bgy->parent;
        $prov = $mun?->parent;

        return [
            'barangay' => $bgy->name,
            'municipality' => $mun?->name,
            'province' => $prov?->name,
        ];
    }
}
