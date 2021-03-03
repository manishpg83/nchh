<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = "states";
    
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 
    protected $fillable = [
        'name', 'country_id',
    ];
    
	/**
     * Get the city for the state.
     */
    public function city()
    {
        return $this->hasMany('App\City', 'state_id');
    }
    
	/**
	 * Get the country that owns the state.
	 */
	public function country()
	{
		return $this->belongsTo('App\Country', 'country_id');
	}
}
