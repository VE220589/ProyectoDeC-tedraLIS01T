<?php

namespace App\Models;

use CodeIgniter\Model;

class ListServiciosModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'description',
        'is_active'
    ];
}
