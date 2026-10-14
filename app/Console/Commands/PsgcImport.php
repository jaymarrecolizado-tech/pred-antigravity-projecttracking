<?php

namespace App\Console\Commands;

use App\Domain\Geo\PsgcArea;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class PsgcImport extends Command
{
    protected $signature = 'psgc:import {file?} {--region=02} {--release=2026Q2}';
    protected $description = 'Import Philippine Standard Geographic Code (PSGC) areas for Region 02';

    public function handle(): int
    {
        $filePath = $this->argument('file') ?? database_path('data/psgc_r2.json');
        $regionPrefix = $this->option('region');
        $release = $this->option('release');

        if (!file_exists($filePath)) {
            $this->error("Target PSGC file does not exist: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Importing PSGC data from: {$filePath} (Region prefix: {$regionPrefix})");

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'json') {
            $this->importFromJson($filePath, $release);
        } else {
            $this->importFromSpreadsheet($filePath, $regionPrefix, $release);
        }

        $summary = [
            'Provinces' => PsgcArea::where('level', 'prov')->count(),
            'Cities & Municipalities' => PsgcArea::whereIn('level', ['city', 'mun'])->count(),
            'Barangays' => PsgcArea::where('level', 'bgy')->count(),
        ];

        $this->newLine();
        $this->info('PSGC Import Complete! Region 2 Entity Summary:');
        $this->table(['Administrative Level', 'Count'], [
            ['Provinces', $summary['Provinces']],
            ['Cities & Municipalities', $summary['Cities & Municipalities']],
            ['Barangays', $summary['Barangays']],
            ['Total PSGC Entities', PsgcArea::count()],
        ]);

        return Command::SUCCESS;
    }

    protected function importFromJson(string $path, string $release): void
    {
        $data = json_decode(file_get_contents($path), true);
        if (!$data) {
            $this->error('Failed to parse PSGC JSON data.');
            return;
        }

        $this->output->progressStart(count($data));

        DB::transaction(function () use ($data, $release) {
            foreach ($data as $row) {
                PsgcArea::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'level' => $row['level'],
                        'region_code' => $row['region_code'],
                        'province_code' => $row['province_code'],
                        'citymun_code' => $row['citymun_code'],
                        'parent_code' => $row['parent_code'],
                        'city_class' => $row['city_class'] ?? null,
                        'centroid_lat' => $row['centroid_lat'] ?? null,
                        'centroid_lng' => $row['centroid_lng'] ?? null,
                        'psgc_release' => $release,
                    ]
                );
                $this->output->progressAdvance();
            }
        });

        $this->output->progressFinish();
    }

    protected function importFromSpreadsheet(string $path, string $regionPrefix, string $release): void
    {
        $reader = new XlsxReader();
        $reader->open($path);

        $records = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                if ($index === 1) continue; // Skip header
                $cells = $row->toArray();
                $code = (string)($cells[0] ?? '');

                if (!str_starts_with($code, $regionPrefix)) {
                    continue;
                }

                $records[] = [
                    'code' => $code,
                    'name' => (string)($cells[1] ?? ''),
                    'level' => strtolower((string)($cells[2] ?? 'bgy')),
                    'region_code' => substr($code, 0, 2) . '00000000',
                    'province_code' => substr($code, 0, 4) . '000000',
                    'citymun_code' => substr($code, 0, 6) . '000',
                    'parent_code' => null,
                    'psgc_release' => $release,
                ];
            }
        }

        $reader->close();

        $this->info('Parsed ' . count($records) . ' records from spreadsheet. Inserting...');
        DB::transaction(function () use ($records) {
            foreach (array_chunk($records, 200) as $chunk) {
                PsgcArea::upsert($chunk, ['code'], ['name', 'level', 'region_code', 'province_code', 'citymun_code', 'psgc_release']);
            }
        });
    }
}
