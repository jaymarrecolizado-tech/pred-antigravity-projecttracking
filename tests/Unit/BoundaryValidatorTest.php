<?php

namespace Tests\Unit;

use App\Domain\Geo\BoundaryValidator;
use Tests\TestCase;

class BoundaryValidatorTest extends TestCase
{
    protected BoundaryValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new BoundaryValidator();
    }

    public function test_ray_casting_point_in_ring(): void
    {
        // Simple square: lng 10 to 20, lat 10 to 20
        $squareRing = [
            [10.0, 10.0],
            [20.0, 10.0],
            [20.0, 20.0],
            [10.0, 20.0],
            [10.0, 10.0],
        ];

        // Point inside: lat 15, lng 15
        $this->assertTrue($this->validator->pointInRing(15.0, 15.0, $squareRing));

        // Point outside: lat 25, lng 15
        $this->assertFalse($this->validator->pointInRing(25.0, 15.0, $squareRing));

        // Point outside: lat 15, lng 5
        $this->assertFalse($this->validator->pointInRing(15.0, 5.0, $squareRing));
    }

    public function test_haversine_distance_and_proximity_check(): void
    {
        // Two points ~25 meters apart in Tuguegarao
        $lat1 = 17.613200;
        $lng1 = 121.727000;
        $lat2 = 17.613350;
        $lng2 = 121.727100;

        $distance = $this->validator->haversineDistanceMeters($lat1, $lng1, $lat2, $lng2);

        $this->assertLessThan(40.0, $distance);
        $this->assertTrue($this->validator->isNearby($lat1, $lng1, $lat2, $lng2, 50.0));

        // Tuguegarao to Basco (~320 km)
        $bascoLat = 20.4485;
        $bascoLng = 121.9708;

        $distToBasco = $this->validator->haversineDistanceMeters($lat1, $lng1, $bascoLat, $bascoLng);
        $this->assertGreaterThan(250000.0, $distToBasco);
        $this->assertFalse($this->validator->isNearby($lat1, $lng1, $bascoLat, $bascoLng, 50.0));
    }

    public function test_region_2_bounding_box_filter(): void
    {
        // Tuguegarao City (Inside)
        $this->assertTrue($this->validator->isWithinRegion2(17.6132, 121.7270));

        // Basco, Batanes (Inside)
        $this->assertTrue($this->validator->isWithinRegion2(20.4485, 121.9708));

        // Manila (Outside)
        $this->assertFalse($this->validator->isWithinRegion2(14.5995, 120.9842));

        // Davao (Outside)
        $this->assertFalse($this->validator->isWithinRegion2(7.1907, 125.4553));
    }

    public function test_validate_province_polygon(): void
    {
        // Tuguegarao in Cagayan (0215)
        $this->assertTrue($this->validator->validateProvince(17.6132, 121.7270, '0215000000'));

        // Tuguegarao should NOT be in Batanes (0209)
        $this->assertFalse($this->validator->validateProvince(17.6132, 121.7270, '0209000000'));

        // Basco in Batanes (0209)
        $this->assertTrue($this->validator->validateProvince(20.4485, 121.9708, '0209000000'));
    }
}
