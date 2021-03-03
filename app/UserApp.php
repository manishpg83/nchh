<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserApp extends Model
{
    protected $table ="user_apps";
    
    protected $fillable = ['user_id', 'device_unique_id', 'token', 'language', 'device_type', 'device_os', 'device_model', 'device_manufacturer', 'api_version', 'app_version', 'build_type', 'build_version', 'status'];
    
}