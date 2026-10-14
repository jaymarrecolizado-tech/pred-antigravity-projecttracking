<?php

namespace Tests\Feature;

use App\Domain\Access\GrantResolver;
use App\Domain\Geo\CongressionalDistrict;
use App\Domain\Geo\PsgcArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_psgc_and_districts_import_commands_populate_region_2(): void
    {
        // 1. Run PSGC Import
        $this->artisan('psgc:import')->assertSuccessful();

        // Verify entities created
        $this->assertEquals(5, PsgcArea::where('level', 'prov')->count());
        $this->assertGreaterThanOrEqual(91, PsgcArea::whereIn('level', ['city', 'mun'])->count());
        $this->assertGreaterThan(500, PsgcArea::where('level', 'bgy')->count());

        // Verify Tuguegarao City exists as a component city in Cagayan
        $tuguegarao = PsgcArea::where('code', '021528000')->first();
        $this->assertNotNull($tuguegarao);
        $this->assertEquals('Tuguegarao City', $tuguegarao->name);
        $this->assertEquals('CC', $tuguegarao->city_class);
        $this->assertEquals('0215000000', $tuguegarao->province_code);

        // 2. Run Districts Import
        $this->artisan('districts:import')->assertSuccessful();

        // Verify 12 Congressional Districts
        $this->assertEquals(12, CongressionalDistrict::count());

        // Verify Tuguegarao is a member of Cagayan 3rd District
        $cagayan3rd = CongressionalDistrict::where('name', 'Cagayan 3rd District')->first();
        $this->assertNotNull($cagayan3rd);
        $this->assertTrue($cagayan3rd->memberships()->where('citymun_code', '021528000')->exists());
    }

    public function test_admin_can_access_geo_index_and_view_hierarchy(): void
    {
        $this->artisan('psgc:import');
        $this->artisan('districts:import');

        $admin = User::factory()->create([
            'two_factor_secret' => 'SECRET_KEY',
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->roleGrants()->create(['role' => GrantResolver::ROLE_SUPER_ADMIN]);

        $response = $this->actingAs($admin)->get(route('admin.geo.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Geo/Index')
            ->has('provinces', 5)
            ->has('stats')
        );
    }

    public function test_admin_can_fetch_barangays_for_lgu(): void
    {
        $this->artisan('psgc:import');

        $admin = User::factory()->create([
            'two_factor_secret' => 'SECRET_KEY',
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->roleGrants()->create(['role' => GrantResolver::ROLE_SUPER_ADMIN]);

        // Fetch barangays for Tuguegarao City (021528000)
        $response = $this->actingAs($admin)->get(route('admin.geo.barangays', '021528000'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['code', 'name', 'centroid_lat', 'centroid_lng']
        ]);
        $this->assertNotEmpty($response->json());
    }
}
