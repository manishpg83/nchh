<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UserWallet extends Model
{
    use SoftDeletes;
    
    protected  $table = "user_wallet";

    protected $fillable = ['user_id','appointment_id','payment_id','price','status'];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function appointment()
    {
        return $this->belongsTo('App\Appointment', 'appointment_id');
    }
    public function payment()
    {
        return $this->belongsTo('App\Payment', 'payment_id');
    } 
}
