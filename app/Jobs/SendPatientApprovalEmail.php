<?php

namespace App\Jobs;

use App\Models\Patients;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPatientApprovalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    protected int $patientId;

    public function __construct(int $patientId)
    {
        $this->patientId = $patientId;
    }

    public function handle(): void
    {
        $patient = Patients::findOrFail($this->patientId);

        $file1 = storage_path("app/patient_{$this->patientId}_bactine.pdf");
        $file2 = storage_path("app/patient_{$this->patientId}_lidocaine.pdf");

        Pdf::loadView('pdf/bactine', ['patient' => $patient])->save($file1);
        Pdf::loadView('pdf/lidocaine', ['patient' => $patient])->save($file2);

        Mail::send('emails/patient_approved', ['patient' => $patient], function ($message) use ($patient, $file1, $file2) {
            $message->to($patient->email)
                ->subject('Your Medications Are Approved')
                ->attach($file1, ['as' => 'Bactine.pdf'])
                ->attach($file2, ['as' => 'Preparation H.pdf']);
        });

        @unlink($file1);
        @unlink($file2);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Patient approval email failed for patient {$this->patientId}: " . $e->getMessage());
    }
}
