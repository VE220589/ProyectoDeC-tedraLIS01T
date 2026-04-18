<?php

namespace App\Controllers;

use App\Models\PermisosModel;
use CodeIgniter\RESTful\ResourceController;

class Permisos extends ResourceController
{
    protected $format = 'json';

 public function readByRoleAndModule()
{
    try {
        $roleId = $this->request->getPost('role_id');
        $module = $this->request->getPost('module'); // ej: "users", "tickets"

        if (!$roleId || !$module) {
            return $this->respond([
                'status' => false,
                'message' => 'Faltan parámetros: role_id o module'
            ], 400);
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
        $roleId = $this->request->getPost('role_id');
        $module = $this->request->getPost('modulo');

        if (!$roleId) {
            return $this->respond([
                'status' => false,
                'message' => 'Faltan parámetros: role_id'
            ], 400);
        }

        if (!$module) {
            return $this->respond([
                'status' => false,
                'message' => 'Faltan parámetros: modulo'
            ], 400);
        }
        // Acciones esperadas: create, update, delete, view
        $acciones = ['create', 'update', 'delete', 'view'];

        // Leer switches del frontend
        $statusValues = [];
        foreach ($acciones as $accion) {
            $statusValues[$accion] = $this->request->getPost($accion) == "1" ? 't' : 'f';
        }

        $db = \Config\Database::connect();

        // Recorrer cada permiso y actualizarlo
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
                $module . "." . $accion
            ]);
        }

        // Después de actualizar role_permissions

        // Si el rol que se modificó es el rol del usuario logueado
        if (session('role_id') == $roleId) {

            $db = \Config\Database::connect();

            $sql = "
                SELECT p.name
                FROM permissions p
                JOIN role_permissions rp ON rp.permission_id = p.id
                WHERE rp.role_id = ?
                AND rp.status = 't';
            ";

            $result = $db->query($sql, [$roleId])->getResultArray();
            $permisos = array_column($result, 'name');

            // Actualizar permisos en sesión
            session()->set('permissions', $permisos);
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




}