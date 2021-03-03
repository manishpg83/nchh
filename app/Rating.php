<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Rating extends Model
{
    protected $table = 'ratings';
    
    protected $fillable = ['user_id', 'rating', 'rateable_id', 'review', 'rateable_type'];
    
    public function rateable()
    {
        return $this->morphTo();
    }
    
    public function user()
    {
        $userClassName = Config::get('auth.model');
        if (is_null($userClassName)) {
            $userClassName = Config::get('auth.providers.users.model');
        }
        
        return $this->belongsTo($userClassName);
    }

    public function rateableUser(){
        return $this->belongsTo('App\user','rateable_id');
    }
}