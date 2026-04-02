<?php

namespace App\Jobs;

use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Worker $worker,
        public string $event,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $token = $this->worker->user?->fcm_token;
        if (!$token) return;

        $message = $this->buildMessage();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type'  => 'application/json',
        ])->post(
            'https://fcm.googleapis.com/v1/projects/' . config('services.firebase.project_id') . '/messages:send',
            ['message' => ['token' => $token, 'notification' => $message, 'data' => $this->data]]
        );

        if (!$response->successful()) {
            Log::error('FCM push failed', ['worker' => $this->worker->id, 'response' => $response->body()]);
        }
    }

    private function buildMessage(): array
    {
        return match($this->event) {
            'book_created'        => ['title' => 'New book created', 'body' => "You have been allocated on {$this->data['date']}"],
            'job_cancelled'       => ['title' => 'Job cancelled', 'body' => "{$this->data['client']} — {$this->data['date']} at {$this->data['time']}"],
            'team_leader_changed' => ['title' => 'Team Leader changed', 'body' => "New TL for {$this->data['client']}: {$this->data['new_tl']}"],
            'payment_processed'   => ['title' => 'Payment processed', 'body' => "{$this->data['amount']} for week of {$this->data['week_period']}"],
            default               => ['title' => 'IMS Notification', 'body' => 'You have a new notification'],
        };
    }

    private function getAccessToken(): string
    {
        // In production, use Google Auth Library to get OAuth2 token
        return config('services.firebase.server_key');
    }
}
