<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = "permissions";

	protected $fillable = ['role_id','route_id'];

	public function role(){
		return $this->belongsTo('App\UserRole','id','role_id');
	}

	public function route(){
		return $this->belongsTo('App\RouteManager','route_id','id');
	}
}
