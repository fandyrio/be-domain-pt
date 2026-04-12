<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_file_indikator extends Model
{
    // use HasFactory;
    protected $table="master_file_indikator";
    protected $fillable=["id", "file_code", "file_name", "periode", "gd_id"];
}
