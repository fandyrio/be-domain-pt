<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ampuh_indikator extends Model
{
    // use HasFactory;
    protected $table="ampuh_indikator";
    protected $fillable=['id', 'gd_id', 'indikator_name', 'level_sub_indikator', 'last_upload_id', 'rule_id', 'detil_rule_id'];
}
