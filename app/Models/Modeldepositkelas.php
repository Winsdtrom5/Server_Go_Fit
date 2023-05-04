<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeldepositkelas extends Model
{
    protected $table = 'depositkelas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_member','tanggal','jumlah_deposit','id_promo','id_pegawai','batas_berlaku'
    ];
}