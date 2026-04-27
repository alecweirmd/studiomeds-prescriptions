<?php

namespace App\Console\Commands;

use App\Jobs\SendRejectionReengagement;
use App\Models\PatientsCQI;
use Illuminate\Console\Command;

class SendRejectionReengagements extends Command
{
    protected $signature = 'outreach:rejection-reengagements';

    protected $description = 'Queue re-engagement emails for patients rejected exactly 90 days ago';

    public function handle()
    {
        $target = now()->subDays(90)->toDateString();

        $records = PatientsCQI::where('status', 2)
            ->whereNull('reengagement_sent_at')
            ->whereDate('updated_at', $target)
            ->whereNotNull('patient_id')
            ->get();

        foreach ($records as $cqi) {
            SendRejectionReengagement::dispatch($cqi->patient_id);
        }
    }
}
