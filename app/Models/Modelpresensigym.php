<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpresensigym extends Model
{
    protected $table = 'presensi_gym';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_booking','status'
    ];
}
