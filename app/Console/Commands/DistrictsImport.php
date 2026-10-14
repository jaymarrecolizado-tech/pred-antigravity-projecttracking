<?php

namespace App\Console\Commands;

use App\Domain\Geo\CongressionalDistrict;
use App\Domain\Geo\DistrictMembership;
use App\Domain\Geo\PsgcArea;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DistrictsImport extends Command
{
    protected $signature = 'districts:import {file?}';
    protected $description = 'Import Philippine Congressional District assignments for Region 02';

    public function handle(): int
    {
        $filePath = $this->argument('file') ?? database_path('data/districts_r2.csv');

        if (!file_exists($filePath)) {
            $this->error("Districts CSV file does not exist: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Importing Congressional Districts from: {$filePath}");

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            $this->error('File is empty.');
            return Command::FAILURE;
        }

        $header = str_getcsv(array_shift($lines));
        $mappedCount = 0;

        DB::transaction(function () use ($lines, &$mappedCount) {
            foreach ($lines as $line) {
                $row = str_getcsv($line);
                if (count($row) < 5) continue;

                $provinceCode = trim($row[0]);
                $districtNum = !empty(trim($row[1])) ? (int)trim($row[1]) : null;
                $districtName = trim($row[2]);
                $citymunCode = trim($row[3]);
                $effectiveFrom = trim($row[4]);

                // 1. Ensure district exists
                $district = CongressionalDistrict::firstOrCreate(
                    [
                        'province_code' => $provinceCode,
                        'name' => $districtName,
                    ],
                    [
                        'district_number' => $districtNum,
                    ]
                );

                // 2. Ensure membership exists
                DistrictMembership::updateOrCreate(
                    [
                        'district_id' => $district->id,
                        'citymun_code' => $citymunCode,
                        'effective_from' => $effectiveFrom,
                    ],
                    [
                        'effective_to' => null,
                    ]
                );

                $mappedCount++;
            }
        });

        // Verification: Check if any cities/municipalities in Region 2 lack a district assignment
        $unassigned = PsgcArea::whereIn('level', ['city', 'mun'])
            ->whereDoesntHave('districtMemberships')
            ->get();

        $this->newLine();
        $this->info("Imported {$mappedCount} district membership assignments across " . CongressionalDistrict::count() . " congressional districts.");

        if ($unassigned->isNotEmpty()) {
            $this->warn('Warning: The following municipalities/cities lack a congressional district mapping:');
            foreach ($unassigned as $lgu) {
                $this->line(" - {$lgu->name} ({$lgu->code})");
            }
        } else {
            $this->info('Success: 100% of Region 2 Cities & Municipalities are mapped to Congressional Districts!');
        }

        return Command::SUCCESS;
    }
}
