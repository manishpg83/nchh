<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = "feedbacks";
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['user_id', 'text', 'status'];
    
    
    
    //Get the user record associated with the blog.
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}