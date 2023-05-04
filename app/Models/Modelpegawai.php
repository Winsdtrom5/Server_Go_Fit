<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id_pegawai';
    protected $allowedFields = [
        'nama_pegawai','password','email','umur','jabatan','no_telp'
    ];
}
