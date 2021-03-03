<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = "chats";

    protected $fillable = ['sender_id', 'recipient_id'];

    public function sender()
    {
        return $this->belongsTo('App\User', 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo('App\User', 'recipient_id');
    }
}
