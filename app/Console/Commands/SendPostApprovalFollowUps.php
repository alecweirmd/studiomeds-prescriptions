<?php

namespace App\Console\Commands;

use App\Jobs\SendPostApprovalFollowUp;
use App\Models\PatientsCQI;
use Illuminate\Console\Command;

class SendPostApprovalFollowUps extends Command
{
    protected $signature = 'outreach:post-approval-followups';

    protected $description = 'Queue follow-up emails for patients approved exactly 3 days ago';

    public function handle()
    {
        $target = now()->subDays(3)->toDateString();

        $records = PatientsCQI::where('status', 1)
            ->whereNull('follow_up_sent_at')
            ->whereDate('updated_at', $target)
            ->whereNotNull('patient_id')
            ->get();

        foreach ($records as $cqi) {
            SendPostApprovalFollowUp::dispatch($cqi->patient_id);
        }
    }
}
