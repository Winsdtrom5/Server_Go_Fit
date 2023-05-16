<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelinstruktur extends Model
{
    protected $table = 'instruktur';
    protected $primaryKey = 'id_instruktur';
    protected $allowedFields = [
        'id_instruktur','nama','password','umur','no_telp','keterlambatan'
    ];
}
