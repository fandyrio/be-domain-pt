<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    // use HasFactory;
    protected $table="bagian";
    protected $fillbale=['id', 'bagian_code', 'cuti_id', 'bagian', 'alias', 'pj', 'active'];
}
