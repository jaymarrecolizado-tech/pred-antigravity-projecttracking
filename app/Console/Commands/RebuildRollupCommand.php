<?php

namespace App\Console\Commands;

use App\Domain\Analytics\KpiCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RebuildRollupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rollup:rebuild {--date= : Date to calculate daily rollups for (format: YYYY-MM-DD)} {--project= : Specific Project ID to rebuild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild daily rollup aggregate rows in rollup_daily table for analytics';

    /**
     * Execute the console command.
     */
    public function handle(KpiCalculator $calculator): int
    {
        $dateParam = $this->option('date');
        $date = $dateParam ? Carbon::parse($dateParam) : Carbon::now();
        $projectId = $this->option('project') ? (int) $this->option('project') : null;

        $this->info("Rebuilding daily rollups for date: {$date->toDateString()}" . ($projectId ? " (Project ID: {$projectId})" : " (All Projects)"));

        $count = $calculator->rebuildRollups($date, $projectId);

        $this->info("Successfully upserted {$count} rollup records into rollup_daily.");

        return Command::SUCCESS;
    }
}
