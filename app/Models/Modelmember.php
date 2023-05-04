<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelmember extends Model
{
    protected $table = 'member';
    protected $primaryKey = 'id_member';
    protected $allowedFields = [
        'id_member','nama_member','password','umur','email','no_telp','tanggal_lahir','date_daftar','deposit_uang','deposit_kelas','Expiration_Date','status'
    ];
}
