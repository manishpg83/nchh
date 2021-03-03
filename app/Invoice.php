<?php

namespace App;

use Dompdf\Dompdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class Invoice extends Model
{
    protected $pdf;

    public function __construct()
    {
        $this->pdf = new Dompdf;
    }

    public function generate($template, $data, $type = 'stream')
    {
        $invoice = View::make($template,$data)->render();
        $this->pdf->loadHtml($invoice);
        $this->pdf->render();
        /*'admin.invoice.subscription'*/
//        $this->pdf->stream('invoice.pdf');
//        $this->pdf->stream('invoice.pdf', ['Attachment' => false]);
        return $this->pdf->output();
    }
}
