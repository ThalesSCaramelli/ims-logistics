<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Worksheet;
use App\Models\Worker;
use App\Models\Product;
use App\Models\ContainerWorker;
use App\Models\WorksheetService;
use App\Models\ClientContainerPrice;
use App\Models\ClientContainerAdditional;
use App\Models\JobContainerAdditional;
use App\Models\ClientHourlyRate;
use App\Models\SpecialDay;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WorksheetController extends Controller
{
    public function index(Request $request)
    {
        $week = $request->get('week', now()->format('Y-\WW'));

        [$year, $isoWeek] = str_contains($week, '-W')
            ? explode('-W', $week)
            : explode('-', $week);

        $weekStart = Carbon::now()->setISODate((int)$year, (int)$isoWeek)->startOfWeek();
        $weekEnd   = $weekStart->copy()->endOfWeek();

        $worksheets = Worksheet::whereBetween('created_at', [$weekStart, $weekEnd])
            ->with(['job.site.client', 'job.book.workers', 'job.containers'])
            ->orderByRaw("CASE sync_status WHEN 'pending' THEN 1 WHEN 'draft' THEN 2 WHEN 'approved' THEN 3 WHEN 'paid' THEN 4 END")
            ->orderByDesc('created_at')
            ->get();

        $kpis = [
            'pending'       => $worksheets->filter(fn($w) => ($w->sync_status?->value ?? $w->sync_status) === 'pending')->count(),
            'approved_week' => $worksheets->filter(fn($w) => ($w->sync_status?->value ?? $w->sync_status) === 'approved')->count(),
            'paid_week'     => $worksheets->filter(fn($w) => ($w->sync_status?->value ?? $w->sync_status) === 'paid')->count(),
            'pending_value' => 0,
        ];

        return view('worksheets.index', compact('worksheets', 'kpis', 'week'));
    }

    public function show(Worksheet $worksheet)
    {
        $worksheet->load([
            'job.site.client',
            'job.book.workers',
            'job.containers.product',
            'job.containers.workers.worker',
            'job.containers.additionals',
            'services',
        ]);

        $client      = $worksheet->job->site->client;
        $bookWorkers = $worksheet->job->book->workers;

        // Workers already on containers + book workers
        $containerWorkerIds = ContainerWorker::whereIn(
            'container_id', $worksheet->job->containers->pluck('id')
        )->pluck('worker_id')->unique();

        $allWorkers = Worker::whereIn('id',
            $containerWorkerIds->merge($bookWorkers->pluck('id'))->unique()
        )->orderBy('name')->get();

        // Products filtered by client prices per feet
        $productsByFeet = [
            '20' => $this->clientProductsForFeet($client->id, '20'),
            '40' => $this->clientProductsForFeet($client->id, '40'),
        ];

        // Client additionals available for this client
        $clientAdditionals = ClientContainerAdditional::where('client_id', $client->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Serialize for JS
        $workersJson = $allWorkers->map(function($w) {
            $parts = explode(' ', $w->name);
            $initials = strtoupper(substr($parts[0],0,1)) . (isset($parts[1]) ? strtoupper(substr($parts[1],0,1)) : '');
            return ['id' => $w->id, 'initials' => $initials, 'first' => $parts[0]];
        })->values();

       $isApproved = !is_null($worksheet->approved_at);

        if ($isApproved && $worksheet->filled_data) {
            $preview = json_decode($worksheet->filled_data, true);
        } else {
            $preview = $this->buildPreview($worksheet, $allWorkers);
        }

        return view('worksheets.show', compact(
            'worksheet', 'productsByFeet', 'bookWorkers', 'allWorkers',
            'preview', 'workersJson', 'clientAdditionals', 'isApproved'
        ));
    }

    public function save(Request $request, Worksheet $worksheet)
    {
        if (!is_null($worksheet->approved_at)) {
            return back()->with('error', 'This worksheet has already been approved and cannot be edited.');
        }

        $errors = [];
        $seenNumbers = [];

        // Collect all container numbers from existing + new
        foreach ($request->containers ?? [] as $cid => $data) {
            $num = trim($data['container_number'] ?? '');
            if (empty($num)) {
                $errors[] = "All containers must have a container number.";
                break;
            }
            $upper = strtoupper($num);
            if (isset($seenNumbers[$upper])) {
                $errors[] = "Container number \"{$num}\" appears more than once in this worksheet.";
            }
            $seenNumbers[$upper] = true;

            // Validate split total
            if (!empty($data['split'])) {
                $total = round(collect($data['parts'] ?? [])->sum(fn($p) => floatval($p['qty'] ?? 0)), 2);
                if (abs($total - 1.0) > 0.01) {
                    $errors[] = "Container {$num}: split parts must total 1.0 (currently {$total}).";
                }
            }
        }

        foreach ($request->new_containers ?? [] as $idx => $data) {
            $num = trim($data['container_number'] ?? '');
            if (empty($num)) {
                $errors[] = "All new containers must have a container number.";
                break;
            }
            $upper = strtoupper($num);
            if (isset($seenNumbers[$upper])) {
                $errors[] = "Container number \"{$num}\" appears more than once in this worksheet.";
            }
            $seenNumbers[$upper] = true;

            if (!empty($data['split'])) {
                $total = round(collect($data['parts'] ?? [])->sum(fn($p) => floatval($p['qty'] ?? 0)), 2);
                if (abs($total - 1.0) > 0.01) {
                    $errors[] = "Container {$num}: split parts must total 1.0 (currently {$total}).";
                }
            }
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $worksheet->load(['job.containers']);

        // Save existing containers (keyed by real DB id)
        foreach ($request->containers ?? [] as $containerId => $data) {
            $container = $worksheet->job->containers->find($containerId);
            if (!$container) continue;

            $container->update([
                'container_number'  => $data['container_number'] ?? $container->container_number,
                'feet'              => $data['feet'] ?? $container->feet,
                'product_id'        => $data['product_id'] ?: null,
                'boxes_count'       => $data['boxes_count'] ?? null,
                'skills_count'      => $data['skills_count'] ?? null,
                'description_extra' => $data['description'] ?? null,
            ]);

            $this->saveContainerWorkers($containerId, $data);
            $this->saveContainerAdditionals($containerId, $data);
        }

        // Create new containers added via JS (keyed by temp index)
        foreach ($request->new_containers ?? [] as $idx => $data) {
            if (empty($data['container_number']) && empty($data['boxes_count'])) continue;

            $feet = str_replace('ft', '', $data['feet'] ?? '40');

            $container = \App\Models\JobContainer::create([
                'job_id'            => $worksheet->job_id,
                'container_number'  => $data['container_number'] ?? null,
                'feet'              => $feet,
                'product_id'        => $data['product_id'] ?: null,
                'boxes_count'       => $data['boxes_count'] ?? null,
                'skills_count'      => $data['skills_count'] ?? null,
                'description_extra' => $data['description'] ?? null,
                'status'            => 'pending',
            ]);

            $this->saveContainerWorkers($container->id, $data);
            $this->saveContainerAdditionals($container->id, $data);
        }

        // Rebuild services
        WorksheetService::where('worksheet_id', $worksheet->id)->delete();

        foreach ($request->services ?? [] as $svc) {
            if (empty($svc['hours'])) continue;
            WorksheetService::create([
                'worksheet_id' => $worksheet->id,
                'service_type' => $svc['service_type'],
                'hours'        => $svc['hours'],
                'description'  => $svc['description'] ?? null,
            ]);
        }

        $worksheet->update(['observations' => $request->observations]);

        return back()->with('success', 'Worksheet saved.');
    }

    public function approve(Request $request, Worksheet $worksheet)
    {
        if (!is_null($worksheet->approved_at)) {
            return back()->with('error', 'This worksheet is already approved.');
        }

        // Save first
        $saveRequest = $request;
        $this->save($saveRequest, $worksheet);

        // Reload fresh
        $worksheet->load([
            'job.site.client',
            'job.book.workers',
            'job.containers.product',
            'job.containers.workers.worker',
            'services',
        ]);

        $allWorkers = Worker::whereIn('id',
            ContainerWorker::whereIn('container_id', $worksheet->job->containers->pluck('id'))
                ->pluck('worker_id')->unique()
                ->merge($worksheet->job->book->workers->pluck('id'))
                ->unique()
        )->get();

        $calc = $this->calculatePayments($worksheet, $allWorkers);

        $worksheet->update([
            'sync_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'filled_data' => json_encode($calc),
        ]);

        return back()->with('success', 'Worksheet approved — payment generated.');
    }

    public function addWorker(Request $request, Worksheet $worksheet)
    {
        $request->validate(['worker_id' => 'required|exists:workers,id']);

        $book = $worksheet->job->book;

        if (!$book->workers()->where('worker_id', $request->worker_id)->exists()) {
            $book->workers()->attach($request->worker_id);
        }

        return back()->with('success', 'Worker added.');
    }
    // ── Container workers helper ──────────────────────────────────────
    private function saveContainerWorkers(int $containerId, array $data): void
    {
        ContainerWorker::where('container_id', $containerId)->delete();

        if (!empty($data['split'])) {
            foreach ($data['parts'] ?? [] as $partIdx => $part) {
                $qty = floatval($part['qty'] ?? 1);
                foreach ($part['workers'] ?? [] as $workerId) {
                    ContainerWorker::create([
                        'container_id' => $containerId,
                        'worker_id'    => $workerId,
                        'part'         => $partIdx + 1,
                        'qty'          => $qty,
                    ]);
                }
            }
        } else {
            foreach ($data['workers'] ?? [] as $workerId) {
                ContainerWorker::create([
                    'container_id' => $containerId,
                    'worker_id'    => $workerId,
                    'part'         => 1,
                    'qty'          => 1.00,
                ]);
            }
        }
    }

    private function saveContainerAdditionals(int $containerId, array $data): void
    {
        JobContainerAdditional::where('container_id', $containerId)->delete();

        foreach ($data['additionals'] ?? [] as $additionalId) {
            JobContainerAdditional::create([
                'container_id'  => $containerId,
                'additional_id' => $additionalId,
            ]);
        }
    }

    // ── Calculation ───────────────────────────────────────────────────

    private function buildPreview(Worksheet $worksheet, $allWorkers): array
    {
        try {
            return $this->calculatePayments($worksheet, $allWorkers);
        } catch (\Throwable $e) {
            return [
                'error'         => $e->getMessage(),
                'client_lines'  => [],
                'client_total'  => 0,
                'worker_totals' => [],
                'is_holiday'    => false,
            ];
        }
    }

    private function calculatePayments(Worksheet $worksheet, $allWorkers): array
    {
        $job    = $worksheet->job;
        $client = $job->site->client;

        $isHoliday = SpecialDay::where('date', $job->date ?? today())
            ->where('is_active', true)->exists();

        $clientLines    = [];
        $workerEarnings = $allWorkers->mapWithKeys(fn($w) => [$w->id => 0.0])->toArray();

        // ── Containers ─────────────────────────────────────────────
        foreach ($job->containers as $container) {
            // Find price: prefer product-specific, fallback to Standard (null product)
            $price = ClientContainerPrice::where('client_id', $client->id)
                ->where('feet', $container->feet)
                ->where(function ($q) use ($container) {
                    $q->where('product_id', $container->product_id)
                      ->orWhereNull('product_id');
                })
                ->orderByRaw('CASE WHEN product_id IS NULL THEN 1 ELSE 0 END')
                ->first();

            if (!$price) continue;

            // Base rates
            $clientAmt = (float) $price->client_rate;
            $workerAmt = (float) $price->worker_rate;

            // Box additionals
            if ($price->has_box_additional && $container->boxes_count > 0) {
                $ba = $price->calcBoxAdditional((int) $container->boxes_count);
                $clientAmt += $ba['client'];
                $workerAmt += $ba['worker'];
            }

            // Skill additionals
            if ($price->has_skill_additional && $container->skills_count > 0) {
                $sa = $price->calcSkillAdditional((int) $container->skills_count);
                $clientAmt += $sa['client'];
                $workerAmt += $sa['worker'];
            }

            $clientLines[] = [
                'description' => ($container->container_number ?? 'Container') . ' · ' . $container->feet . 'ft',
                'client'      => round($clientAmt, 2),
                'worker'      => round($workerAmt, 2),
            ];

            // Distribute worker pay
            $parts     = $container->workers->groupBy('part');
            $totalQty  = $parts->sum(fn($p) => $p->first()->qty ?? 1);

            foreach ($parts as $partNum => $partWorkers) {
                $qty         = (float) ($partWorkers->first()->qty ?? 1);
                $fraction    = $totalQty > 0 ? $qty / $totalQty : 0;
                $partTotal   = $workerAmt * $fraction;
                $workerCount = $partWorkers->count();

                foreach ($partWorkers as $cw) {
                    if (array_key_exists($cw->worker_id, $workerEarnings)) {
                        $workerEarnings[$cw->worker_id] += $workerCount > 0
                            ? $partTotal / $workerCount
                            : 0;
                    }
                }
            }
        }

        // ── Services ──────────────────────────────────────────────
        foreach ($worksheet->services as $svc) {
            $rate = ClientHourlyRate::where('client_id', $client->id)
                ->where('service_type', $svc->service_type)
                ->where('is_active', true)
                ->first();

            if (!$rate) continue;

            $mult      = $isHoliday ? (float) $rate->holiday_multiplier : 1.0;
            $clientAmt = $svc->hours * $rate->client_rate_per_hour * $mult;
            $workerAmt = $svc->hours * $rate->worker_rate_per_hour * $mult;

            $clientLines[] = [
                'description' => ucwords(str_replace('_', ' ', $svc->service_type)) . ' ' . $svc->hours . 'h'
                    . ($svc->description ? ' — ' . $svc->description : ''),
                'client' => round($clientAmt, 2),
                'worker' => round($workerAmt, 2),
            ];

            // Split service pay equally
            $count = count($workerEarnings);
            if ($count > 0) {
                foreach ($workerEarnings as $wid => $_) {
                    $workerEarnings[$wid] += $workerAmt / $count;
                }
            }
        }

        $clientTotal = array_sum(array_column($clientLines, 'client'));

        $workerTotals = $allWorkers
            ->filter(fn($w) => ($workerEarnings[$w->id] ?? 0) > 0)
            ->map(fn($w) => [
                'id'     => $w->id,
                'name'   => $w->name,
                'amount' => round($workerEarnings[$w->id], 2),
            ])->values()->toArray();

        return [
            'client_lines'  => $clientLines,
            'client_total'  => round($clientTotal, 2),
            'worker_totals' => $workerTotals,
            'is_holiday'    => $isHoliday,
        ];
    }

    private function clientProductsForFeet(int $clientId, string $feet): array
    {
        $prices = ClientContainerPrice::where('client_id', $clientId)
            ->where('feet', $feet)
            ->with('product')
            ->get();

        $list = [];

        foreach ($prices as $price) {
            if ($price->product_id === null) {
                $list[] = ['id' => null, 'name' => 'Standard'];
            } elseif ($price->product) {
                $list[] = ['id' => $price->product_id, 'name' => $price->product->name];
            }
        }

        return $list;
    }

    public function create(\App\Models\Job $job)
    {
        $worksheet = Worksheet::firstOrCreate(
            ['job_id' => $job->id],
            ['sync_status' => 'draft', 'observations' => null]
        );

        return redirect()->route('worksheets.show', $worksheet);
    }
}