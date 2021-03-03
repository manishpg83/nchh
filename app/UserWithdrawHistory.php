<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWithdrawHistory extends Model
{
    protected $table ="user_withdraw_history";

    protected $fillable = ['user_id', 'transfer_id', 'source_id', 'recipient_id', 'amount', 'currency', 'fee', 'tax', 'date', ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

}
