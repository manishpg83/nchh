<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RouteManager extends Model
{
    protected $table = "route_manager";

    protected $fillable = ['module_id', 'route_name', 'label'];

    public function module()
    {
        return $this->belongsTo('App\Module', 'id', 'module_id');
    }

    public function permission()
    {
        return $this->hasMany('App\Permission', 'route_id');
    }

    public static function getRoutePermission($data)
    {
        if (Permission::where(['role_id' => $data['role_id'], 'route_id' => $data['route_id'], 'status' => 1])->exists()) {
            return "checked";
        } else {
            return "";
        }
    }
}
