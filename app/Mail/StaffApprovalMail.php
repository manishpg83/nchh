<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;   

class StaffApprovalMail extends Mailable
{
    use Queueable, SerializesModels;
    
    private $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($record)
    {
        $this->data = $record;
        $this->subject = $record['subject'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    { 
       return $this->view('emails.staff-approval')->subject($this->subject)->with($this->data);
    }
}