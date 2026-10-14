<?php

namespace App\Jobs;

use App\Domain\Analytics\KpiCalculator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRollupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $projectId = null,
        public ?string $date = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(KpiCalculator $calculator): void
    {
        $asOfDate = $this->date ? Carbon::parse($this->date) : Carbon::now();
        $calculator->rebuildRollups($asOfDate, $this->projectId);
    }
}
