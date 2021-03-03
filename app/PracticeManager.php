<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class PracticeManager extends Model
{
    protected $table = "practice_manager";

    protected $fillable = ['doctor_id', 'name', 'email', 'phone', 'logo', 'address', 'locality', 'city', 'country', 'pincode', 'timing', 'fees', 'latitude', 'longitude', 'added_by', 'staff_id', 'status'];

    public function getLogoAttribute($value)
    {
        if ($this->attributes['logo']) {
            if (File::exists(storage_path('app/practice/' . $this->attributes['logo']))) {
                return url('storage/app/practice') . '/' . $this->attributes['logo'];
            } else {
                return url('public/images/') . '/practice_logo.png';
            }
        }
    }

    public function getLogoFilenameAttribute($value)
    {
        return $this->attributes['logo'];
    }

    public function getFullAddressAttribute()
    {
        $address =  $this->attributes['address'] . '<br>' . $this->attributes['locality'] . ', ' . $this->attributes['city'];
        $address .= '<br>' . $this->attributes['country'] . ', ' . $this->attributes['pincode'];
        return $address;
    }

    /**
     * Get the doctor.
     */
    public function doctor()
    {
        return $this->belongsTo('App\User', 'doctor_id');
    }

    public function addedBy()
    {
        return $this->belongsTo('App\User', 'added_by');
    }
}
