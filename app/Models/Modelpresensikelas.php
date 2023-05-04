<?php

namespace App\Models;

use CodeIgniter\Model;

class Modelpresensikelas extends Model
{
    protected $table = 'presensikelas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_booking','status'
    ];
}
