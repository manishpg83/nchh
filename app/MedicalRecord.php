<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $table = "medical_records";

    protected $fillable = ['title', 'record_for', 'record_date', 'type', 'added_by', 'status'];

    public function addedBy()
    {
        return $this->belongsTo('App\User', 'added_by');
    }

    public function files()
    {
        return $this->hasMany('App\MedicalRecordFile', 'record_id');
    }
}
