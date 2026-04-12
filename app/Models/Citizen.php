<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citizen extends Model
{
    // use HasFactory;
    protected $table="citizen";
    protected $fillabel=['id', 'citizen_id_cuti', 'nama', 'nip','nik', 'email', 'pangkat', 'pendidikan', 'tempat_lahir', 'tanggal_lahir', 'no_hp', 'jenis_kelamin', 'id_jabatan', 'id_bagian', 'satker', 'foto', 'status', 'synced'];
}
