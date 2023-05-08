<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpromoreguler extends Model
{
    protected $table = 'promo_reguler';
    protected $primaryKey = 'id_promo';
    protected $allowedFields = [
        'nama_promo','requirement','minimal','bonus','deskripsi'
    ];
}
