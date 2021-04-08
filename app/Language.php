<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use SoftDeletes;
    protected $table = "languages";
    protected $guarded = ['id'];

    public function userlanugage()
    {
        return $this->hasMany('App\UserLanguage', 'language_id');
    }

    
    /* public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }*/
}
