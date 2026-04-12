<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indikator_lvl3_user extends Model
{
    // use HasFactory;
    protected $table="indikator_lvl3_user";
    protected $fillable=['id', 'id_indikator_lvl3', 'id_bagian', 'has_child', 'periode', 'tahun', 'is_folder_bagian'];
}
