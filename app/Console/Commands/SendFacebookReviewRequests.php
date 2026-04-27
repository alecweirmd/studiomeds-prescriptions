<?php

namespace App\Console\Commands;

use App\Jobs\SendFacebookReviewRequest;
use App\Models\PatientsCQI;
use Illuminate\Console\Command;

class SendFacebookReviewRequests extends Command
{
    protected $signature = 'outreach:facebook-review-requests';

    protected $description = 'Queue Facebook review request emails for patients approved exactly 7 days ago';

    public function handle()
    {
        $target = now()->subDays(7)->toDateString();

        $records = PatientsCQI::where('status', 1)
            ->whereNull('review_sent_at')
            ->whereDate('updated_at', $target)
            ->whereNotNull('patient_id')
            ->get();

        foreach ($records as $cqi) {
            SendFacebookReviewRequest::dispatch($cqi->patient_id);
        }
    }
}
