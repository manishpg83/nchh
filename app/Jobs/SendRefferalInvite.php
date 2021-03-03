<?php

namespace App\Jobs;

use App\Mail\SendRefferalInvite as MailSendRefferalInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRefferalInvite implements ShouldQueue
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
        $data = new MailSendRefferalInvite($this->details);
        Mail::to(['email' => $this->details['recipient_email']])->send($data);
    }
    
    /* 
    public function handleBK()
    {
        $data = new MailSendRefferalInvite($this->details);
        $recipientEmails = json_decode($this->details['recipient_emails']);
        if (!empty($recipientEmails)) {
            foreach ($recipientEmails as $key => $email) {
                Mail::to([['email' => $email]])->send($data);
            }
        }
    } */
}
