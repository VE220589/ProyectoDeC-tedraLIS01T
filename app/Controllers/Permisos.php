<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Permisos extends ResourceController
{
    protected $format = 'json';

    private const MODULES = 'users,roles,services,tickets';

    public function readByRoleAndModule()
    {
        try {
            $errors = $this->runValidation([
                'role_id' => 'required|integer',
                'module' => 'required|in_list[' . self::MODULES . ']'
            ], $this->permissionMessages('module'));

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $roleId = (int) $this->request->getPost('role_id');
            $module = $this->request->getPost('module');

            if (! $this->roleExists($roleId)) {
                return $this->validationResponse(['role_id' => 'Seleccione un rol valido.']);
            }

            $db = \Config\Database::connect();
            $sql = "
                SELECT
                    p.id AS permission_id,
                    p.name,
                    rp.status
                FROM permissions p
                JOIN role_permissions rp
                    ON rp.permission_id = p.id
                WHERE rp.role_id = ?
                AND p.name LIKE ?
                ORDER BY p.name;
            ";

            $data = $db->query($sql, [
                $roleId,
                $module . '.%'
            ])->getResultArray();

            return $this->respond([
                'status' => true,
                'dataset' => $data
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    public function updateByRoleAndModule()
    {
        try {
            $rules = [
                'role_id' => 'required|integer',
                'modulo' => 'required|in_list[' . self::MODULES . ']',
                'create' => 'required|in_list[0,1]',
                'update' => 'required|in_list[0,1]',
                'delete' => 'required|in_list[0,1]',
                'view' => 'required|in_list[0,1]',
            ];

            $errors = $this->runValidation($rules, $this->permissionMessages('modulo'));

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $roleId = (int) $this->request->getPost('role_id');
            $module = $this->request->getPost('modulo');

            if (! $this->roleExists($roleId)) {
                return $this->validationResponse(['role_id' => 'Seleccione un rol valido.']);
            }

            $acciones = ['create', 'update', 'delete', 'view'];
            $statusValues = [];

            foreach ($acciones as $accion) {
                $statusValues[$accion] = $this->request->getPost($accion) === '1' ? 't' : 'f';
            }

            $db = \Config\Database::connect();
            $db->transStart();

            foreach ($acciones as $accion) {
                $sql = "
                    UPDATE role_permissions rp
                    SET status = ?
                    FROM permissions p
                    WHERE p.id = rp.permission_id
                    AND rp.role_id = ?
                    AND p.name = ?;
                ";

                $db->query($sql, [
                    $statusValues[$accion],
                    $roleId,
                    $module . '.' . $accion
                ]);
            }

            $db->transComplete();

            if (! $db->transStatus()) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se pudieron actualizar los permisos.'
                ], 500);
            }

            if ((int) session('role_id') === $roleId) {
                $result = $db->query(
                    "
                    SELECT p.name
                    FROM permissions p
                    JOIN role_permissions rp ON rp.permission_id = p.id
                    WHERE rp.role_id = ?
                    AND rp.status = 't';
                    ",
                    [$roleId]
                )->getResultArray();

                session()->set('permissions', array_column($result, 'name'));
            }

            return $this->respond([
                'status' => true,
                'message' => 'Permisos actualizados correctamente'
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    private function permissionMessages(string $moduleField): array
    {
        return [
            'role_id' => [
                'required' => 'Falta el rol.',
                'integer' => 'El rol seleccionado no es valido.'
            ],
            $moduleField => [
                'required' => 'Falta el modulo.',
                'in_list' => 'El modulo seleccionado no es valido.'
            ],
            'create' => [
                'required' => 'Falta el permiso de creacion.',
                'in_list' => 'El permiso de creacion no es valido.'
            ],
            'update' => [
                'required' => 'Falta el permiso de actualizacion.',
                'in_list' => 'El permiso de actualizacion no es valido.'
            ],
            'delete' => [
                'required' => 'Falta el permiso de eliminacion.',
                'in_list' => 'El permiso de eliminacion no es valido.'
            ],
            'view' => [
                'required' => 'Falta el permiso de lectura.',
                'in_list' => 'El permiso de lectura no es valido.'
            ],
        ];
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
