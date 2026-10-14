<?php

namespace App\Domain\Geo;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PsgcArea extends Model
{
    use HasFactory;

    protected $table = 'psgc_areas';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'level',
        'region_code',
        'province_code',
        'citymun_code',
        'parent_code',
        'city_class',
        'centroid_lat',
        'centroid_lng',
        'psgc_release',
    ];

    protected function casts(): array
    {
        return [
            'centroid_lat' => 'float',
            'centroid_lng' => 'float',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PsgcArea::class, 'parent_code', 'code');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PsgcArea::class, 'parent_code', 'code');
    }

    public function districtMemberships(): HasMany
    {
        return $this->hasMany(DistrictMembership::class, 'citymun_code', 'code');
    }

    public function scopeRegion(Builder $query): Builder
    {
        return $query->where('level', 'reg');
    }

    public function scopeProvinces(Builder $query): Builder
    {
        return $query->where('level', 'prov');
    }

    public function scopeCitiesMunicipalities(Builder $query): Builder
    {
        return $query->whereIn('level', ['city', 'mun']);
    }

    public function scopeBarangays(Builder $query): Builder
    {
        return $query->where('level', 'bgy');
    }
}
