<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = "user_roles";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'keyword', 'status'];

    function getRoleBadgeAttribute()
    {
        switch ($this->attributes['keyword']) {
            case "admin":
                return '<span class="badge badge-pill badge-primary">' . $this->attributes['name'] . '</span>';
                break;
            case "doctor":
                return '<span class="badge badge-pill badge-success">' . $this->attributes['name'] . '</span>';
                break;
            case "clinic":
                return '<span class="badge badge-pill badge-info">' . $this->attributes['name'] . '</span>';
                break;
            case "hospital":
                return '<span class="badge badge-pill badge-info">' . $this->attributes['name'] . '</span>';
                break;
            case "pharmacy":
                return '<span class="badge badge-pill badge-info">' . $this->attributes['name'] . '</span>';
                break;
            case "manager":
                return '<span class="badge badge-pill badge-info">' . $this->attributes['name'] . '</span>';
                break;
            case "accountant":
                return '<span class="badge badge-pill badge-primary">' . $this->attributes['name'] . '</span>';
                break;
            default:
                return '<span class="badge badge-pill badge-warning">' . $this->attributes['name'] . '</span>';
                break;
        }
    }
}
