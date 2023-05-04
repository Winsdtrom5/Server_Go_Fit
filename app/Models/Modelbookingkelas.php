<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelbookingkelas extends Model
{
    protected $table = 'bookingkelas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_member','id_jadwal'
    ];
}