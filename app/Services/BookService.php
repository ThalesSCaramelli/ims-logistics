<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\Client;
use App\Models\Book;
use App\Jobs\SendPushNotification;
use App\Jobs\SendSmsNotification;

class BookService
{
    /**
     * Generate all advisory alerts for a worker being added to a book.
     * Alerts are warnings only — office decides whether to proceed.
     */
    public function getWorkerAlerts(Worker $worker, Client $client, string $date): array
    {
        $alerts = [];

        // Worker already has a book on this date
        if ($worker->hasBookOn($date)) {
            $existingBook = $worker->books()->where('date', $date)->first();
            $alerts[] = [
                'type'    => 'conflict',
                'level'   => 'warning',
                'message' => "Already has a book on {$date} — check job times before allocating",
            ];
        }

        // Induction not completed for this client
        if (!$worker->hasInductionFor($client->id)) {
            $alerts[] = [
                'type'    => 'induction',
                'level'   => 'warning',
                'message' => "Has not completed induction for {$client->name}",
            ];
        }

        // Visa issues (non-Australian workers only)
        if (!$worker->is_australian && $worker->visa) {
            if ($worker->visa->isExpired()) {
                $alerts[] = [
                    'type'    => 'visa',
                    'level'   => 'danger',
                    'message' => "Visa expired on {$worker->visa->valid_until->format('d M Y')}",
                ];
            } elseif ($worker->visa->isExpiringSoon()) {
                $alerts[] = [
                    'type'    => 'visa',
                    'level'   => 'warning',
                    'message' => "Visa expires on {$worker->visa->valid_until->format('d M Y')}",
                ];
            }

            if (!$worker->visa->work_permitted) {
                $alerts[] = [
                    'type'    => 'visa',
                    'level'   => 'danger',
                    'message' => "Work not permitted under current visa",
                ];
            }
        }

        // Worker suspended
        if ($worker->isSuspended()) {
            $return = $worker->return_date
                ? " until {$worker->return_date->format('d M Y')}"
                : '';
            $alerts[] = [
                'type'    => 'suspended',
                'level'   => 'warning',
                'message' => "Worker is currently suspended{$return}",
            ];
        }

        // Site or client restriction
        // (site_id would be passed if checking per-job)
        if ($worker->hasActiveRestrictionForClient($client->id, $date)) {
            $alerts[] = [
                'type'    => 'restriction',
                'level'   => 'warning',
                'message' => "Has an active restriction for client {$client->name}",
            ];
        }

        return $alerts;
    }

    /**
     * Check if a worker has a forklift licence (shows Driver tag in book creation).
     */
    public function workerHasForklift(Worker $worker): bool
    {
        return $worker->has_forklift;
    }

    /**
     * Check if worker is below weekly minimum (shows purple badge).
     */
    public function workerBelowWeeklyMinimum(Worker $worker, string $weekStart): bool
    {
        if (!$worker->min_weekly) return false;
        return $worker->weeklyEarnings($weekStart) < (float) $worker->min_weekly;
    }
}
