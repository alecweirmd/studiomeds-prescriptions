<?php

namespace App\Console\Commands;
use App\Models\Patients;

use Illuminate\Console\Command;

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
        $patients = Patients::whereNull('first_name')->delete(); 

    }
}
