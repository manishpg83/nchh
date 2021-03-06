<?php

namespace App\Console\Commands;

use App\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompleteAppointment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complete:appointment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command used to complete all appointment`s after the appointment end time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Appointment::where('status', 'create')->where('end_time', '<', Carbon::now()->setTimezone('Asia/Kolkata'))->update(['status' => 'completed']);
        
        \Log::info('Appointment status change successfully');     
    }
}
