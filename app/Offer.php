<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = "offers";
    
    protected $fillable = ['title', 'image', 'type', 'status'];
    
    public function getImageAttribute()
    {
        if(file_exists(storage_path().'/app/offer/'.$this->attributes['image'])){
            return url('storage/app/offer').'/'.$this->attributes['image'];
        }else{
            return url('storage/app/offer/default.png');
        }
    }
    
    public function getImageNameAttribute()
    {
        return $this->attributes['image'];
    }
    
}