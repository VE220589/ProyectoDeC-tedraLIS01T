<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\RESTful\ResourceController;

class Auth extends ResourceController
{
    // Todas las respuestas de este controlador se entregan como JSON.
    protected $format = 'json';

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
                'status' => true,
                'dataset' => $data,
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'message' => 'No se pudo consultar la informacion de autenticacion.',
                'exception' => ENVIRONMENT === 'development' ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function exists()
    {
        try {
            // El login usa esta verificacion para avisar si la base no tiene usuarios cargados.
            $model = new UsuarioModel();
            $count = $model->countAllResults();

            return $this->respond([
                'status' => $count > 0,
                'message' => $count > 0 ? 'Usuarios existen' : 'No hay usuarios registrados',
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'message' => 'No fue posible verificar el estado de usuarios.',
                'exception' => ENVIRONMENT === 'development' ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function login()
    {
        try {
            // Acepta credenciales enviadas como formulario tradicional o como JSON.
            $alias = $this->request->getPost('alias_usuario') ?? $this->request->getJSONVar('alias_usuario');
            $clave = $this->request->getPost('clave_usuario') ?? $this->request->getJSONVar('clave_usuario');
            $alias = trim((string) $alias);
            $clave = (string) $clave;

            if (! $alias || ! $clave) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Ingrese usuario y contrasena para continuar.',
                ], 400);
            }

            if (! preg_match('/^[A-Za-z0-9]{3,25}$/', $alias) || strlen($clave) > 72) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Revise el formato del usuario o la contrasena.',
                ], 422);
            }

            $model = new UsuarioModel();
            // Se une con roles para guardar el nombre del rol y su id dentro de la sesion.
            $user = $model->select('users.*, roles.name as tipo_usuario, roles.id as role_id')
                ->join('roles', 'roles.id = users.role_id')
                ->where('users.username', $alias)
                ->first();

            // La contrasena nunca se compara en texto plano; se valida contra el hash.
            if (! $user || ! password_verify($clave, $user['password_hash'])) {
                return $this->respond([
                    'status' => false,
                    'message' => 'El usuario no existe o la clave es incorrecta.',
                ], 401);
            }

            if (! $this->isActive($user['is_active'])) {
                return $this->respond([
                    'status' => false,
                    'message' => 'El usuario esta inactivo. Solicite apoyo al administrador.',
                ], 403);
            }

            $this->startUserSession($user, 'local');

