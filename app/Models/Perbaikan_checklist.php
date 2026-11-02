<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perbaikan_checklist extends Model
{
    // use HasFactory;
    protected $table = "perbaikan_checklist";
    protected $fillable = ["id", "level", "periode_perbaikan_id", "indikator_lvl_id", "catatan"];
}
