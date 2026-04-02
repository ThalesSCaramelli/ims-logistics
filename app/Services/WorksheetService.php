<?php

namespace App\Services;

use App\Models\Worksheet;
use App\Models\Job;
use App\Enums\WorksheetStatus;

class WorksheetService
{
    public function __construct(private PaymentService $payments) {}

    /**
     * Office approves worksheet — triggers payment calculation.
     */
    public function approve(Worksheet $worksheet, int $approvedBy): void
    {
        $worksheet->update([
            'sync_status' => WorksheetStatus::Approved,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        $worksheet->job->update(['status' => 'completed']);

        // Calculate payments automatically after approval
        $this->payments->calculateFromWorksheet($worksheet);
    }

    /**
     * Office corrects a field — records full history with justification.
     */
    public function correct(Worksheet $worksheet, string $field, mixed $newValue, string $reason, int $userId): void
    {
        $oldValue = data_get($worksheet, $field);

        // Apply the correction
        $worksheet->update([$field => $newValue]);

        // Record in correction history
        $worksheet->addCorrection($field, $oldValue, $newValue, $reason, $userId);

        // Mark as corrected in sync status if still pending
        if ($worksheet->isPending()) {
            $worksheet->update(['sync_status' => 'synced']); // office corrected, stays reviewable
        }
    }

    /**
     * Override client price for a specific container.
     */
    public function overrideContainerPrice(
        \App\Models\JobContainer $container,
        float $amount,
        string $reason,
        int $userId
    ): void {
        $container->update([
            'override_client_amount' => $amount,
            'override_reason'        => $reason,
            'override_by'            => $userId,
            'override_at'            => now(),
        ]);
    }

    /**
     * Submit worksheet (TL action — verified by job.team_leader_id).
     */
    public function submit(Worksheet $worksheet, int $workerId): void
    {
        abort_unless(
            $worksheet->job->team_leader_id === $workerId,
            403,
            'Only the Team Leader of this job can submit the worksheet.'
        );

        $worksheet->update([
            'submitted_by' => $workerId,
            'submitted_at' => now(),
            'sync_status'  => WorksheetStatus::Pending,
        ]);
    }

    /**
     * Waive client signature (office) or request waiver (TL).
     */
    public function waiveSignature(Worksheet $worksheet, string $reason, int $userId): void
    {
        $worksheet->update([
            'client_signature_type' => 'waived',
            'waived_by'             => $userId,
            'waived_reason'         => $reason,
            'waived_at'             => now(),
        ]);
    }

    /**
     * Save digital signature from client.
     */
    public function saveSignature(Worksheet $worksheet, string $signatureData, string $signedBy): void
    {
        $worksheet->update([
            'client_signature_type' => 'digital',
            'client_signature_data' => $signatureData,
            'client_signed_by'      => $signedBy,
        ]);
    }
}
