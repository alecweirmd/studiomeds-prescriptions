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
        $yesterday = now()->subDay()->toDateString();

        $count = FormStart::whereDate('abandoned_at', $yesterday)->count();

        if ($count === 0) {
            return;
        }

        $dateLabel = now()->subDay()->format('F j, Y');

        $body = "Yesterday ({$dateLabel}) there were {$count} abandoned intakes on StudioMeds. "
            . "Log in to review them here: https://prescriptions.studiomeds.com/login";

        Mail::raw($body, function ($m) {
            $m->to('admin@studiomeds.com')
              ->from(config('services.admin.from_email'))
              ->subject('StudioMeds - Abandoned Intake Report');
        });
    }
}
