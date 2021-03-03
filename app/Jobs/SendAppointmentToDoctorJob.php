<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; 
use Illuminate\Support\Facades\Mail;
use App\Mail\SendAppointmentToDoctor;

class SendAppointmentToDoctorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new SendAppointmentToDoctor($this->details);
        Mail::to([['email' => $this->details['doctor_email'], 'name' => $this->details['doctor_name']]])->cc($this->details['manager'])->send($email);
        //Mail::to($this->details['manager'])->send($email);
    }
}
