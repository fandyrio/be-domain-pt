<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Web_config extends Model
{
    // use HasFactory;
    protected $table="web_config";
    protected $fillable=['id', 'config_initial', 'config_name', 'config_value', 'active'];
}
