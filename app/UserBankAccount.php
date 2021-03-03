<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserBankAccount extends Model
{
    protected $table ="user_bank_account";

    protected $fillable = ['user_id', 'bank_name', 'account_number', 'ifsc_code', 'account_type', 'beneficiary_name'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function setIfscCodeAttribute($value)
    {
        $this->attributes['ifsc_code'] = Str::upper($value);
    }
    
}
