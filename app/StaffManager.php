<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class StaffManager extends Model
{
    protected $table = "staff_manager";

    protected $fillable = ['user_id', 'added_by', 'status'];

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function addedBy()
    {
        return $this->belongsTo('App\User', 'added_by');
    }

    public function practice()
    {
        return $this->hasOne('App\PracticeManager','staff_id','id');
    }
}
