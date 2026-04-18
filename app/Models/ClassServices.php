<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassServices extends Model
{
    protected $table = 'services_classification';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name'
    ];
}
