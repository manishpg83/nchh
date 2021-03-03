<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = "modules";

	protected $fillable = ['name','keyword'];

	public function route(){
		return $this->hasMany('App\RouteManager','module_id','id');
	}
}
