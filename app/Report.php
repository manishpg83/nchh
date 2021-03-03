<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
	protected $table = "reports";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'title', 'name', 'image', 'date', 'type', 'status'];
    
    
    
    //Get the user record associated with the blog.
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    //get image file path
    public function getImageAttribute($value)
    {
        return asset('images/'. $value);
    }
    
    //get image 
    public function getImageNameAttribute($value)
    {
        return  $this->attributes['image'];
    }

    public function getReportDateAttribute($value)
    {
        return $this->created_at->format('j F, Y');
    }

}
