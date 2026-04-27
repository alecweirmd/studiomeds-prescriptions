<?php

namespace App\Console\Commands;
use App\Models\Patients;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanUpPatients extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * @var string
     */
    protected $signature = 'patients:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $deleted = Patients::whereNull('first_name')->where('created_at', '<', now()->subHours(2))->delete();
        Log::info('patients:cleanup deleted ' . $deleted . ' orphaned patient rows');
    }
}
