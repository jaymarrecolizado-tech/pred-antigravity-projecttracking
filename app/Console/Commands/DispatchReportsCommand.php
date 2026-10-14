<?php

namespace App\Console\Commands;

use App\Domain\Audit\AuditLogger;
use App\Domain\Reporting\Export\ExcelReportGenerator;
use App\Domain\Reporting\ReportSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DispatchReportsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:dispatch {--force : Force dispatch of all subscriptions regardless of frequency schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch scheduled automated reports to regional executive subscribers';

    /**
     * Execute the console command.
     */
    public function handle(ExcelReportGenerator $excelGenerator): int
    {
        $force = (bool) $this->option('force');
        $now = Carbon::now();

        $subscriptions = ReportSubscription::where('is_active', true)->get();
        $this->info("Evaluating {$subscriptions->count()} active report subscription(s)...");

        $dispatchedCount = 0;
        $reportsDir = storage_path('app/reports');
        if (!File::isDirectory($reportsDir)) {
            File::makeDirectory($reportsDir, 0755, true);
        }

        foreach ($subscriptions as $sub) {
            $shouldDispatch = $force || $this->shouldDispatch($sub, $now);

            if (!$shouldDispatch) {
                continue;
            }

            $this->info("Dispatching report: '{$sub->title}' [{$sub->frequency}] to " . implode(', ', $sub->recipients ?? []));

            $filename = 'report_' . $sub->id . '_' . $now->format('Ymd_His') . '.' . ($sub->format ?? 'xlsx');
            $filePath = $reportsDir . DIRECTORY_SEPARATOR . $filename;

            // Generate report
            $excelGenerator->generateToFile($filePath, $sub->filter_config ?? []);

            // Log dispatched audit
            AuditLogger::log('report.dispatch', $sub, null, [
                'subscription_id' => $sub->id,
                'file_path' => $filePath,
                'recipients' => $sub->recipients,
                'format' => $sub->format,
            ], $sub->user);

            // Update subscription last dispatched timestamp
            $sub->last_dispatched_at = $now;
            $sub->save();

            $dispatchedCount++;
        }

        $this->info("Report dispatch complete. Dispatched {$dispatchedCount} report(s).");

        return Command::SUCCESS;
    }

    /**
     * Check if a subscription is due for dispatch based on its configured frequency.
     */
    protected function shouldDispatch(ReportSubscription $sub, Carbon $now): bool
    {
        if (!$sub->last_dispatched_at) {
            return true;
        }

        $last = Carbon::parse($sub->last_dispatched_at);

        return match ($sub->frequency) {
            'daily' => $now->diffInDays($last) >= 1,
            'weekly' => $now->diffInDays($last) >= 7,
            'monthly' => $now->diffInMonths($last) >= 1,
            default => false,
        };
    }
}
