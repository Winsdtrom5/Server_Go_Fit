<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeljadwalharian extends Model
{
    protected $table = 'jadwalharian';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id','jadwal','tanggal_kelas','status','id_instruktur','sisa_peserta'
    ];
}
