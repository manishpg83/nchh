<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Service extends Model
{
	protected $table = "services";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'keyword'];

    public function getImageAttribute()
    {
        if ($this->attributes['image']) {
            if (File::exists(storage_path('app/service/' . $this->attributes['image']))) {
                return url('storage/app/service') . '/' . $this->attributes['image'];
            } else {
                return url('public/images/') . '/default_service.png';
            }
        }
    }
    
    public function getImageNameAttribute()
    {
        return $this->attributes['image'];
    }
}
