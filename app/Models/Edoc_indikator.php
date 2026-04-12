<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edoc_indikator extends Model
{
    // use HasFactory;
    protected $table="edoc_indikator";
    protected $fillable=["id", 'id_master', 'edoc', 'periode', 'timeline', 'max_fill_at'];
}
