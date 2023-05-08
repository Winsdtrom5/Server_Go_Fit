<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpromopaket extends Model
{
    protected $table = 'promo_paket';
    protected $primaryKey = 'id_promo';
    protected $allowedFields = [
        'nama_promo','requirement','minimal','bonus','batas_berlaku','deskripsi'
    ];
}
