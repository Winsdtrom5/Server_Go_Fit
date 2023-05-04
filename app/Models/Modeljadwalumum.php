<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeljadwalumum extends Model
{
    protected $table = 'jadwalumum';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id','hari','jam','id_kelas','id_instruktur'
    ];
}
