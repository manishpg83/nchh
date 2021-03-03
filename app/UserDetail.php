<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Specialty;
use Illuminate\Support\Facades\File;
class UserDetail extends Model
{
    protected $table = "user_details";
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [
        'user_id', 'specialty_ids', 'doctor_ids', 'registration_year', 'registration_number', 'registration_number_verified', 'liecence_number', 'liecence_number_verified', 'degree', 'collage_or_Institute', 'year_of_completion', 'experience', 'identity_proof', 'medical_registration_proof', 'diagnostics_proof', 'establishment_name', 'establishment_address', 'establishment_latitude', 'establishment_longitude', 'gst_in', 'website', 'about', 'services', 'bed', 'timing', 'charge', 'mode_of_payment'
    ];
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function getServicesAttribute($value)
    {
        return stringToArray($value);
    }
    
    //Get specialty list strint to array
    public function getSpecialtyIdsAttribute($value)
    {
        return stringToArray($value);
    }
    
    //Get user's all specialty list name
    public function getSpecialtyNameAttribute($value)
    {
        if ($this->attributes['specialty_ids']) {
            $data = stringToArray($this->attributes['specialty_ids']);
            $specialty = Specialty::whereIn('id', $data)->pluck('title', 'id')->toArray();
            return empty($specialty) ? [] : arrayToString($specialty);
        } else {
            return '';
        }
    }
    
    //Get user's all specialty list name
    public function getSpecialtiesAttribute($value)
    {
        $ids = stringToArray($this->attributes['specialty_ids']);
        if ($ids) {
            return Specialty::select('id', 'title', 'image')->whereIn('id', $ids)->get();
        } else {
            return null;
        }
    }
    
    /* public function getDoctorIdAttribute($value)
    {
        return stringToArray($value);
    }
    
    public function getDoctorCountAttribute($value)
    {
        $data = stringToArray($this->attributes['doctor_ids']);
        return count($data);
    }
    
    public function getDoctorsAttribute($value)
    {
        $data = stringToArray($this->attributes['doctor_ids']);
        $user = User::whereIn('id', $data)->get();
        return $user;
    } */
    
    public function getServicesListAttribute($value)
    {
        $data = stringToArray($this->attributes['services']);
        $services = Service::select('id', 'name', 'image')->whereIn('id', $data)->get();
        return $services;
    }
    
    public function getServicesListNameAttribute($value)
    { 
        if ($this->attributes['services']) {
            $data = stringToArray($this->attributes['services']);
            $services = Service::whereIn('id', $data)->pluck('name', 'id')->toArray();
            return empty($services) ? [] : arrayToString($services);
        } else {
            return '';
        }

    }
    
    public function getserviceCountAttribute($value)
    {
        $data = stringToArray($this->attributes['services']);
        return count($data);
    }
    
    public function getIdentityProofAttribute($value)
    {
        if ($this->attributes['identity_proof']) {
            if (File::exists(storage_path('app/document/' . $this->attributes['identity_proof']))) {
                return url('storage/app/document') . '/' . $this->attributes['identity_proof'];
            } else {
                return url('public/images/') . '/document_default.png';
            }
        }
    }
    
    public function getIdentityProofNameAttribute($value)
    {
        return  $this->attributes['identity_proof'];
    }
    
    public function getMedicalRegistrationProofAttribute($value)
    {
        if ($this->attributes['medical_registration_proof']) {
            if (File::exists(storage_path('app/document/' . $this->attributes['medical_registration_proof']))) {
                return url('storage/app/document') . '/' . $this->attributes['medical_registration_proof'];
            } else {
                return url('public/images/') . '/document_default.png';
            }
        }
    }
    
    public function getMedicalRegistrationProofNameAttribute($value)
    {
        return  $this->attributes['medical_registration_proof'];
    }

    public function getDiagnosticsProofAttribute($value)
    {
        if ($this->attributes['diagnostics_proof']) {
            if (File::exists(storage_path('app/document/' . $this->attributes['diagnostics_proof']))) {
                return url('storage/app/document') . '/' . $this->attributes['diagnostics_proof'];
            } else {
                return url('public/images/') . '/document_default.png';
            }
        }
    }
    
    public function getDiagnosticsProofNameAttribute($value)
    {
        return  $this->attributes['diagnostics_proof'];
    }
}