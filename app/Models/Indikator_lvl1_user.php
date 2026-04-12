<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indikator_lvl1_user extends Model
{
    // use HasFactory;
    protected $table="indikator_lvl1_user";
    protected $fillable=['id', 'id_indikator_lvl1', 'id_bagian', 'has_child', 'periode', 'tahun', 'is_folder_bagian'];
}
