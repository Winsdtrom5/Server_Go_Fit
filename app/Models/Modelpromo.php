<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpromo extends Model
{
    protected $table = 'promo';
    protected $primaryKey = 'id_promo';
    protected $allowedFields = [
        'nama_promo','jenis_promo','deskripsi'
    ];
}
