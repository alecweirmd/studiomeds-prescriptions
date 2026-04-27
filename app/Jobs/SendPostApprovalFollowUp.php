<?php

namespace App\Jobs;

use App\Models\Patients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPostApprovalFollowUp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    protected int $patientId;

    public function __construct(int $patientId)
    {
        $this->patientId = $patientId;
    }

    public function handle(): void
    {
        $patient = Patients::with('patientsCQI')->findOrFail($this->patientId);

        if (!$patient->email) {
            return;
        }

        Mail::send('emails/post_approval_followup', ['patient' => $patient], function ($message) use ($patient) {
            $message->to($patient->email)
                ->subject('Checking in from StudioMeds');
        });

        if ($patient->patientsCQI) {
            $patient->patientsCQI->follow_up_sent_at = now();
            $patient->patientsCQI->save();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Post-approval follow-up failed for patient {$this->patientId}: " . $e->getMessage());
    }
}
