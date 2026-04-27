<?php

namespace App\Console\Commands;

use App\Models\FormStart;
use Illuminate\Console\Command;

class MarkAbandonedIntakes extends Command
{
    protected $signature = 'intakes:mark-abandoned';

    protected $description = 'Mark form_starts records older than 24 hours and not completed as abandoned';

    public function handle()
    {
        $cutoff = now()->subHours(24);

        FormStart::where('completed', false)
            ->whereNull('abandoned_at')
            ->where('started_at', '<', $cutoff)
            ->update(['abandoned_at' => now()]);
    }
}
