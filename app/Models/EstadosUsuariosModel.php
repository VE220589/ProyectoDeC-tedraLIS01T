<?php

namespace App\Models;

use CodeIgniter\Model;

class EstadosUsuariosModel extends Model
{
    protected $table = 'estados_usuarios';
    protected $primaryKey = 'id_estado';

    protected $allowedFields = [
        'estado'
    ];
}
