<?php

namespace App\Models;

use CodeIgniter\Model;

class Modeldeposituang extends Model
{
    protected $table = 'deposituang';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_member','tanggal','jumlah_deposit','id_promo','id_pegawai'
    ];
}