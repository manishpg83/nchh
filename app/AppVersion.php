<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $table ="app_versions";
    
    protected $fillable = ['type', 'version', 'forceupdate', 'status'];
}