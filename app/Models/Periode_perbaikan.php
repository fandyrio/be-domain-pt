<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periode_perbaikan extends Model
{
    // use HasFactory;
    protected $table = "periode_perbaikan";
    protected $fillable = ["id", "tahun", "periode"];
}
