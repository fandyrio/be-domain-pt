<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Api_token extends Model
{
    // use HasFactory;
    protected $table="api_token";
    protected $fillable=['id', 'token', 'env'];
}
