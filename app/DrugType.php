<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrugType extends Model
{
    use SoftDeletes;
    protected $table = "drug_types";
    protected $guarded = ['id'];
}
