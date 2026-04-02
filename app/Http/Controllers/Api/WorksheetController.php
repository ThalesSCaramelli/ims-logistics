<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Worksheet;
use App\Services\WorksheetService;
use Illuminate\Http\Request;

class WorksheetController extends Controller
{
    public function __construct(private WorksheetService $worksheetService) {}

    public function show(Request $request, Job $job)
    {
        abort_unless($job->workerParticipates($request->user()->worker_id), 403);

        $worksheet = $job->worksheet ?? new Worksheet(['job_id' => $job->id, 'sync_status' => 'draft']);
        return response()->json(['worksheet' => $worksheet]);
    }

    public function saveDraft(Request $request, Job $job)
    {
        abort_unless($job->workerParticipates($request->user()->worker_id), 403);

        $worksheet = $job->worksheet()->firstOrCreate(['job_id' => $job->id]);
        $worksheet->update([
            'filled_by'   => $request->user()->worker_id,
            'filled_data' => $request->filled_data,
            'filled_at'   => now(),
            'sync_status' => 'synced',
            'synced_at'   => now(),
        ]);

        return response()->json(['worksheet' => $worksheet]);
    }

    public function submit(Request $request, Job $job)
    {
        $workerId  = $request->user()->worker_id;
        $worksheet = $job->worksheet;

        abort_unless($worksheet, 404, 'Worksheet not found. Fill it first.');

        $this->worksheetService->submit($worksheet, $workerId);

        return response()->json(['message' => 'Worksheet submitted for office review.', 'worksheet' => $worksheet]);
    }

    public function saveSignature(Request $request, Job $job)
    {
        $request->validate(['signature_data' => 'required|string', 'signed_by' => 'required|string']);
        $worksheet = $job->worksheet;
        abort_unless($worksheet, 404);

        $this->worksheetService->saveSignature($worksheet, $request->signature_data, $request->signed_by);
        return response()->json(['message' => 'Signature saved.']);
    }

    public function waiveSignature(Request $request, Job $job)
    {
        $request->validate(['reason' => 'required|string']);
        $worksheet = $job->worksheet;
        abort_unless($worksheet, 404);

        $this->worksheetService->waiveSignature($worksheet, $request->reason, $request->user()->id);
        return response()->json(['message' => 'Signature waiver recorded.']);
    }
}