            return $this->respond([
                'status' => true,
                'message' => 'Autenticacion exitosa',
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'message' => 'No fue posible iniciar sesion. Intente nuevamente.',
                'exception' => ENVIRONMENT === 'development' ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function google()
    {
        try {
            // Google Identity Services envia un ID token firmado en "credential" o "id_token".
            $credential = $this->request->getPost('credential')
                ?? $this->request->getPost('id_token')
                ?? $this->request->getJSONVar('credential')
                ?? $this->request->getJSONVar('id_token');
            $credential = trim((string) $credential);

            if (! $credential) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se recibio la credencial de Google.',
                ], 400);
            }

            if (strlen($credential) > 4096) {
                return $this->respond([
                    'status' => false,
                    'message' => 'La credencial de Google no tiene un formato valido.',
                ], 422);
            }

            $profile = $this->verifyGoogleCredential($credential);

            if (! $profile) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se pudo validar la cuenta de Google.',
                ], 401);
            }

            $model = new UsuarioModel();
            // Si el correo ya existe, se reutiliza la cuenta local asociada a ese email.
            $user = $model->select('users.*, roles.name as tipo_usuario, roles.id as role_id')
                ->join('roles', 'roles.id = users.role_id')
                ->where('users.email', $profile['email'])
                ->first();

            if (! $user) {
                // Si es un correo nuevo, se registra como usuario final.
                // La contrasena interna es aleatoria porque el acceso real sera por Google.
                $nameParts = explode(' ', trim($profile['name'] ?? 'Usuario Google'), 2);
                $model->insert([
                    'username' => $this->buildGoogleUsername($profile['email']),
                    'email' => $profile['email'],
                    'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'name' => $profile['given_name'] ?? ($nameParts[0] ?: 'Usuario'),
                    'last_name' => $profile['family_name'] ?? ($nameParts[1] ?? 'Google'),
                    'role_id' => $this->getDefaultRoleId(),
                    'is_active' => true,
                ]);

                $user = $model->select('users.*, roles.name as tipo_usuario, roles.id as role_id')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.id', $model->getInsertID())
                    ->first();
            }

            if (! $user || ! $this->isActive($user['is_active'])) {
                return $this->respond([
                    'status' => false,
                    'message' => 'La cuenta esta inactiva. Solicite apoyo al administrador.',
                ], 403);
            }

            $this->startUserSession($user, 'google');

            return $this->respond([
                'status' => true,
                'message' => 'Autenticacion con Google exitosa',
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'message' => 'No fue posible iniciar sesion con Google.',
                'exception' => ENVIRONMENT === 'development' ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function logOut()
    {
        session()->destroy();

        return $this->respond([
            'status' => true,
            'message' => 'Sesion cerrada correctamente',
        ]);
    }

    private function startUserSession(array $user, string $authProvider = 'local'): void
    {
        $db = \Config\Database::connect();
        // Carga los permisos activos del rol para que el filtro y la interfaz apliquen RBAC.
        $permisosDB = $db->query(
            "SELECT p.name
             FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?
             AND rp.status = true",
            [$user['role_id']]
        )->getResultArray();

        // Regenera el ID de sesion para reducir riesgo de fijacion de sesion.
        session()->regenerate(true);
        session()->set([
            'id_usuario' => $user['id'],
            'alias_usuario' => $user['username'],
            'tipo_usuario' => $user['tipo_usuario'],
            'role_id' => $user['role_id'],
            'permissions' => array_column($permisosDB, 'name'),
            'auth_provider' => $authProvider,
            'login' => true,
            'last_activity_at' => time(),
        ]);
    }

    private function verifyGoogleCredential(string $credential): ?array
    {
        $clientId = env('GOOGLE_CLIENT_ID');

        if (! $clientId) {
            throw new \RuntimeException('Configure GOOGLE_CLIENT_ID para habilitar Google Login.');
        }

        // Valida el token directamente con Google y confirma que pertenece a este Client ID.
        $client = \Config\Services::curlrequest([
            'timeout' => 5,
            'http_errors' => false,
        ]);
        $response = $client->get('https://oauth2.googleapis.com/tokeninfo', [
            'query' => ['id_token' => $credential],
        ]);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $payload = json_decode($response->getBody(), true);

        if (! is_array($payload)
            || ($payload['aud'] ?? '') !== $clientId
            || ($payload['email_verified'] ?? '') !== 'true'
            || empty($payload['email'])
        ) {
            return null;
        }

        return $payload;
    }

    private function getDefaultRoleId(): int
    {
        $db = \Config\Database::connect();
        // Las cuentas creadas desde Google entran con el rol mas limitado.
        $role = $db->table('roles')->select('id')->where('name', 'end_user')->get()->getRowArray();

        if (! $role) {
            throw new \RuntimeException('No existe el rol end_user para nuevas cuentas Google.');
        }

        return (int) $role['id'];
    }

    private function buildGoogleUsername(string $email): string
    {
        // Genera un alias unico a partir del correo para cumplir la restriccion UNIQUE.
        $base = strtolower(preg_replace('/[^a-z0-9]/i', '', strtok($email, '@') ?: 'google'));
        $base = substr($base, 0, 18) ?: 'google';
        $candidate = $base;
        $model = new UsuarioModel();
        $suffix = 1;

        while ($model->where('username', $candidate)->first()) {
            $candidate = substr($base, 0, 18) . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function isActive($value): bool
    {
        return $value === true || $value === 't' || $value === '1' || $value === 1;
    }
}
