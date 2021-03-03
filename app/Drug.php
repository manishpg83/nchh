<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $table = "drugs";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['added_by', 'name', 'type', 'strength', 'unit', 'instructions', 'status'];



    //Get the user record associated with the blog.
    public function user()
    {
        return $this->belongsTo('App\User', 'added_by');
    }

    public function getDrugNameAttribute($value)
    {
        return $this->attributes['type'] . ' ' . $this->attributes['name'] . ' (' . $this->attributes['strength'] . $this->attributes['unit'] . ')';
    }
}