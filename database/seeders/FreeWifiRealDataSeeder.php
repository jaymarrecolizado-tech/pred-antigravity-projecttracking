<?php

namespace Database\Seeders;

use App\Domain\Analytics\KpiCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class FreeWifiRealDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artisan::call('wifi:import');
    }
}
