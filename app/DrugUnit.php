<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrugUnit extends Model
{
    use SoftDeletes;
    protected $table = "drug_units";
    protected $guarded = ['id'];
}
