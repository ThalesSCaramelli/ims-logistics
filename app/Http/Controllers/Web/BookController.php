<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Worker;
use App\Models\Client;
use App\Models\DayDemand;
use App\Services\BookService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(
        private BookService $bookService,
        private NotificationService $notifications
    ) {}

    public function create()
    {
        $workers = Worker::where('status', '!=', 'inactive')
            ->with(['visa', 'inductions', 'restrictions'])
            ->orderBy('name')
            ->get();

        $clients = Client::where('is_active', true)
            ->with('activeSites')
            ->orderBy('name')
            ->get();

        // Load demand if coming from Planning page
        $demand = null;
        if (request('demand_id')) {
            $demand = DayDemand::with(['client', 'site', 'product'])->find(request('demand_id'));
        }

        return view('books.create', compact('workers', 'clients', 'demand'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'                                 => 'required|date',
            'worker_ids'                           => 'required|array|min:1',
            'worker_ids.*'                         => 'exists:workers,id',
            'jobs'                                 => 'required|array|min:1',
            'jobs.*.site_id'                       => 'required|exists:sites,id',
            'jobs.*.start_time'                    => 'required',
            'jobs.*.team_leader_id'                => 'required|exists:workers,id',
            'jobs.*.containers'                    => 'nullable|array',
            'jobs.*.containers.*.container_number' => 'nullable|string|max:20',
            'jobs.*.containers.*.feet'             => 'nullable|in:20,40',
            'demand_id'                            => 'nullable|exists:day_demands,id',
        ]);

        $book = Book::create([
            'date'       => $request->date,
            'status'     => 'scheduled',
            'notes'      => $request->notes,
            'created_by' => auth()->id(),
            'demand_id'  => $request->demand_id ?: null,
        ]);

        $book->workers()->attach($request->worker_ids);

        foreach ($request->jobs as $jobData) {
            $job = $book->jobs()->create([
                'site_id'        => $jobData['site_id'],
                'date'           => $request->date,
                'start_time'     => $jobData['start_time'],
                'team_leader_id' => $jobData['team_leader_id'],
                'status'         => 'scheduled',
                'notes'          => $jobData['notes'] ?? null,
            ]);

            foreach ($jobData['containers'] ?? [] as $containerData) {
                if (empty(trim($containerData['container_number'] ?? ''))) continue;
                $job->containers()->create([
                    'container_number' => strtoupper(trim($containerData['container_number'])),
                    'feet'             => $containerData['feet'] ?? '40',
                    'status'           => 'pending',
                ]);
            }
        }

        // Link to demand and increment crew count
        if ($request->demand_id) {
            $demand = DayDemand::find($request->demand_id);
            if ($demand) $demand->incrementCrews();
        }

        return redirect()
            ->route('dashboard', ['date' => $request->date])
            ->with('success', 'Book created. Send notifications when all books for the day are ready.');
    }

    public function notify(Book $book)
    {
        $book->load(['workers.user', 'jobs.site.client']);
        $this->notifications->sendBookNotifications($book, auth()->id());
        return back()->with('success', 'Notifications sent to ' . $book->workers->count() . ' workers.');
    }

    public function notifyAll(Request $request)
    {
        $date  = $request->date ?? today()->toDateString();
        $count = $this->notifications->sendAllPendingNotifications($date, auth()->id());
        if ($count === 0) return back()->with('info', 'All books for this day have already been notified.');
        return back()->with('success', $count . ' book(s) notified for ' . $date . '.');
    }

    public function workerAlerts(Request $request)
    {
        $worker    = Worker::with(['visa', 'inductions', 'restrictions'])->findOrFail($request->worker_id);
        $client    = Client::findOrFail($request->client_id);
        $date      = $request->date ?? today()->toDateString();
        $alerts    = $this->bookService->getWorkerAlerts($worker, $client, $date);
        $weekStart = now()->startOfWeek()->toDateString();

        return response()->json([
            'alerts'       => $alerts,
            'below_min'    => $this->bookService->workerBelowWeeklyMinimum($worker, $weekStart),
            'has_forklift' => $worker->has_forklift,
        ]);
    }
}
