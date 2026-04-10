<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerPayment;
use App\Models\Worksheet;
use App\Models\ContainerWorker;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $week = $request->get('week', now()->format('Y-\WW'));

        [$year, $isoWeek] = str_contains($week, '-W')
            ? explode('-W', $week)
            : explode('-', $week);

        $weekStart = Carbon::now()->setISODate((int)$year, (int)$isoWeek)->startOfWeek();
        $weekEnd   = $weekStart->copy()->endOfWeek();

        // Approved or paid worksheets in this week
        $worksheets = Worksheet::whereIn('sync_status', ['approved', 'paid'])
            ->whereHas('job', fn($q) => $q->whereBetween('date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ]))
            ->with([
                'job.site.client',
                'job.book.workers',
                'job.containers.workers',
                'services',
            ])
            ->get();

        $workerPayments = $this->buildWorkerPayments($worksheets, $weekStart);

        $kpis = [
            'total'        => $workerPayments->sum('payable_amount'),
            'paid_count'   => $workerPayments->where('is_paid', true)->count(),
            'unpaid_count' => $workerPayments->where('is_paid', false)->count(),
            'outstanding'  => $workerPayments->where('is_paid', false)->sum('payable_amount'),
            'jobs_count'   => $worksheets->count(),
        ];

        return view('payments.index', compact('workerPayments', 'kpis', 'week'));
    }

    public function markPaid(Request $request, Worker $worker)
    {
        $week = $request->week ?? now()->format('Y-\WW');
        [$year, $isoWeek] = str_contains($week, '-W')
            ? explode('-W', $week)
            : explode('-', $week);
        $weekStart = Carbon::now()->setISODate((int)$year, (int)$isoWeek)->startOfWeek();

        WorkerPayment::updateOrCreate(
            ['worker_id' => $worker->id, 'week_period' => $weekStart->toDateString()],
            ['paid_at' => now(), 'paid_by' => auth()->id()]
        );

        return back()->with('success', $worker->name . ' marked as paid.');
    }

    public function markUnpaid(Request $request, Worker $worker)
    {
        $week = $request->week ?? now()->format('Y-\WW');
        [$year, $isoWeek] = str_contains($week, '-W')
            ? explode('-W', $week)
            : explode('-', $week);
        $weekStart = Carbon::now()->setISODate((int)$year, (int)$isoWeek)->startOfWeek();

        WorkerPayment::where('worker_id', $worker->id)
            ->where('week_period', $weekStart->toDateString())
            ->delete();

        return back()->with('success', $worker->name . ' payment undone.');
    }

    public function markAllPaid(Request $request)
    {
        $week = $request->week ?? now()->format('Y-\WW');
        [$year, $isoWeek] = str_contains($week, '-W')
            ? explode('-W', $week)
            : explode('-', $week);
        $weekStart = Carbon::now()->setISODate((int)$year, (int)$isoWeek)->startOfWeek();
        $weekEnd   = $weekStart->copy()->endOfWeek();

        $worksheets = Worksheet::whereIn('sync_status', ['approved', 'paid'])
            ->whereHas('job', fn($q) => $q->whereBetween('date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ]))
            ->with('job.book.workers')
            ->get();

        $workerIds = $worksheets
            ->flatMap(fn($ws) => $ws->job->book->workers->pluck('id'))
            ->unique();

        foreach ($workerIds as $wid) {
            WorkerPayment::updateOrCreate(
                ['worker_id' => $wid, 'week_period' => $weekStart->toDateString()],
                ['paid_at' => now(), 'paid_by' => auth()->id()]
            );
        }

        return back()->with('success', $workerIds->count() . ' workers marked as paid.');
    }

    public function export(Request $request)
    {
        $week = $request->get('week', now()->format('Y-\WW'));
        [$year, $isoWeek] = str_contains($week, '-W')
            ? explode('-W', $week)
            : explode('-', $week);
        $weekStart = Carbon::now()->setISODate((int)$year, (int)$isoWeek)->startOfWeek();
        $weekEnd   = $weekStart->copy()->endOfWeek();

        $worksheets = Worksheet::whereIn('sync_status', ['approved', 'paid'])
            ->whereHas('job', fn($q) => $q->whereBetween('date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ]))
            ->with(['job.site.client', 'job.book.workers', 'job.containers.workers', 'services'])
            ->get();

        $workerPayments = $this->buildWorkerPayments($worksheets, $weekStart);

        $rows = ["Worker,Jobs,Total Payable,Status"];
        foreach ($workerPayments as $wp) {
            $rows[] = implode(',', [
                '"' . $wp['worker']->name . '"',
                $wp['jobs_count'],
                number_format($wp['payable_amount'], 2),
                $wp['is_paid'] ? 'Paid' : 'Unpaid',
            ]);
        }

        return response(implode("\n", $rows))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="payments_' . $week . '.csv"');
    }

    // ── Build per-worker payment summaries from approved worksheets ──────────
    private function buildWorkerPayments($worksheets, Carbon $weekStart): \Illuminate\Support\Collection
    {
        $byWorker      = [];
        $weekStartDate = $weekStart->toDateString();

        // Collect public holidays in this week
        $weekEnd     = $weekStart->copy()->endOfWeek();
        $holidayDates = \App\Models\SpecialDay::where('is_active', true)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
            ->toArray();

        foreach ($worksheets as $ws) {
            // Decode filled_data — source of truth for approved values
            $filled = $ws->filled_data ? json_decode($ws->filled_data, true) : null;
            $workerTotalsMap = [];
            if ($filled && !empty($filled['worker_totals'])) {
                foreach ($filled['worker_totals'] as $wt) {
                    $workerTotalsMap[$wt['id']] = (float) $wt['amount'];
                }
            }

            $workerIds = !empty($workerTotalsMap)
                ? array_keys($workerTotalsMap)
                : $ws->job->containers->flatMap(fn($c) => $c->workers->pluck('worker_id'))->unique()->toArray();

            if (empty($workerIds)) {
                $workerIds = $ws->job->book->workers->pluck('id')->toArray();
            }

            $jobDate    = \Carbon\Carbon::parse($ws->job->date);
            $isWeekend  = $jobDate->isWeekend();
            $isHoliday  = in_array($jobDate->toDateString(), $holidayDates);

            foreach ($workerIds as $workerId) {
                $worker = $ws->job->book->workers->find($workerId)
                    ?? Worker::find($workerId);

                if (!$worker) continue;

                if (!isset($byWorker[$workerId])) {
                    $byWorker[$workerId] = [
                        'worker'            => $worker,
                        'jobs_count'        => 0,
                        'total_amount'      => 0,
                        'total_hours'       => 0,
                        'weekday_earned'    => 0,
                        'weekend_earned'    => 0,
                        'weekdays_worked'   => [],  // unique weekday dates worked
                        'lines'             => [],
                    ];
                }

                $lineAmount = $workerTotalsMap[$workerId] ?? 0;

                $byWorker[$workerId]['jobs_count']++;
                $byWorker[$workerId]['total_amount'] += $lineAmount;

                if ($isWeekend || $isHoliday) {
                    $byWorker[$workerId]['weekend_earned'] += $lineAmount;
                } else {
                    $byWorker[$workerId]['weekday_earned'] += $lineAmount;
                    // Track unique weekdays worked (for minimum calculation)
                    $byWorker[$workerId]['weekdays_worked'][] = $jobDate->toDateString();
                }

                $byWorker[$workerId]['lines'][] = [
                    'date'         => $jobDate,
                    'client'       => $ws->job->site->client->name,
                    'site'         => $ws->job->site->name ?? '',
                    'is_weekend'   => $isWeekend,
                    'is_holiday'   => $isHoliday,
                    'hours'        => 0,
                    'rate'         => 0,
                    'labour'       => $lineAmount,
                    'additionals'  => 0,
                    'deductions'   => 0,
                    'total'        => $lineAmount,
                    'amount'       => $lineAmount,
                    'worksheet_id' => $ws->id,
                    'worker_id'    => $workerId,
                ];
            }
        }

        return collect($byWorker)->map(function ($wp) use ($weekStartDate) {
            $worker  = $wp['worker'];
            $isPaid  = WorkerPayment::where('worker_id', $worker->id)
                ->where('week_period', $weekStartDate)
                ->exists();

            // ── Minimum weekly calculation ─────────────────────────────────
            $topUp        = 0;
            $minWeekly    = (float) ($worker->min_weekly ?? 0);
            $weekdayDates = array_unique($wp['weekdays_worked']);
            $daysWorked   = count($weekdayDates);

            if ($minWeekly > 0 && $daysWorked > 0) {
                $dailyRate      = $minWeekly / 5;
                $minimumOwed    = round($dailyRate * $daysWorked, 2);
                $weekdayEarned  = round($wp['weekday_earned'], 2);

                if ($weekdayEarned < $minimumOwed) {
                    $topUp = $minimumOwed - $weekdayEarned;
                }
            }

            $payable = round($wp['weekday_earned'] + $topUp + $wp['weekend_earned'], 2);

            return array_merge($wp, [
                'days_worked'    => $daysWorked,
                'minimum_owed'   => $minWeekly > 0 ? round(($minWeekly / 5) * $daysWorked, 2) : 0,
                'top_up'         => round($topUp, 2),
                'payable_amount' => $payable,
                'is_paid'        => $isPaid,
            ]);
        })->sortBy('worker.name')->values();
    }
}