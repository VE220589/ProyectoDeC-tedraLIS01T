<?php

namespace App\Models;

use CodeIgniter\Model;

class TiposUsuariosModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name'
    ];
}
