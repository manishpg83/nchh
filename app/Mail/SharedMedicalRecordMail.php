<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SharedMedicalRecordMail extends Mailable
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
           return $this->view('emails.shared_medical_record')->subject($this->subject)->with($this->data); 
     }
}
