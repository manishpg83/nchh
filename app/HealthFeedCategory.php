<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthFeedCategory extends Model
{
    protected $table = "health_categories";
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = ['parent_id', 'title','image', 'status'];
    
    public function healthfeed(){
        return $this->hasMany('App\HealthFeed','category_ids');
    }
    
    public function getImageAttribute()
    {
        if(file_exists(storage_path().'/app/health_category/'.$this->attributes['image'])){
            return url('storage/app/health_category').'/'.$this->attributes['image'];
        }else{
            return url('storage/app/health_category/default.png');
        }
    }
    
    public function getImageNameAttribute()
    {
        return $this->attributes['image'];
    }
}