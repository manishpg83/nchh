<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class MedicalRecordFile extends Model
{
    protected $table = "medical_record_files";

    protected $fillable = ['record_id', 'filename'];
    
    public $timestamps = false;
    
    public function record()
    {
        return $this->belongsTo('App\MedicalRecord', 'record_id');
    }

    public function getFilenameAttribute($value)
    {
        if ($this->attributes['filename']) {
            if (File::exists(storage_path('app/medical-record-files/' . $this->attributes['filename']))) {
                return url('storage/app/medical-record-files') . '/' . $this->attributes['filename'];
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
