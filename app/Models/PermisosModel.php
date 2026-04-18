<?php

namespace App\Models;

use CodeIgniter\Model;

class PermisosModel extends Model
{
    protected $table = 'role_permissions';
    protected $allowedFields = [
        'role_id',
        'permission_id',
        'status'
    ];
    protected $useTimestamps = false;
}
