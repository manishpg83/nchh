<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	protected $table = "messages";

    protected $fillable = ['from', 'to', 'message', 'is_read'];

    public function sender()
    {
        return $this->belongsTo('App\User', 'from');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'to');
    }
}