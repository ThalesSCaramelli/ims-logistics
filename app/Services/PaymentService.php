<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\WorkerPayment;
use App\Models\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PaymentService
{
    public function __construct(
        private LaborService $labor,
        private NotificationService $notifications
    ) {}

    /**
     * Calculate payments for all workers after worksheet approval.
     * Called by WorksheetService::approve()
     */
    public function calculateFromWorksheet(Worksheet $worksheet): void
    {
        $this->labor->calculateForWorksheet($worksheet);
        $this->applyWeeklyMinimums($worksheet->job->date->startOfWeek()->toDateString());
    }

    /**
     * Check all workers with weekly minimums and top up if needed.
     * Called after each payment calculation and on manual weekly close.
     */
    public function applyWeeklyMinimums(string $weekStart): void
    {
        Worker::where('status', 'active')
            ->whereNotNull('min_weekly')
            ->each(function (Worker $worker) use ($weekStart) {
                $earned = $worker->weeklyEarnings($weekStart);
                $minimum = (float) $worker->min_weekly;

                if ($earned < $minimum) {
                    $topup = round($minimum - $earned, 2);

                    // Update existing payments to reflect top-up
                    $lastPayment = WorkerPayment::where('worker_id', $worker->id)
                        ->where('week_period', $weekStart)
                        ->latest()
                        ->first();

                    if ($lastPayment) {
                        $lastPayment->topup_amount += $topup;
                        $lastPayment->total_amount += $topup;
                        $lastPayment->save();
                    }
                }
            });
    }

    /**
     * Get weekly summary for a worker — used in consolidation screen.
     */
    public function weekSummary(Worker $worker, string $weekStart): array
    {
        $weekEnd = Carbon::parse($weekStart)->addDays(6)->toDateString();

        $payments = WorkerPayment::where('worker_id', $worker->id)
            ->whereBetween('week_period', [$weekStart, $weekEnd])
            ->with(['worksheet.job.site.client', 'containerTeam.container.product'])
            ->get();

        $byDate = $payments->groupBy(fn($p) => $p->worksheet->job->date->format('Y-m-d'));

        $base   = $payments->sum('base_amount');
        $extras = $payments->sum('extras_amount');
        $topup  = $payments->sum('topup_amount');
        $total  = $payments->sum('total_amount');

        return [
            'worker'     => $worker,
            'week_start' => $weekStart,
            'week_end'   => $weekEnd,
            'base'       => round($base, 2),
            'extras'     => round($extras, 2),
            'topup'      => round($topup, 2),
            'total'      => round($total, 2),
            'minimum'    => $worker->min_weekly,
            'below_min'  => $worker->min_weekly && $total < (float) $worker->min_weekly,
            'status'     => $payments->first()?->status ?? 'calculated',
            'breakdown'  => $byDate->map(fn($dayPayments) => [
                'date'     => $dayPayments->first()->worksheet->job->date->format('d M Y'),
                'client'   => $dayPayments->first()->worksheet->job->site->client->name,
                'site'     => $dayPayments->first()->worksheet->job->site->name,
                'product'  => $dayPayments->first()->containerTeam?->container?->product?->name ?? 'Labor Hire',
                'base'     => round($dayPayments->sum('base_amount'), 2),
                'extras'   => round($dayPayments->sum('extras_amount'), 2),
                'subtotal' => round($dayPayments->sum('total_amount'), 2),
            ])->values(),
        ];
    }

    /**
     * Mark all payments for a worker in a week as paid.
     */
    public function markAsPaid(Worker $worker, string $weekStart, int $approvedBy): void
    {
        $updated = WorkerPayment::where('worker_id', $worker->id)
            ->where('week_period', $weekStart)
            ->update([
                'status'      => 'paid',
                'paid_at'     => now(),
                'approved_by' => $approvedBy,
            ]);

        if ($updated > 0) {
            $payment = WorkerPayment::where('worker_id', $worker->id)
                ->where('week_period', $weekStart)
                ->latest()
                ->first();

            if ($payment) {
                $this->notifications->paymentProcessed($payment);
            }
        }
    }

    /**
     * Export weekly payments as CSV-ready array.
     */
    public function exportWeek(string $weekStart): array
    {
        $workers = Worker::where('status', 'active')->get();
        $rows = [];

        foreach ($workers as $worker) {
            $summary = $this->weekSummary($worker, $weekStart);
            if ($summary['total'] > 0) {
                $rows[] = [
                    'worker'     => $worker->name,
                    'abn'        => $worker->abn,
                    'base'       => $summary['base'],
                    'extras'     => $summary['extras'],
                    'topup'      => $summary['topup'],
                    'total'      => $summary['total'],
                    'status'     => $summary['status'],
                ];
            }
        }

        return $rows;
    }
}
