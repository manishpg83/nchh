<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SharePrescription extends Model
{
    protected $table = "share_prescriptions";

    protected $fillable = ['prescription_id', 'pharmacy_id', 'appointment_id', 'patient_id', 'doctor_id'];

    /**
     * Get the user.
     */
    public function pharmacy()
    {
        return $this->belongsTo('App\User', 'pharmacy_id');
    }

    public function patient()
    {
        return $this->belongsTo('App\User', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo('App\User', 'doctor_id');
    }

    public function appointment()
    {
        return $this->belongsTo('App\Appointment', 'appointment_id');
    }

}
