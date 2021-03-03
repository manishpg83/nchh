<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $table ="commission";
    
    protected $fillable =['user_id', 'neucrad_commission', 'patient_agent', 'other_agent'];
}
