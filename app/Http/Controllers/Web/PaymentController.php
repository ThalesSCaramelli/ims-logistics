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
            //'total_workers' => $totalWorkers,
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

        foreach ($worksheets as $ws) {
            // Collect all worker IDs that worked on containers in this worksheet
            $containerWorkerIds = $ws->job->containers
                ->flatMap(fn($c) => $c->workers->pluck('worker_id'))
                ->unique();

            // Fallback to book workers if no container workers set
            if ($containerWorkerIds->isEmpty()) {
                $containerWorkerIds = $ws->job->book->workers->pluck('id');
            }

            foreach ($containerWorkerIds as $workerId) {
                $worker = $ws->job->book->workers->find($workerId)
                    ?? Worker::find($workerId);

                if (!$worker) continue;

                if (!isset($byWorker[$workerId])) {
                    $byWorker[$workerId] = [
                        'worker'       => $worker,
                        'jobs_count'   => 0,
                        'total_amount' => 0,
                        'lines'        => [],
                    ];
                }

                // Use worksheet approved amounts if available, otherwise estimate
                $workerTotal = $ws->worker_totals[$workerId] ?? null;
                $lineAmount  = $workerTotal ? (float)$workerTotal : 0;

                $byWorker[$workerId]['jobs_count']++;
                $byWorker[$workerId]['total_amount'] += $lineAmount;
                $byWorker[$workerId]['lines'][] = [
                    'date'   => $ws->job->date->format('d M Y'),
                    'client' => $ws->job->site->client->name,
                    'amount' => $lineAmount,
                ];
            }
        }

        return collect($byWorker)->map(function ($wp) use ($weekStartDate) {
            $isPaid = WorkerPayment::where('worker_id', $wp['worker']->id)
                ->where('week_period', $weekStartDate)
                ->exists();

            return array_merge($wp, [
                'payable_amount' => round($wp['total_amount'], 2),
                'is_paid'        => $isPaid,
            ]);
        })->sortBy('worker.name')->values();
    }
}
