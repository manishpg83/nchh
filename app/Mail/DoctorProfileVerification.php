<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DoctorProfileVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    private $data;

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
      //  dd($this->data['message']);
         return $this->view('emails.doctor_profile_verification')->subject($this->subject)->with($this->data); 
    }
}
