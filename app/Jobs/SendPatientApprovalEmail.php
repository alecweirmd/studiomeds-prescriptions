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
use Illuminate\Support\Facades\Storage;

class SendPatientApprovalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    protected int $patientId;

    /**
     * Optional issuance record [{key, name, path}] of stored prescription PDFs.
     * When provided and the files exist, attachments are served from disk
     * (preserving the issued document); otherwise PDFs are regenerated fresh.
     */
    protected ?array $storedPaths;

    public function __construct(int $patientId, ?array $storedPaths = null)
    {
        $this->patientId   = $patientId;
        $this->storedPaths = $storedPaths;
    }

    public function handle(): void
    {
        $patient = Patients::findOrFail($this->patientId);

        $procedure = $patient->procedure_type;

        if ($procedure === 'lip_blush' || $procedure === 'eyeliner') {
            [$file, $cleanup] = $this->resolveAttachment('zensa', 'pdf/zensa', $patient);

            Mail::send('emails/patient_approved_facial', ['patient' => $patient], function ($message) use ($patient, $file) {
                $message->to($patient->email)
                    ->subject('Your StudioMeds prescription is ready')
                    ->attach($file, ['as' => 'Zensa Prescription.pdf']);
            });

            if ($cleanup) {
                @unlink($file);
            }
            return;
        }

        // Default branch: tattoo, brow_pmu, or unset procedure_type (legacy patients)
        [$file1, $cleanup1] = $this->resolveAttachment('bactine', 'pdf/bactine', $patient);
        [$file2, $cleanup2] = $this->resolveAttachment('lidocaine', 'pdf/lidocaine', $patient);

        Mail::send('emails/patient_approved', ['patient' => $patient], function ($message) use ($patient, $file1, $file2) {
            $message->to($patient->email)
                ->subject('Your Medications Are Approved')
                ->attach($file1, ['as' => 'Bactine.pdf'])
                ->attach($file2, ['as' => 'Lidocaine Cream.pdf']);
        });

        if ($cleanup1) {
            @unlink($file1);
        }
        if ($cleanup2) {
            @unlink($file2);
        }
    }

    /**
     * Resolve the absolute path to attach for a document. Prefers the stored
     * PDF when available; otherwise regenerates to a temp file.
     *
     * @return array{0:string, 1:bool} [absolutePath, shouldUnlinkAfterSend]
     */
    private function resolveAttachment(string $key, string $view, Patients $patient): array
    {
        foreach ($this->storedPaths ?? [] as $doc) {
            if (($doc['key'] ?? null) === $key && !empty($doc['path']) && Storage::exists($doc['path'])) {
                return [Storage::path($doc['path']), false];
            }
        }

        $file = storage_path("app/{$key}_{$this->patientId}.pdf");
        Pdf::loadView($view, ['patient' => $patient])->save($file);

        return [$file, true];
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Patient approval email failed for patient {$this->patientId}: " . $e->getMessage());

        // Surface the failure in the unified Alerts tab so an admin can resend
        // or mark it handled out-of-band (Audit Finding #9). Each genuine failure
        // creates its own alert — repeated failures on a patient are signal, not
        // noise. Alert creation must never throw out of failed(): if the DB is
        // unavailable, the Log::error above is still the durable record.
        try {
            \App\Models\Alert::create([
                'type'           => 'email_delivery_failed',
                'alertable_type' => Patients::class,
                'alertable_id'   => $this->patientId,
                'metadata'       => [
                    'failure_reason' => $e->getMessage(),
                    'attempt_count'  => $this->attempts(),
                    'failed_at'      => now()->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $alertException) {
            Log::critical(
                "Failed to create email_delivery_failed alert for patient {$this->patientId}: "
                . $alertException->getMessage()
            );
        }
    }
}
