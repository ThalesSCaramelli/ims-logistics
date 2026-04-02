<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\Book;
use App\Jobs\SendPushNotification;
use App\Jobs\SendSmsNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private function isConfigured(): bool
    {
        return !empty(config('services.firebase.project_id')) &&
               !empty(config('services.twilio.sid'));
    }

    /**
     * Notify workers — Push + SMS always together.
     * Silent no-op when credentials are not configured (development).
     */
    public function notifyWorkers(Collection|array $workers, string $event, array $data = []): void
    {
        if (!$this->isConfigured()) {
            Log::info('[IMS] Notifications skipped — credentials not configured', [
                'event'   => $event,
                'workers' => collect($workers)->pluck('name')->toArray(),
            ]);
            return;
        }

        collect($workers)->each(function (Worker $worker) use ($event, $data) {
            if ($worker->user?->fcm_token && $worker->user?->push_notifications) {
                SendPushNotification::dispatch($worker, $event, $data);
            }
            if ($worker->phone && $worker->user?->sms_notifications) {
                SendSmsNotification::dispatch($worker, $event, $data);
            }
        });
    }

    /**
     * Send book notifications manually.
     * Office calls this when all books for the day are finalized.
     */
    public function sendBookNotifications(Book $book, int $userId): void
    {
        $book->update(['notified_at' => now(), 'notified_by' => $userId]);

        $this->notifyWorkers($book->workers, 'book_created', [
            'date' => $book->date->format('D d M Y'),
            'jobs' => $book->jobs->map(fn($j) => [
                'time'   => $j->start_time,
                'client' => $j->site->client->name,
                'site'   => $j->site->name,
            ])->toArray(),
        ]);
    }

    /**
     * Send notifications for all unnotified books on a date.
     * Called from dashboard "Send all notifications" button.
     */
    public function sendAllPendingNotifications(string $date, int $userId): int
    {
        $books = Book::where('date', $date)
            ->whereNull('notified_at')
            ->where('status', '!=', 'cancelled')
            ->with(['workers.user', 'jobs.site.client'])
            ->get();

        foreach ($books as $book) {
            $this->sendBookNotifications($book, $userId);
        }

        return $books->count();
    }

    public function jobCancelled(\App\Models\Job $job): void
    {
        $this->notifyWorkers($job->book->workers, 'job_cancelled', [
            'date'   => $job->date->format('D d M Y'),
            'time'   => $job->start_time,
            'client' => $job->site->client->name,
            'site'   => $job->site->name,
        ]);
    }

    public function teamLeaderChanged(\App\Models\Job $job, \App\Models\Worker $newTL): void
    {
        $this->notifyWorkers($job->book->workers, 'team_leader_changed', [
            'job_date' => $job->date->format('D d M Y'),
            'new_tl'   => $newTL->name,
            'client'   => $job->site->client->name,
        ]);
    }

    public function paymentProcessed(\App\Models\WorkerPayment $payment): void
    {
        $this->notifyWorkers(collect([$payment->worker]), 'payment_processed', [
            'amount'      => '$' . number_format($payment->total_amount, 2),
            'week_period' => $payment->week_period->format('d M Y'),
        ]);
    }
}
