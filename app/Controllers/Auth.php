<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\RESTful\ResourceController;

class Auth extends ResourceController
{
    // Vista del login
    public function loginView()
    {
        return view('dashboard/index');
    }
    
      public function index()
{
    try {
        $model = new UsuarioModel();
        $data = $model->findAll();

        return $this->respond([
            'status'  => true,
            'dataset' => $data
        ]);

    } catch (\Throwable $th) {
        // Captura cualquier error del modelo o la BD
        return $this->respond([
            'status'    => false,
            'exception' => $th->getMessage(),   // Mensaje del error
            'line'      => $th->getLine(),      // Línea donde ocurrió
            'file'      => $th->getFile()       // Archivo donde ocurrió
        ], 500);
    }
}

    // Verificar si existen usuarios
    public function exists()
    {
        try {
            $model = new UsuarioModel();
            $count = $model->countAllResults();

            return $this->respond([
                'status' => $count > 0,
                'message' => $count > 0 ? 'Usuarios existen' : null,
                'exception' => $count > 0 ? null : 'No hay usuarios registrados'
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

  public function login()
{
    try {
        $alias = $this->request->getPost('alias_usuario');
        $clave = $this->request->getPost('clave_usuario');

        if (!$alias || !$clave) {
            return $this->respond([
                'status' => false,
                'exception' => 'Faltan datos'
            ], 400);
        }

        $model = new UsuarioModel();

        // Obtener usuario con su rol
        $user = $model->select('users.*, roles.name as tipo_usuario, roles.id as role_id')
                      ->join('roles', 'roles.id = users.role_id')
                      ->where('users.username', $alias)
                      ->first();

        if (!$user) {
            return $this->respond([
                'status' => false,
                'exception' => 'El usuario no existe o la clave es incorrecta.'
            ], 404);
        }

        // Validar si está activo
        if ($user['is_active'] !== 't') {
            return $this->respond([
                'status' => false,
                'exception' => 'El usuario está inactivo.'
            ], 403);
        }

        // Validar contraseña
        if (!password_verify($clave, $user['password_hash'])) {
            return $this->respond([
                'status' => false,
                'exception' => 'Contraseña incorrecta'
            ], 401);
        }

        // ==========================================
        // CARGAR PERMISOS DEL ROL
        // ==========================================
        $db = \Config\Database::connect();

        $sql = "
            SELECT p.name
            FROM permissions p
            JOIN role_permissions rp ON rp.permission_id = p.id
            WHERE rp.role_id = ?
            AND rp.status = 't';
        ";

        $permisosDB = $db->query($sql, [$user['role_id']])->getResultArray();

        // Convertir a arreglo simple ["users.create", "users.view", ...]
        $permisos = array_column($permisosDB, 'name');

        // ==========================================
        // GUARDAR EN SESIÓN
        // ==========================================
        session()->set([
            'id_usuario'    => $user['id'],
            'alias_usuario' => $user['username'],
            'tipo_usuario'  => $user['tipo_usuario'],
            'role_id'       => $user['role_id'],
            'permissions'   => $permisos,
            'login'         => true
        ]);

        return $this->respond([
            'status' => true,
            'message' => 'Autenticación exitosa'
        ]);

    } catch (\Throwable $th) {
        return $this->respond([
            'status' => false,
            'exception' => $th->getMessage()
        ], 500);
    }
}


    public function logOut()
{
    // Destruye toda la sesión
    session()->destroy();

    return $this->respond([
        'status' => true,
        'message' => 'Sesión cerrada correctamente'
    ]);
}
 
}
