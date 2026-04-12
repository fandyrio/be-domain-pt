<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ampuh_sub_indikator_lvl3 extends Model
{
    // use HasFactory;
    protected $table="ampuh_sub_indikator_lvl3";
    protected $fillable=['id', 'parent_id', 'gd_id', 'sub_indikator_name', 'level_sub_indikator', 'last_upload_id', 'rule_id', 'detil_rule_id'];
}
