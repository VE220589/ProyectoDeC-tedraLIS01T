<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\RESTful\ResourceController;

class Usuarios extends ResourceController
{
    protected $format = 'json';

    private const NAME_RULE = 'required|regex_match[/^[\p{L}]+(?:\s+[\p{L}]+)*$/u]|min_length[2]|max_length[30]';
    private const ALIAS_RULE = 'required|alpha_numeric|min_length[3]|max_length[25]';
    private const PASSWORD_RULE = 'min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Za-z])(?=.*\d).+$/]';

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
            $errors = $this->runValidation([
                'id_usuario' => 'required|integer'
            ], [
                'id_usuario' => [
                    'required' => 'Falta el ID del usuario.',
                    'integer' => 'El ID del usuario debe ser numerico.'
                ]
            ]);

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $model = new UsuarioModel();
            $data = $model->find((int) $this->request->getPost('id_usuario'));

            if (! $data) {
                return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            unset($data['password_hash']);

            return $this->respond(['status' => true, 'dataset' => $data]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    public function readPerfil()
    {
        try {
            // El usuario final puede leer su propio perfil sin tener permiso users.view.
            $id = session()->get('id_usuario');

            if (! $id) {
                return $this->respond(['status' => false, 'message' => 'Sesion no valida'], 401);
            }

            $model = new UsuarioModel();
            $data = $model->find((int) $id);

            if (! $data) {
                return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            unset($data['password_hash']);

            return $this->respond(['status' => true, 'dataset' => $data]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // CREAR USUARIO
    // =========================================
    public function create()
    {
        try {
            $rules = $this->userRules();
            $rules['clave_usuario'] = 'required|' . self::PASSWORD_RULE;
            $rules['confirmar_clave'] = 'required|matches[clave_usuario]';

            $errors = $this->runValidation($rules, $this->userValidationMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $roleId = (int) $this->request->getPost('id_tipo');

            if (! $this->roleExists($roleId)) {
                return $this->validationResponse(['id_tipo' => 'Seleccione un rol valido.']);
            }

            if ($message = $this->duplicateUserMessage()) {
                return $this->respond([
                    'status' => false,
                    'message' => $message
                ], 409);
            }

            $model = new UsuarioModel();
            $model->insert($this->userPayload(true, true));

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
            $rules = $this->userRules(true);
            $rules['clave_usuario'] = 'permit_empty|' . self::PASSWORD_RULE;
            $rules['confirmar_clave'] = 'permit_empty|max_length[72]';

            $errors = $this->runValidation($rules, $this->userValidationMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_usuario');
            $model = new UsuarioModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            $roleId = (int) $this->request->getPost('id_tipo');

            if (! $this->roleExists($roleId)) {
                return $this->validationResponse(['id_tipo' => 'Seleccione un rol valido.']);
            }

            if ($message = $this->duplicateUserMessage($id)) {
                return $this->respond([
                    'status' => false,
                    'message' => $message
                ], 409);
            }

            $claveNueva = (string) $this->request->getPost('clave_usuario');
            $confirmarClave = (string) $this->request->getPost('confirmar_clave');

            if ($claveNueva !== '' && $claveNueva !== $confirmarClave) {
                return $this->validationResponse(['confirmar_clave' => 'La confirmacion de clave no coincide.']);
            }

            $data = $this->userPayload(true, false);

            if ($claveNueva !== '') {
                $data['password_hash'] = password_hash($claveNueva, PASSWORD_DEFAULT);
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
            $rules = $this->profileRules();
            $rules['clave_usuario'] = 'permit_empty|' . self::PASSWORD_RULE;
            $rules['confirmar_clave'] = 'permit_empty|max_length[72]';
            $rules['clave_actual'] = 'permit_empty|max_length[72]';

            $errors = $this->runValidation($rules, $this->userValidationMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_usuario');
            $model = new UsuarioModel();
            $usuarioActual = $model->find($id);

            if (! $usuarioActual) {
                return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            if ((int) session()->get('id_usuario') !== $id) {
                // Evita que un usuario modifique datos de otra cuenta cambiando el id en el formulario.
                return $this->respond([
                    'status' => false,
                    'message' => 'No puede modificar el perfil de otro usuario'
                ], 403);
            }

            if ($message = $this->duplicateUserMessage($id)) {
                return $this->respond([
                    'status' => false,
                    'message' => $message
                ], 409);
            }

            $claveActual = (string) $this->request->getPost('clave_actual');
            $claveNueva = (string) $this->request->getPost('clave_usuario');
            $confirmarClave = (string) $this->request->getPost('confirmar_clave');
            $isGoogleUser = session()->get('auth_provider') === 'google';

            // Solo se exige clave actual si el usuario local intenta cambiar su contrasena.
            // Las cuentas Google no conocen una clave local porque el acceso se valida con Google.
            if (! $isGoogleUser && $claveNueva !== '' && ($claveActual === '' || ! password_verify($claveActual, $usuarioActual['password_hash']))) {
                return $this->respond([
                    'status' => false,
                    'message' => 'La contrasena actual es incorrecta'
                ], 401);
            }

            if ($claveNueva !== '' && $claveNueva !== $confirmarClave) {
                return $this->validationResponse(['confirmar_clave' => 'La confirmacion de clave no coincide.']);
            }

            $data = $this->userPayload(false, false);

            if ($claveNueva !== '') {
                $data['password_hash'] = password_hash($claveNueva, PASSWORD_DEFAULT);
            }

            $model->update($id, $data);
            session()->set('alias_usuario', $data['username']);

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
            $errors = $this->runValidation([
                'id_usuario' => 'required|integer'
            ], [
                'id_usuario' => [
                    'required' => 'Falta el ID del usuario.',
                    'integer' => 'El ID del usuario debe ser numerico.'
                ]
            ]);

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_usuario');

            if ((int) session()->get('id_usuario') === $id) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No puede dar de baja su propia cuenta.'
                ], 409);
            }

            $model = new UsuarioModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            $model->update($id, ['is_active' => false]);

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
            $errors = $this->runValidation([
                'id_usuario' => 'required|integer'
            ], [
                'id_usuario' => [
                    'required' => 'Falta el ID del usuario.',
                    'integer' => 'El ID del usuario debe ser numerico.'
                ]
            ]);

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_usuario');

            if ((int) session()->get('id_usuario') === $id) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No puede eliminar su propia cuenta.'
                ], 409);
            }

            $model = new UsuarioModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Usuario no encontrado'], 404);
            }

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
    // BUSQUEDA DE USUARIOS
    // =========================================
    public function search()
    {
        try {
            $errors = $this->runValidation([
                'search' => 'required|min_length[2]|max_length[80]'
            ], [
                'search' => [
                    'required' => 'Debe ingresar un termino de busqueda.',
                    'min_length' => 'La busqueda debe tener al menos 2 caracteres.',
                    'max_length' => 'La busqueda no puede superar 80 caracteres.'
                ]
            ]);

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $search = trim((string) $this->request->getPost('search'));
            $model = new UsuarioModel();

            $data = $model
                ->select('users.*, roles.name as tipo')
                ->join('roles', 'roles.id = users.role_id')
                ->groupStart()
                    ->like('users.name', $search)
                    ->orLike('users.last_name', $search)
                    ->orLike('users.username', $search)
                    ->orLike('users.email', $search)
                ->groupEnd()
                ->findAll();

            foreach ($data as &$user) {
                unset($user['password_hash']);
            }

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

    private function userRules(bool $withId = false): array
    {
        $rules = [
            'nombres_usuario' => self::NAME_RULE,
            'apellidos_usuario' => self::NAME_RULE,
            'correo_usuario' => 'required|valid_email|max_length[100]',
            'alias_usuario' => self::ALIAS_RULE,
            'id_tipo' => 'required|integer',
        ];

        if ($withId) {
            $rules['id_usuario'] = 'required|integer';
        }

        return $rules;
    }

    private function profileRules(): array
    {
        return [
            'id_usuario' => 'required|integer',
            'nombres_usuario' => self::NAME_RULE,
            'apellidos_usuario' => self::NAME_RULE,
            'correo_usuario' => 'required|valid_email|max_length[100]',
            'alias_usuario' => self::ALIAS_RULE,
        ];
    }

    private function userPayload(bool $includeRole, bool $includePassword): array
    {
        $data = [
            'name' => trim((string) $this->request->getPost('nombres_usuario')),
            'last_name' => trim((string) $this->request->getPost('apellidos_usuario')),
            'email' => trim((string) $this->request->getPost('correo_usuario')),
            'username' => trim((string) $this->request->getPost('alias_usuario')),
        ];

        if ($includeRole) {
            $data['role_id'] = (int) $this->request->getPost('id_tipo');
        }

        if ($includePassword) {
            $data['password_hash'] = password_hash((string) $this->request->getPost('clave_usuario'), PASSWORD_DEFAULT);
        }

        return $data;
    }

    private function userValidationMessages(): array
    {
        return [
            'id_usuario' => [
                'required' => 'Falta el ID del usuario.',
                'integer' => 'El ID del usuario debe ser numerico.'
            ],
            'nombres_usuario' => [
                'required' => 'Ingrese los nombres.',
                'regex_match' => 'Los nombres solo pueden contener letras y espacios internos.',
                'min_length' => 'Los nombres deben tener al menos 2 caracteres.',
                'max_length' => 'Los nombres no pueden superar 30 caracteres.'
            ],
            'apellidos_usuario' => [
                'required' => 'Ingrese los apellidos.',
                'regex_match' => 'Los apellidos solo pueden contener letras y espacios internos.',
                'min_length' => 'Los apellidos deben tener al menos 2 caracteres.',
                'max_length' => 'Los apellidos no pueden superar 30 caracteres.'
            ],
            'correo_usuario' => [
                'required' => 'Ingrese el correo.',
                'valid_email' => 'Ingrese un correo valido.',
                'max_length' => 'El correo no puede superar 100 caracteres.'
            ],
            'alias_usuario' => [
                'required' => 'Ingrese el alias.',
                'alpha_numeric' => 'El alias solo puede contener letras y numeros.',
                'min_length' => 'El alias debe tener al menos 3 caracteres.',
                'max_length' => 'El alias no puede superar 25 caracteres.'
            ],
            'clave_usuario' => [
                'required' => 'Ingrese la clave.',
                'min_length' => 'La clave debe tener al menos 8 caracteres.',
                'max_length' => 'La clave no puede superar 72 caracteres.',
                'regex_match' => 'La clave debe incluir al menos una letra y un numero.'
            ],
            'confirmar_clave' => [
                'required' => 'Confirme la clave.',
                'matches' => 'La confirmacion de clave no coincide.',
                'max_length' => 'La confirmacion no puede superar 72 caracteres.'
            ],
            'id_tipo' => [
                'required' => 'Seleccione un rol.',
                'integer' => 'El rol seleccionado no es valido.'
            ],
        ];
    }

    private function duplicateUserMessage(?int $ignoreId = null): ?string
    {
        $email = trim((string) $this->request->getPost('correo_usuario'));
        $alias = trim((string) $this->request->getPost('alias_usuario'));

        if ($this->userValueExists('email', $email, $ignoreId)) {
            return 'El correo ya esta registrado por otro usuario.';
        }

        if ($this->userValueExists('username', $alias, $ignoreId)) {
            return 'El alias ya esta registrado por otro usuario.';
        }

        return null;
    }

    private function userValueExists(string $field, string $value, ?int $ignoreId = null): bool
    {
        if ($value === '') {
            return false;
        }

        $builder = \Config\Database::connect()->table('users')->where($field, $value);

        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    private function roleExists(int $roleId): bool
    {
        return \Config\Database::connect()
            ->table('roles')
            ->where('id', $roleId)
            ->countAllResults() > 0;
    }

    private function runValidation(array $rules, array $messages = []): ?array
    {
        $validation = \Config\Services::validation();

        if (! $validation->setRules($rules, $messages)->withRequest($this->request)->run()) {
            return $validation->getErrors();
        }

        return null;
    }

    private function validationResponse(array $errors)
    {
        return $this->respond([
            'status' => false,
            'errors' => $errors
        ], 422);
    }
}
