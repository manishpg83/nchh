<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiagnosticsService extends Model
{
    protected $table = "diagnostics_services";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['diagnostics_id','name','price','information'];

    //Get the user details.
    public function diagnostics()
    {
        return $this->belongsTo('App\User', 'diagnostics_id');
    }

}
