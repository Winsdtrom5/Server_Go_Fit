<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpresensiinstruktur extends Model
{
    protected $table = 'presensiinstruktur';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_instruktur','id_jadwal','status'
    ];
}
