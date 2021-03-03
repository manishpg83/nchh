<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimingManager extends Model
{
    protected $table = "timing_manager";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['doctor_id', 'is_for', 'schedule', 'status'];
}
