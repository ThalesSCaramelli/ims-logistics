<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Verifies that the authenticated worker is the Team Leader of the job.
 * TL is not a system role — it's verified per job via job.team_leader_id.
 */
class EnsureTeamLeader
{
    public function handle(Request $request, Closure $next)
    {
        $job = $request->route('job');

        if (!$job) {
            return response()->json(['message' => 'Job not found.'], 404);
        }

        $workerId = $request->user()?->worker_id;

        if ($job->team_leader_id !== $workerId) {
            return response()->json([
                'message' => 'Only the Team Leader of this job can perform this action.'
            ], 403);
        }

        return $next($request);
    }
}
