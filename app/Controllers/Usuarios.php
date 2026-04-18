<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\RESTful\ResourceController;

class Usuarios extends ResourceController
{
    protected $format = 'json';

    // =========================================
    // LISTAR TODOS LOS USUARIOS (CON JOINS)
    // =========================================
    public function index()
    {
        try {
            $idUsuario = session()->get('id_usuario');
            $model = new UsuarioModel();
            $data = $model->getUsuariosConJoin($idUsuario);

            return $this->respond([
                'status' => true,
                'dataset' => $data
            ]);

        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // LEER UN USUARIO POR ID
    // =========================================
    public function readOne()
    {
        try {
            $id = $this->request->getPost('id_usuario');

            if (!$id) {
                return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
            }

            $model = new UsuarioModel();
            $data = $model->find($id);

            if (!$data) {
                return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            return $this->respond(['status' => true, 'dataset' => $data]);

        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // CREAR USUARIO
    // =========================================
      // =========================================
    // CREAR USUARIO
    // =========================================
    public function create()
    {
        try {
            $validation = \Config\Services::validation();

        $rules = [
            'nombres_usuario'    => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]', //permite el uso tildes 
            'apellidos_usuario'  => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'correo_usuario'     => 'required|valid_email|max_length[100]',
            'alias_usuario'      => 'required|alpha_numeric|min_length[3]|max_length[20]',
            'clave_usuario'      => 'required|min_length[8]',
            'confirmar_clave'    => 'required|matches[clave_usuario]',
            'id_tipo'            => 'required|integer'
        ];

        if (!$validation->setRules($rules)->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false, 
                'errors' => $validation->getErrors()
            ]);
        }

            $model = new UsuarioModel();

            $data = [
                'name'   => $this->request->getPost('nombres_usuario'),
                'last_name' => $this->request->getPost('apellidos_usuario'),
                'email'    => $this->request->getPost('correo_usuario'),
                'username'     => $this->request->getPost('alias_usuario'),
                'password_hash'     => password_hash($this->request->getPost('clave_usuario'), PASSWORD_DEFAULT),
                'role_id'   => $this->request->getPost('id_tipo'),
            ];

            $model->insert($data);

            return $this->respond([
                'status' => true,
                'message' => 'Usuario creado correctamente'
            ]);

        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // ACTUALIZAR USUARIO
    // =========================================
    public function update($id = null)
    {
        try {
            $id = $this->request->getPost('id_usuario');

            if (!$id) {
                return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
            }

            $validation = \Config\Services::validation();

            $rules = [
                'nombres_usuario'    => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
                'apellidos_usuario'  => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
                'correo_usuario'     => 'required|valid_email|max_length[100]',
                'id_tipo'            => 'required|integer'
            ];

            if (!$validation->setRules($rules)->withRequest($this->request)->run()) {
                return $this->respond([
                    'status' => false, 
                    'errors' => $validation->getErrors()
                ]);
            }

            $model = new UsuarioModel();

            $data = [
                'name'   => $this->request->getPost('nombres_usuario'),
                'last_name' => $this->request->getPost('apellidos_usuario'),
                'email'    => $this->request->getPost('correo_usuario'),
                'username'     => $this->request->getPost('alias_usuario'),
                'role_id'           => $this->request->getPost('id_tipo')

            ];

            // Si viene la clave, actualizarla
            if ($this->request->getPost('clave_usuario')) {
                $data['password_hash'] = password_hash(
                    $this->request->getPost('clave_usuario'),
                    PASSWORD_DEFAULT
                );
            }

            $model->update($id, $data);

            return $this->respond([
                'status' => true,
                'message' => 'Usuario actualizado correctamente'
            ]);

        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }


    public function updatePerfil()
{
    try {

        $id = $this->request->getPost('id_usuario');

        if (!$id) {
            return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
        }

        $model = new UsuarioModel();
        $usuarioActual = $model->find($id);

        if (!$usuarioActual) {
            return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        // ============================================================
        // VALIDAR CONTRASEÑA ACTUAL
        // ============================================================
        $claveActual = $this->request->getPost('clave_actual');

        if (!$claveActual || !password_verify($claveActual, $usuarioActual['password_hash'])) {
            return $this->respond([
                'status' => false,
                'message' => 'La contraseña actual es incorrecta'
            ], 401);
        }

        // ============================================================
        // VALIDACIÓN DE CAMPOS
        // ============================================================
        $validation = \Config\Services::validation();

        $rules = [
            'nombres_usuario'    => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'apellidos_usuario'  => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'correo_usuario'     => 'required|valid_email|max_length[100]',
            'alias_usuario'      => 'required|min_length[3]|max_length[50]',
        ];

        if (!$validation->setRules($rules)->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'errors' => $validation->getErrors()
            ]);
        }

        // ============================================================
        // PREPARAR LOS DATOS A ACTUALIZAR
        // ============================================================
        $data = [
            'name'       => $this->request->getPost('nombres_usuario'),
            'last_name'  => $this->request->getPost('apellidos_usuario'),
            'email'      => $this->request->getPost('correo_usuario'),
            'username'   => $this->request->getPost('alias_usuario'),
        ];

        // ============================================================
        // SI VIENE CONTRASEÑA NUEVA → ACTUALIZARLA
        // ============================================================
        $claveNueva = $this->request->getPost('clave_usuario');
        $confirmarClave = $this->request->getPost('confirmar_clave');

        if ($claveNueva) {

            // Validar confirmación
            if ($claveNueva !== $confirmarClave) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Las claves no coinciden'
                ], 400);
            }

            // Guardar nueva contraseña
            $data['password_hash'] = password_hash($claveNueva, PASSWORD_DEFAULT);
        }

        // ============================================================
        // GUARDAR EN BD
        // ============================================================
        $model->update($id, $data);

        if (session('id_usuario') == $id) {
            // IMPORTANTE: Solo actualizar si vienen datos nuevos
            if (isset($data['username'])) {
                session()->set('alias_usuario', $data['username']);
            }
        }

        return $this->respond([
            'status' => true,
            'message' => 'Perfil actualizado correctamente'
        ]);


    } catch (\Throwable $th) {
        return $this->respond([
            'status' => false,
            'exception' => $th->getMessage()
        ], 500);
    }
}


       public function deletelogic($id = null)
    {
        try {
            $id = $this->request->getPost('id_usuario');

            if (!$id) {
                return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
            }

            $model = new UsuarioModel();

            $data = [
                'is_active'           => false

            ];

            $model->update($id, $data);

            return $this->respond([
                'status' => true,
                'message' => 'Usuario ha sido dado de baja'
            ]);

        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // ELIMINAR USUARIO
    // =========================================
    public function delete($id = null)
    {
        try {
            $id = $this->request->getPost('id_usuario');

            if (!$id) {
                return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
            }

            $model = new UsuarioModel();
            $model->delete($id);

            return $this->respond([
                'status' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);

        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // BÚSQUEDA DE USUARIOS
    // =========================================
    public function search() 
{
    try {
        //$search = $this->request->getPost('search') ?? $this->request->getGet('search');

        $search = $this->request->getPost('search');

        if (!$search) {
            return $this->respond([
                'status' => false,
                'message' => 'Debe ingresar un término de búsqueda'
            ]);
        }

        $model = new UsuarioModel();

        // Hacemos JOIN con tipos y estados para obtener los nombres
        $data = $model
            ->select('users.*, roles.name as tipo')
            ->join('roles', 'roles.id = users.role_id')
            ->groupStart()
                ->like('users.name', $search)
                ->orLike('users.last_name', $search)
                ->orLike('users.username', $search)
            ->groupEnd()
            ->findAll();

        return $this->respond([
            'status' => true,
            'dataset' => $data
        ]);

    } catch (\Throwable $th) {
        return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
    }
}


    public function getTipo()
{
    try {
        $db = \Config\Database::connect();
        $query = $db->table('roles')->select('id AS id, name AS nombre')->get()->getResult();

        return $this->respond([
            'status' => true,
            'dataset' => $query
        ]);

    } catch (\Throwable $th) {
        return $this->respond([
            'status' => false,
            'exception' => $th->getMessage()
        ]);
    }
}

public function getEstado()
{
    try {
        $db = \Config\Database::connect();
        $query = $db->table('estados_usuarios')->select('id_estado AS id, nombre_estado AS nombre')->get()->getResult();

        return $this->respond([
            'status' => true,
            'dataset' => $query
        ]);

    } catch (\Throwable $th) {
        return $this->respond([
            'status' => false,
            'exception' => $th->getMessage()
        ]);
    }
}


}


