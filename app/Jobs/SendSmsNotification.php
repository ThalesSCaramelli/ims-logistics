<?php

namespace App\Jobs;

use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Worker $worker,
        public string $event,
        public array $data = []
    ) {}

    public function handle(): void
    {
        if (!$this->worker->phone) return;

        $body = $this->buildSmsBody();

        try {
            $twilio = new TwilioClient(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create($this->worker->phone, [
                'from' => config('services.twilio.from'),
                'body' => $body,
            ]);
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed', ['worker' => $this->worker->id, 'error' => $e->getMessage()]);
            throw $e; // Allow retry
        }
    }

    private function buildSmsBody(): string
    {
        return match($this->event) {
            'book_created'        => "IMS: You have been allocated on {$this->data['date']}. Check the app for details.",
            'job_cancelled'       => "IMS: Job cancelled — {$this->data['client']} on {$this->data['date']} at {$this->data['time']}.",
            'team_leader_changed' => "IMS: Team Leader changed for {$this->data['client']} job. New TL: {$this->data['new_tl']}.",
            'payment_processed'   => "IMS: Payment of {$this->data['amount']} processed for week of {$this->data['week_period']}.",
            default               => "IMS: You have a new notification. Please check the app.",
        };
    }
}
