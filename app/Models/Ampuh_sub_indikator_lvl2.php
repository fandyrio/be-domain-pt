<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ampuh_sub_indikator_lvl2 extends Model
{
    // use HasFactory;
    protected $table="ampuh_sub_indikator_lvl2";
    protected $fillable=['id', 'sub_indikaotr_lvl1_id', 'gd_id', 'sub_indikator_name', 'level_sub_indikator', 'last_upload_id', 'rule_id', 'detil_rule_id'];
}
