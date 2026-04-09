<?php

namespace App\Jobs;

use App\Models\Patients;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendArtistApprovalEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    protected int $patientId;
    protected int $artistId;

    public function __construct(int $patientId, int $artistId)
    {
        $this->patientId = $patientId;
        $this->artistId  = $artistId;
    }

    public function handle(): void
    {
        $patient = Patients::findOrFail($this->patientId);
        $artist  = User::findOrFail($this->artistId);

        $file1 = storage_path("app/artist_{$this->patientId}_bactine.pdf");
        $file2 = storage_path("app/artist_{$this->patientId}_lidocaine.pdf");

        Pdf::loadView('pdf/bactine', ['patient' => $patient])->save($file1);
        Pdf::loadView('pdf/lidocaine', ['patient' => $patient])->save($file2);

        Mail::send('emails/artist_approved', ['patient' => $patient, 'artist' => $artist], function ($message) use ($artist, $file1, $file2) {
            $message->to($artist->email)
                ->subject('Your Medications Are Approved')
                ->attach($file1, ['as' => 'Bactine.pdf'])
                ->attach($file2, ['as' => 'Lidocaine Cream.pdf']);
        });

        @unlink($file1);
        @unlink($file2);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("Artist approval email failed for patient {$this->patientId}, artist {$this->artistId}: " . $e->getMessage());
    }
}
