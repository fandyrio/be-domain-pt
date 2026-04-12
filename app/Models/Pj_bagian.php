<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pj_bagian extends Model
{
    // use HasFactory;
    protected $table="pj_bagian";
    protected $fillable=['id', 'id_citizen', 'id_bagian', 'active'];
}
