<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobTeam;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function store(Request $request, Job $job)
    {
        abort_unless($job->isTeamLeader($request->user()->worker_id), 403, 'Only the Team Leader can create teams.');

        $request->validate([
            'name'       => 'required|string',
            'worker_ids' => 'required|array',
        ]);

        $team = $job->teams()->create([
            'name'       => $request->name,
            'created_by' => $request->user()->worker_id,
        ]);

        $team->workers()->attach($request->worker_ids);

        return response()->json(['team' => $team->load('workers')]);
    }

    public function update(Request $request, Job $job, JobTeam $team)
    {
        abort_unless($job->isTeamLeader($request->user()->worker_id), 403);
        $team->workers()->sync($request->worker_ids);
        return response()->json(['team' => $team->load('workers')]);
    }
}
