<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelizininstruktur extends Model
{
    protected $table = 'izininstruktur';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_instruktur','tanggal_izin','alasan','status'
    ];
}
