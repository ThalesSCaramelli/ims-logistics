<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        $operations = $request->validate(['operations' => 'required|array'])['operations'];
        $results    = [];

        foreach ($operations as $op) {
            try {
                $results[] = $this->processOperation($op);
            } catch (\Exception $e) {
                $results[] = ['id' => $op['id'] ?? null, 'success' => false, 'error' => $e->getMessage()];
            }
        }

        return response()->json(['results' => $results]);
    }

    private function processOperation(array $op): array
    {
        return match($op['type']) {
            'save_draft'     => $this->handleSaveDraft($op),
            'save_signature' => $this->handleSaveSignature($op),
            'create_team'    => $this->handleCreateTeam($op),
            default          => ['id' => $op['id'], 'success' => false, 'error' => 'Unknown operation type'],
        };
    }

    private function handleSaveDraft(array $op): array
    {
        $job       = Job::findOrFail($op['job_id']);
        $worksheet = $job->worksheet()->firstOrCreate(['job_id' => $job->id]);
        $worksheet->update(['filled_data' => $op['data'], 'sync_status' => 'synced', 'synced_at' => now()]);
        return ['id' => $op['id'], 'success' => true];
    }

    private function handleSaveSignature(array $op): array
    {
        $job = Job::findOrFail($op['job_id']);
        if ($job->worksheet) {
            $job->worksheet->update([
                'client_signature_type' => 'digital',
                'client_signature_data' => $op['signature_data'],
                'client_signed_by'      => $op['signed_by'],
            ]);
        }
        return ['id' => $op['id'], 'success' => true];
    }

    private function handleCreateTeam(array $op): array
    {
        $job  = Job::findOrFail($op['job_id']);
        $team = $job->teams()->create(['name' => $op['name'], 'created_by' => $op['worker_id']]);
        $team->workers()->attach($op['worker_ids']);
        return ['id' => $op['id'], 'success' => true, 'team_id' => $team->id];
    }
}
