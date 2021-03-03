<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class UserGallery extends Model
{
    protected $table = "user_galleries";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'image'];

    public function getImageAttribute($value)
    {
        if ($this->attributes['image']) {
            if (File::exists(storage_path('app/user/gallery_photos/' . $this->attributes['image']))) {
                return url('storage/app/user/gallery_photos') . '/' . $this->attributes['image'];
            } else {
                return url('public/images/') . '/default_user.png';
            }
        }
    }

    public function getImageNameAttribute($value)
    {
        return $this->attributes['image'];
    }
}
