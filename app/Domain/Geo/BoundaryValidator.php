<?php

namespace App\Domain\Geo;

class BoundaryValidator
{
    protected ?array $provinceGeoJson = null;

    public function __construct()
    {
        $path = public_path('geo/r2-provinces.json');
        if (file_exists($path)) {
            $json = file_get_contents($path);
            $this->provinceGeoJson = json_decode($json, true);
        }
    }

    /**
     * Ray-Casting algorithm to test if a point (lat, lng) is inside a GeoJSON Polygon ring.
     * Note: In GeoJSON, coordinates are formatted as [lng, lat].
     *
     * @param float $lat
     * @param float $lng
     * @param array $ring Array of [lng, lat] pairs
     * @return bool
     */
    public function pointInRing(float $lat, float $lng, array $ring): bool
    {
        $inside = false;
        $numPoints = count($ring);
        if ($numPoints < 3) {
            return false;
        }

        for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
            $xi = $ring[$i][0]; // lng
            $yi = $ring[$i][1]; // lat
            $xj = $ring[$j][0]; // lng
            $yj = $ring[$j][1]; // lat

            $intersect = (($yi > $lat) !== ($yj > $lat)) &&
                ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 0.000000001) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Test if a point is inside a polygon or multipolygon coordinates array.
     */
    public function pointInPolygonGeometry(float $lat, float $lng, string $type, array $coordinates): bool
    {
        if ($type === 'Polygon') {
            // First ring is exterior ring; subsequent are holes
            if (empty($coordinates[0])) {
                return false;
            }
            if (!$this->pointInRing($lat, $lng, $coordinates[0])) {
                return false;
            }
            // Check holes
            for ($k = 1; $k < count($coordinates); $k++) {
                if ($this->pointInRing($lat, $lng, $coordinates[$k])) {
                    return false; // Point falls inside a hole
                }
            }
            return true;
        }

        if ($type === 'MultiPolygon') {
            foreach ($coordinates as $polygon) {
                if ($this->pointInPolygonGeometry($lat, $lng, 'Polygon', $polygon)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Validate whether a coordinate falls strictly within the province's boundary.
     */
    public function validateProvince(float $lat, float $lng, string $provinceCode): bool
    {
        if (!$this->provinceGeoJson || empty($this->provinceGeoJson['features'])) {
            return $this->isWithinRegion2($lat, $lng);
        }

        foreach ($this->provinceGeoJson['features'] as $feature) {
            $code = $feature['properties']['code'] ?? '';
            // Match full PSGC or prefix (e.g. 0215)
            if ($code === $provinceCode || str_starts_with($provinceCode, substr($code, 0, 4))) {
                $geometry = $feature['geometry'] ?? [];
                $type = $geometry['type'] ?? 'Polygon';
                $coordinates = $geometry['coordinates'] ?? [];
                return $this->pointInPolygonGeometry($lat, $lng, $type, $coordinates);
            }
        }

        return false;
    }

    /**
     * Calculate geodesic distance in meters using Haversine formula.
     */
    public function haversineDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Check if two points are duplicates within a tolerance threshold (default: 50 meters).
     */
    public function isNearby(float $lat1, float $lng1, float $lat2, float $lng2, float $thresholdMeters = 50.0): bool
    {
        return $this->haversineDistanceMeters($lat1, $lng1, $lat2, $lng2) <= $thresholdMeters;
    }

    /**
     * Broad bounding box check for Region 2 (Cagayan Valley + Batanes).
     */
    public function isWithinRegion2(float $lat, float $lng): bool
    {
        return $lat >= 15.5 && $lat <= 21.5 && $lng >= 120.5 && $lng <= 123.0;
    }
}
