<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SharePrescription;

class Appointment extends Model
{
    protected $table = "appointments";

    protected $fillable = ['payment_id', 'patient_id', 'doctor_id', 'practice_id', 'diagnostics_id', 'services_ids', 'appointment_type', 'appointment_from', 'appointment_from_id', 'patient_name', 'patient_phone', 'patient_email', 'date', 'start_time', 'end_time', 'status', 'cancelled_by', 'is_sample_pickup'];

    public function payment()
    {
        return $this->hasOne('App\Payment', 'id', 'payment_id');
    }

    public function patient()
    {
        return $this->belongsTo('App\User', 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo('App\User', 'doctor_id');
    }

    public function diagnostics()
    {
        return $this->belongsTo('App\User', 'diagnostics_id');
    }

    public function practice()
    {
        return $this->belongsTo('App\PracticeManager', 'practice_id', 'id');
    }

    //get Details of cancelled user
    public function cancelled()
    {
        return $this->belongsTo('App\User', 'cancelled_by');
    }

    /* Relation with staff */
    public function appointment_from()
    {
        return $this->belongsTo('App\StaffManager', 'appointment_from_id');
    }

    public function prescriptions()
    {
        return $this->hasMany('App\AppointmentPrescription', 'appointment_id');
    }

    public function files()
    {
        return $this->hasMany('App\AppointmentFile', 'appointment_id');
    }

    public function getLocationAddress($value)
    {
        $address = '';
        if ($value->appointment_from == "Practice") {
            $address .= $value->practice->address . '<br>' . $value->practice->locality . ', ' . $value->practice->city;
            $address .= '<br>' . $value->practice->country . ', ' . $value->practice->pincode;
        }
        return $address;
    }

    public function getLocationDetail($value)
    {
        if ($value->appointment_from == "Practice") {
            return $value->practice;
        }
        return '';
    }

    function getStatus($value)
    {
        switch ($value) {
            case "pending":
                return '<span class="badge badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="Book Appointment">Pending</span>';
                break;
            case "create":
                return '<span class="badge badge-pill badge-info" data-toggle="tooltip" data-placement="top" title="Book Appointment">Create</span>';
                break;
            case "attempt":
                return '<span class="badge badge-pill badge-primary" data-toggle="tooltip" data-placement="top" title="Appointment is Open">Attempt</span>';
                break;
            case "completed":
                return '<span class="badge badge-pill badge-success" data-toggle="tooltip" data-placement="top" title="Appointment Completed">Completed</span>';
                break;
            case "cancelled":
                return '<span class="badge badge-pill badge-danger" data-toggle="tooltip" data-placement="top"  title="Appointment Has Been Cancelled">Cancelled</span>';
                break;
            default:
                return '<span class="badge badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="Book Appointment">Pending</span>';
                break;
        }
    }

    public function type($id)
    {
        $appointment = Appointment::find($id);
        if($appointment->appointment_type == 'ONLINE'){
            $type = 'Video Consultation';
        }else{
            if($appointment->practice->addedBy->role->keyword == 'hospital'){
                $type = 'Hospital Appointment';
            }else{
                $type = 'Clinic Appointment';
            }
        }
        return $type;
    }

    public function isPrescriptionShare($id){
        if(SharePrescription::where('appointment_id',$id)->exists()){
            return false;
        }else{
            return true;
        }
    }

     //Get user's all service list name
     public function getServicesNameAttribute($value)
     {
         if ($this->attributes['services_ids']) {
             $data = stringToArray($this->attributes['services_ids']);
             $services = DiagnosticsService::whereIn('id', $data)->pluck('name', 'id')->toArray();
             return empty($services) ? [] : arrayToString($services);
         } else {
             return '';
         }
     }
 
     //Get user's all service total price
     public function getServicesPriceAttribute($value)
     {
         if ($this->attributes['services_ids']) {
             $data = stringToArray($this->attributes['services_ids']);
             $services = DiagnosticsService::whereIn('id', $data)->sum('price');
             return empty($services) ? 0 : '<i class="fas fa-rupee-sign"></i>'.$services;
         } else {
             return '';
         }
     }
}
