<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table ="wishlists";
    
    protected $fillable = ['user_id', 'doctor_id'];
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function doctor()
    {
        return $this->belongsTo('App\User', 'doctor_id');
    }
}