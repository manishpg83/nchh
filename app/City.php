<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
	protected $table = "cities";
	
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	
	protected $fillable = [
		'name', 'state_id',
	];
	
	/**
	 * Get the state that owns the city.
	 */
	public function state()
	{
		return $this->belongsTo('App\State', 'state_id');
	}
}
