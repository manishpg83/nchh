<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAppointmentToDoctor extends Mailable
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
        $this->subject = 'New Appointment Booked - '.$record['recipient_name'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.send_appointment_doctor')->subject($this->subject)->with($this->data);
    }
}
