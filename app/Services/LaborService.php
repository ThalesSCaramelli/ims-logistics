<?php

namespace App\Services;

use App\Models\JobContainer;
use App\Models\Worksheet;
use App\Models\WorkerPayment;
use App\Models\ClientPrice;
use Illuminate\Support\Collection;

class LaborService
{
    public function __construct(private PricingService $pricing) {}

    /**
     * Calculate and persist labor payments for all containers in a worksheet.
     * Called when office approves a worksheet.
     */
    public function calculateForWorksheet(Worksheet $worksheet): void
    {
        $job = $worksheet->job;
        $weekStart = $job->date->startOfWeek()->toDateString();

        foreach ($job->containers as $container) {
            $this->calculateForContainer($container, $worksheet, $weekStart);
        }

        // Extra work — split among team that did the relevant container
        $this->calculateExtraWork($worksheet, $weekStart);

        // Waiting time — split equally among all workers in the job
        $this->calculateWaitingTime($worksheet, $weekStart);
    }

    /**
     * Split labor pool for a container proportionally by feet, then equally within team.
     */
    public function calculateForContainer(JobContainer $container, Worksheet $worksheet, string $weekStart): void
    {
        $containerTeams = $container->containerTeams()->with('team.workers')->get();
        $totalFeet = $containerTeams->sum('feet_completed');

        if ($totalFeet === 0) return;

        $price = ClientPrice::forSiteAndProduct($container->job->site_id, $container->product_id);
        if (!$price) return;

        $pricing = $this->pricing->calculateContainer($container, $containerTeams->first()?->team?->workers?->count() ?? 1);
        $totalLaborPool = $pricing['labor'];

        foreach ($containerTeams as $ct) {
            // Each team's share is proportional to feet completed
            $teamShare = ($ct->feet_completed / $totalFeet) * $totalLaborPool;
            $workerCount = $ct->team->workers->count();

            if ($workerCount === 0) continue;

            $perWorker = $teamShare / $workerCount;

            foreach ($ct->team->workers as $worker) {
                WorkerPayment::create([
                    'worker_id'              => $worker->id,
                    'worksheet_id'           => $worksheet->id,
                    'job_container_team_id'  => $ct->id,
                    'base_amount'            => round($perWorker, 2),
                    'extras_amount'          => 0,
                    'topup_amount'           => 0,
                    'total_amount'           => round($perWorker, 2),
                    'description'            => "{$container->product->name} — {$ct->feet_completed}ft",
                    'week_period'            => $weekStart,
                    'status'                 => 'calculated',
                ]);
            }
        }
    }

    /**
     * Extra work labor split among the team that did the container with extra work.
     */
    private function calculateExtraWork(Worksheet $worksheet, string $weekStart): void
    {
        $extraWork = $worksheet->extra_work ?? [];
        if (empty($extraWork)) return;

        $price = $this->getPriceForJob($worksheet->job);
        if (!$price) return;

        foreach ($extraWork as $ew) {
            $hours = (float) ($ew['hours'] ?? 0);
            $laborAmount = round($hours * (float) $price->extra_work_labor_rate, 2);

            // Find the team that worked on this container
            $containerId = $ew['container_id'] ?? null;
            $workers = $containerId
                ? $this->getWorkersForContainer($containerId)
                : $this->getAllJobWorkers($worksheet->job);

            $perWorker = $workers->count() > 0
                ? round($laborAmount / $workers->count(), 2)
                : 0;

            foreach ($workers as $worker) {
                // Add to existing payment or create new entry
                $payment = WorkerPayment::where('worker_id', $worker->id)
                    ->where('worksheet_id', $worksheet->id)
                    ->first();

                if ($payment) {
                    $payment->extras_amount += $perWorker;
                    $payment->total_amount  += $perWorker;
                    $payment->save();
                }
            }
        }
    }

    /**
     * Waiting time labor split equally among all workers present.
     */
    private function calculateWaitingTime(Worksheet $worksheet, string $weekStart): void
    {
        $waitingTime = $worksheet->waiting_time ?? [];
        if (empty($waitingTime)) return;

        $price = $this->getPriceForJob($worksheet->job);
        if (!$price) return;

        $totalHours = collect($waitingTime)->sum('hours');
        $laborAmount = round($totalHours * (float) $price->waiting_time_labor_rate, 2);
        $workers = $this->getAllJobWorkers($worksheet->job);

        if ($workers->count() === 0) return;

        $perWorker = round($laborAmount / $workers->count(), 2);

        foreach ($workers as $worker) {
            $payment = WorkerPayment::where('worker_id', $worker->id)
                ->where('worksheet_id', $worksheet->id)
                ->first();
            if ($payment) {
                $payment->extras_amount += $perWorker;
                $payment->total_amount  += $perWorker;
                $payment->save();
            }
        }
    }

    private function getPriceForJob($job): ?ClientPrice
    {
        $firstContainer = $job->containers->first();
        if (!$firstContainer) return null;
        return ClientPrice::forSiteAndProduct($job->site_id, $firstContainer->product_id);
    }

    private function getWorkersForContainer(int $containerId): Collection
    {
        return \App\Models\JobContainerTeam::where('job_container_id', $containerId)
            ->with('team.workers')
            ->get()
            ->flatMap(fn($ct) => $ct->team->workers)
            ->unique('id');
    }

    private function getAllJobWorkers($job): Collection
    {
        return $job->book->workers;
    }
}
