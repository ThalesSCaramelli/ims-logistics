<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function history(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        $query = Job::with([
            'site.client',
            'book.workers',
            'containers',
            'worksheet',
        ])->orderByDesc('date');

        if ($from) $query->whereDate('date', '>=', $from);
        if ($to)   $query->whereDate('date', '<=', $to);

        $jobs = $query->paginate(30)->withQueryString();

        return view('jobs.index', compact('jobs', 'from', 'to'));
    }

    public function show(Job $job)
    {
        $job->load([
            'site.client',
            'book.workers',
            'containers.product',
            'containers.workers.worker',
            'worksheet',
        ]);

        return view('jobs.show', compact('job'));
    }

    public function updateTeamLeader(Request $request, Job $job)
    {
        $request->validate(['team_leader_id' => 'required|exists:workers,id']);

        abort_unless(
            $job->book->workers->contains('id', $request->team_leader_id),
            422,
            'The new Team Leader must be a worker allocated to this book.'
        );

        $job->update(['team_leader_id' => $request->team_leader_id]);

        return back()->with('success', 'Team Leader updated.');
    }

    public function cancel(Job $job)
    {
        $job->update(['status' => 'cancelled']);
        return back()->with('success', 'Job cancelled.');
    }
}
