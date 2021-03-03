<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class AppointmentFile extends Model
{
    protected $table = "appointment_files";
    
    protected $fillable = ['appointment_id', 'filename'];
    
    public function appointment()
    {
        return $this->belongsTo('App\Appointment', 'appointment_id');
    }
    
    public function getFilenameAttribute($value)
    {
        if ($this->attributes['filename']) {
            if (File::exists(storage_path('app/appointment_prescription_file/' . $this->attributes['filename']))) {
                return url('storage/app/appointment_prescription_file') . '/' . $this->attributes['filename'];
            } else {
                return url('public/images/') . '/default.png';
            }
        }
    }
    
    public function getFilenameValueAttribute($value)
    {
        return $this->attributes['filename'];
    }
    
}