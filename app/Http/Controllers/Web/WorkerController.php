<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Client;
use App\Models\Job;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Worker::withCount('restrictions')
            ->with(['visa'])
            ->orderBy('name')
            ->paginate(50);

        return view('workers.index', compact('workers'));
    }

    public function create()
    {
        return view('workers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'required|string|max:30',
            'email'        => 'nullable|email|unique:workers,email',
            'abn'          => 'nullable|string|max:20',
            'status'       => 'required|in:active,suspended,inactive',
            'is_australian'=> 'required|boolean',
            'min_weekly'   => 'nullable|numeric|min:0',
            'has_forklift' => 'boolean',
        ]);

        $worker = Worker::create([
            'name'                    => $request->name,
            'phone'                   => $request->phone,
            'email'                   => $request->email,
            'abn'                     => $request->abn,
            'status'                  => $request->status,
            'is_australian'           => $request->boolean('is_australian'),
            'min_weekly'              => $request->min_weekly,
            'has_forklift'            => $request->boolean('has_forklift'),
            'forklift_licence_number' => $request->forklift_licence_number,
            'forklift_expiry'         => $request->forklift_expiry ?: null,
            'forklift_state'          => $request->forklift_state,
        ]);

        // Create visa if not Australian
        if (!$request->boolean('is_australian') && $request->visa_valid_until) {
            $worker->visa()->create([
                'visa_class'               => explode(' ', $request->visa_class)[0],
                'valid_until'              => $request->visa_valid_until,
                'work_permitted'           => $request->boolean('visa_work_permitted', true),
                'fortnightly_hours_limit'  => $request->visa_fortnightly_hours_limit,
            ]);
        }

        return redirect()
            ->route('workers.show', $worker)
            ->with('success', 'Worker created successfully.');
    }

    public function show(Worker $worker)
    {
        $worker->load(['visa', 'inductions', 'restrictions']);
        $clients    = Client::where('is_active', true)->with('activeSites')->orderBy('name')->get();
        $sites      = \App\Models\Site::where('is_active', true)->get();
        $recentJobs = Job::whereHas('book.workers', fn($q) => $q->where('workers.id', $worker->id))
            ->with(['site.client'])
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('workers.edit', compact('worker', 'clients', 'sites', 'recentJobs'));
    }

    public function update(Request $request, Worker $worker)
    {
        $request->validate([
            'name'   => 'required|string|max:100',
            'phone'  => 'required|string|max:30',
            'email'  => 'nullable|email|unique:workers,email,'.$worker->id,
            'status' => 'required|in:active,suspended,inactive',
        ]);

        $worker->update([
            'name'                    => $request->name,
            'phone'                   => $request->phone,
            'email'                   => $request->email,
            'abn'                     => $request->abn,
            'status'                  => $request->status,
            'suspension_reason'       => $request->suspension_reason,
            'return_date'             => $request->return_date ?: null,
            'is_australian'           => $request->boolean('is_australian'),
            'min_weekly'              => $request->min_weekly,
            'has_forklift'            => $request->boolean('has_forklift'),
            'forklift_licence_number' => $request->forklift_licence_number,
            'forklift_expiry'         => $request->forklift_expiry ?: null,
            'forklift_state'          => $request->forklift_state,
        ]);

        // Update visa
        if (!$request->boolean('is_australian') && $request->visa_valid_until) {
            $worker->visa()->updateOrCreate(
                ['worker_id' => $worker->id],
                [
                    'visa_class'              => explode(' ', $request->visa_class)[0],
                    'valid_until'             => $request->visa_valid_until,
                    'work_permitted'          => $request->boolean('visa_work_permitted', true),
                    'fortnightly_hours_limit' => $request->visa_fortnightly_hours_limit,
                ]
            );
        }

        // Update inductions
        $clients = Client::all();
        foreach ($clients as $client) {
            $completed = isset($request->inductions[$client->id]);
            $worker->inductions()->updateOrCreate(
                ['client_id' => $client->id],
                ['completed' => $completed, 'completed_at' => $completed ? now() : null]
            );
        }

        return back()->with('success', 'Worker updated successfully.');
    }
}
