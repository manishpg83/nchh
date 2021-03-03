<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class SendAppointmentReminder extends Mailable
{
    use Queueable, SerializesModels;
    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details; 
        $this->subject = $details['subject']; 
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    { 
          return $this->view('emails.send_appointment_reminder')->subject($this->subject)->with($this->details);
    }
}
