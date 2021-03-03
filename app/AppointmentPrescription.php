<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppointmentPrescription extends Model
{
    protected $table = "appointment_prescriptions";
    
    protected $fillable = ['appointment_id', 'drug', 'frequency', 'intake', 'intake_instruction', 'duration','status'];
    
    public function appointment()
    {
        return $this->belongsTo('App\Appointment', 'appointment_id');
    }
}