<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelaktivasi extends Model
{
    protected $table = 'aktivasi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_member','tanggal','harga','id_pegawai'
    ];
}