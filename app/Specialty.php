<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\User;

class Specialty extends Model
{
    protected $table = "specialties";
    
    protected $fillable = ['title', 'image','color_code'];
    
    public function getImageAttribute()
    {
        if ($this->attributes['image']) {
            if (File::exists(storage_path('app/specialties/' . $this->attributes['image']))) {
                return url('storage/app/specialties') . '/' . $this->attributes['image'];
            } else {
                return url('public/images/') . '/default_specialty.png';
            }
        }
    }
    
    public function getImageNameAttribute()
    {
        return $this->attributes['image'];
    }
    
    public function totalDoctor($id)
    {
        $doctor = User::whereHas('role', function ($q) {
            $q->where('keyword', 'doctor');
        })->whereHas('detail', function ($q) use ($id) {
            $q->whereRaw("find_in_set($id,specialty_ids)");
        })->count();
        return $doctor;
    }
}