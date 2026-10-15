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
     * Resolve the full geographic breadcrumb hierarchy (Province > Congressional District > Municipality > Barangay).
     */
    public function getHierarchyAttribute(): array
    {
        $bgy = $this->barangay;
        $munName = null;
        $provName = null;
        $bgyName = null;

        if ($bgy) {
            $bgyName = $bgy->name;
            $mun = $bgy->parent;
            $munName = $mun?->name;
            $provName = $mun?->parent?->name;
        } elseif (!empty($this->address_details)) {
            $parts = array_map('trim', explode(',', $this->address_details));
            $bgyName = $parts[0] ?? null;
            $munName = $parts[1] ?? null;
            $provName = $parts[2] ?? null;
        }

        $districtName = self::resolveDistrictName($munName, $provName);

        return [
            'barangay' => $bgyName ?? 'N/A',
            'municipality' => $munName ?? 'N/A',
            'province' => $provName ?? 'Region 02',
            'district' => $districtName,
        ];
    }

    /**
     * Resolve the Congressional District for a given municipality and province in Region 02.
     */
    public static function resolveDistrictName(?string $muni, ?string $prov): string
    {
        $prov = trim((string)$prov);
        $muni = trim((string)$muni);
        $mUpper = strtoupper($muni);

        if ($prov === 'Batanes') {
            return 'Batanes Lone District';
        }
        if ($prov === 'Nueva Vizcaya') {
            return 'Nueva Vizcaya Lone District';
        }
        if ($prov === 'Quirino') {
            return 'Quirino Lone District';
        }

        if ($prov === 'Cagayan') {
            if (in_array($mUpper, ['ALCALA', 'APARRI', 'BAGGAO', 'BUGUEY', 'CAMALANIUGAN', 'GATTARAN', 'GONZAGA', 'LAL-LO', 'SANTA ANA', 'STA. ANA', 'SANTA TERESITA', 'STA. TERESITA'])) {
                return 'Cagayan 1st District';
            }
            if (in_array($mUpper, ['ABULUG', 'ALLACAPAN', 'BALLESTEROS', 'CALAYAN', 'CLAVERIA', 'LASAM', 'PAMPLONA', 'PIAT', 'RIZAL', 'SANCHEZ-MIRA', 'SANCHEZ MIRA', 'SANTA PRAXEDES', 'STA. PRAXEDES', 'SANTO NIÑO', 'STO. NIÑO', 'FAIRE'])) {
                return 'Cagayan 2nd District';
            }
            return 'Cagayan 3rd District';
        }

        if ($prov === 'Isabela') {
            if (in_array($mUpper, ['CITY OF ILAGAN', 'ILAGAN CITY', 'ILAGAN', 'CABAGAN', 'DELFIN ALBANO', 'DIVILACAN', 'MACONACON', 'PALANAN', 'SAN PABLO', 'SANTA MARIA', 'STA. MARIA', 'SANTO TOMAS', 'STO. TOMAS', 'TUMAUINI'])) {
                return 'Isabela 1st District';
            }
            if (in_array($mUpper, ['BENITO SOLIVEN', 'GAMU', 'NAGUILIAN', 'REINA MERCEDES', 'SAN MARIANO'])) {
                return 'Isabela 2nd District';
            }
            if (in_array($mUpper, ['ALICIA', 'ANGADANAN', 'CABATUAN', 'SAN MATEO'])) {
                return 'Isabela 3rd District';
            }
            if (in_array($mUpper, ['CITY OF SANTIAGO', 'SANTIAGO CITY', 'SANTIAGO', 'CORDON', 'DINAPIGUE', 'JONES', 'RAMON', 'SAN AGUSTIN'])) {
                return 'Isabela 4th District';
            }
            if (in_array($mUpper, ['AURORA', 'BURGOS', 'LUNA', 'MALLIG', 'QUEZON', 'QUIRINO', 'ROXAS', 'SAN MANUEL'])) {
                return 'Isabela 5th District';
            }
            return 'Isabela 6th District';
        }

        return 'Region 02 Legislative District';
    }
}

