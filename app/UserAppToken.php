<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAppToken extends Model
{
    protected $table ="user_app_tokens";
    
    protected $fillable = ['dialcode', 'phone', 'token'];
}