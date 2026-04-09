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

class SendPatientRejectionEmail implements ShouldQueue
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

        Mail::send('emails/patient_rejection', ['patient' => $patient], function ($message) use ($patient) {
            $message->to($patient->email)
                ->subject('Your Prescriptions Could Not Be Approved - DO NOT REPLY TO THIS EMAIL');
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Patient rejection email failed for patient {$this->patientId}: " . $e->getMessage());
    }
}
