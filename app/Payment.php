<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = "payments";
    
    protected $fillable = ['user_id', 'order_no', 'invoice_id', 'receipt_id', 'order_id', 'payment_id', 'customer_id', 'txn_date', 'amount', 'discount', 'payable_amount', 'refund_amount', 'payment_mode', 'status', 'signature', 'refunded_date'];
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function appointment()
    {
        return $this->belongsTo('App\Appointment', 'id', 'payment_id');
    }
}