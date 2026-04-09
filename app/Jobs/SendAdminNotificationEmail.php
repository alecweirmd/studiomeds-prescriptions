<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdminNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    protected string $message;
    protected int $patientId;

    public function __construct(string $message, int $patientId)
    {
        $this->message = $message;
        $this->patientId = $patientId;
    }

    public function handle(): void
    {
        $message = $this->message;

        Mail::raw($message, function ($m) {
            $m->to(config('services.admin.notification_email'))
              ->bcc(config('services.admin.bcc_email'))
              ->from(config('services.admin.from_email'))
              ->subject('New Patient Submission');
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Admin notification email job failed for patient ' . $this->patientId . ': ' . $e->getMessage());
    }
}
