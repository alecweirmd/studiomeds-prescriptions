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

        // Lip blush and eyeliner procedures do not yet have prescription PDFs attached.
        // Awaiting clinical product specification — when provided, PDF generation will be
        // added back into the facial branch below.
        $procedure = $patient->procedure_type;

        if ($procedure === 'lip_blush' || $procedure === 'eyeliner') {
            Mail::send('emails/patient_approved_facial', ['patient' => $patient], function ($message) use ($patient) {
                $message->to($patient->email)
                    ->subject('Your Medications Are Approved');
            });
            return;
        }

        // Default branch: tattoo, brow_pmu, or unset procedure_type (legacy patients)
        $file1 = storage_path("app/bactine_{$this->patientId}.pdf");
        $file2 = storage_path("app/lidocaine_{$this->patientId}.pdf");

        Pdf::loadView('pdf/bactine', ['patient' => $patient])->save($file1);
        Pdf::loadView('pdf/lidocaine', ['patient' => $patient])->save($file2);

        Mail::send('emails/patient_approved', ['patient' => $patient], function ($message) use ($patient, $file1, $file2) {
            $message->to($patient->email)
                ->subject('Your Medications Are Approved')
                ->attach($file1, ['as' => 'Bactine.pdf'])
                ->attach($file2, ['as' => 'Lidocaine Cream.pdf']);
        });

        @unlink($file1);
        @unlink($file2);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Patient approval email failed for patient {$this->patientId}: " . $e->getMessage());
    }
}
