<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Geo\CongressionalDistrict;
use App\Domain\Geo\PsgcArea;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GeoController extends Controller
{
    public function index(): Response
    {
        $provinces = PsgcArea::where('level', 'prov')
            ->orderBy('name')
            ->get()
            ->map(function ($prov) {
                $districts = CongressionalDistrict::where('province_code', $prov->code)
                    ->with(['memberships.cityMunicipality'])
                    ->get()
                    ->map(fn ($d) => [
                        'id' => $d->id,
                        'name' => $d->name,
                        'district_number' => $d->district_number,
                        'lgus_count' => $d->memberships->count(),
                        'lgus' => $d->memberships->map(fn ($m) => [
                            'code' => $m->citymun_code,
                            'name' => $m->cityMunicipality?->name,
                            'level' => $m->cityMunicipality?->level,
                            'city_class' => $m->cityMunicipality?->city_class,
                            'lat' => $m->cityMunicipality?->centroid_lat,
                            'lng' => $m->cityMunicipality?->centroid_lng,
                        ]),
                    ]);

                $citiesCount = PsgcArea::where('province_code', $prov->code)->whereIn('level', ['city', 'mun'])->count();
                $barangaysCount = PsgcArea::where('province_code', $prov->code)->where('level', 'bgy')->count();

                return [
                    'code' => $prov->code,
                    'name' => $prov->name,
                    'lat' => $prov->centroid_lat,
                    'lng' => $prov->centroid_lng,
                    'districts_count' => $districts->count(),
                    'districts' => $districts,
                    'cities_count' => $citiesCount,
                    'barangays_count' => $barangaysCount,
                ];
            });

        return Inertia::render('Admin/Geo/Index', [
            'provinces' => $provinces,
            'stats' => [
                'provinces' => PsgcArea::where('level', 'prov')->count(),
                'districts' => CongressionalDistrict::count(),
                'cities_municipalities' => PsgcArea::whereIn('level', ['city', 'mun'])->count(),
                'barangays' => PsgcArea::where('level', 'bgy')->count(),
            ],
        ]);
    }

    public function barangays(string $citymunCode): JsonResponse
    {
        $barangays = PsgcArea::where('citymun_code', $citymunCode)
            ->where('level', 'bgy')
            ->orderBy('name')
            ->get(['code', 'name', 'centroid_lat', 'centroid_lng']);

        return response()->json($barangays);
    }
}
