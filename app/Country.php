<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = "countries";
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    
    protected $fillable = [
        'code', 'name', 'phonecode',
    ];
    
    /**
    * Get the state for the country.
    */
    public function state()
    {
        return $this->hasMany('App\State', 'country_id');
    }
    
    public function user()
    {
        return $this->hasMany('App\User', 'country');
    }
}