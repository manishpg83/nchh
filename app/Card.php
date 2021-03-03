<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $table = "cards";
    
    protected $fillable = ['title', 'sub_title', 'image', 'btn_label', 'type', 'status'];
    
    public function getImageAttribute()
    {
        if(file_exists(storage_path().'/app/card/'.$this->attributes['image'])){
            return url('storage/app/card').'/'.$this->attributes['image'];
        }else{
            return url('storage/app/card/default.png');
        }
    }
    
    public function getImageNameAttribute()
    {
        return $this->attributes['image'];
    }
}