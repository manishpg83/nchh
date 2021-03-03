<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Payment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Invoice;

class BookAppointment extends Mailable
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
        $payment = Payment::find($this->data['payment_id']);
         if (!empty($payment) && !empty($payment->invoice_id)) {
            $invoice_filename = 'invoice_' . $payment->invoice_id . '.pdf';
            // File::exists($myfile);
            $invoice_filepath = storage_path('app/invoice/' . $invoice_filename);
            if (File::exists($invoice_filepath)) {
            } else {
                if (!empty($payment->appointment)) {

                    $price = $payment->appointment->payment->amount - ($payment->appointment->payment->amount * 0.18);
                    $gst = $payment->appointment->payment->amount  - $price;
                    
                    $invoice = new Invoice();
                    $output = $invoice->generate('front.invoice.book_appointment', ['appointment' => $payment->appointment, 'price' => $price, 'gst' => $gst]);
                }
                Storage::put('invoice/' . $invoice_filename, $output);
                $invoice_filepath = storage_path('app/invoice/' . $invoice_filename);
            }
            return $this->view('emails.book_appointment')->subject($this->subject)->with($this->data)->attach($invoice_filepath, ['as' => $invoice_filename, 'mime' => 'application/pdf']);
        }
    }
}
