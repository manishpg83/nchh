<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = "settings";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'consultant_as', 'consultant_duration', 'availability', 'unavailability_start_date', 'unavailability_end_date','do_service_at_other_establishment', 'is_sample_pickup', 'sample_pickup_charge'];
}
