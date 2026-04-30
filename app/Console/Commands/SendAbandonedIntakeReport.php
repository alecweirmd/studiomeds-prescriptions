<?php

namespace App\Console\Commands;

use App\Models\FormStart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAbandonedIntakeReport extends Command
{
    protected $signature = 'intakes:abandoned-report';

    protected $description = 'Email a daily report of abandoned intakes from the previous calendar day';

    public function handle()
    {
        $count = FormStart::whereNotNull('abandoned_at')
            ->whereNull('contacted_at')
            ->whereNull('dismissed_at')
            ->count();

        if ($count === 0) {
            return;
        }

        $body = "You currently have {$count} abandoned intakes awaiting follow-up on StudioMeds. "
            . "Log in to review them here: https://prescriptions.studiomeds.com/login";

        Mail::raw($body, function ($m) use ($count) {
            $m->to('admin@studiomeds.com')
              ->from(config('services.admin.from_email'))
              ->subject("StudioMeds - You currently have {$count} abandoned intakes awaiting follow-up");
        });
    }
}
