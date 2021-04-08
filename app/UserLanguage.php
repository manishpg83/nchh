<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class UserLanguage extends Model
{
    protected $table = "user_languages";
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'user_id', 'language_id'
    ];
    
    public function user()
    {
        return $this->belongsToMany('App\User', 'user_id');
    }

    public function language()
    {
        return $this->belongsToMany('App\Language', 'language_id');
    }
}