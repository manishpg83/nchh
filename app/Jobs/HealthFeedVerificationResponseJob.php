<?php

namespace App\Jobs;

use App\Mail\HealthFeedVerificationResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class HealthFeedVerificationResponseJob implements ShouldQueue
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
        $email = new HealthFeedVerificationResponse($this->details);
        Mail::to([['email' => $this->details['recipient_email'], 'name' => $this->details['recipient_name']]])->send($email);
    }
}
