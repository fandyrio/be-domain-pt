<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Msg_sent extends Model
{
    // use HasFactory;
    protected $table="msg_sent";
    protected $fillable=['id', 'msg', 'status', 'nama_penerima', 'no_penerima', 'month', 'year'];
}
