<?php

namespace App\Controllers;

use App\Models\ServicesModel;
use CodeIgniter\RESTful\ResourceController;

class Services extends ResourceController
{
    protected $format = 'json';

    // =========================================
    // LISTAR TODOS LOS USUARIOS (CON JOINS)
    // =========================================
    public function index()
    {
        try {
            $model = new ServicesModel();
            $data = $model->getServiciosConJoin();

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
            $id = $this->request->getPost('id_servicio');

            if (!$id) {
                return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
            }

            $model = new ServicesModel();
            $data = $model->find($id);

            if (!$data) {
                return $this->respond(['status' => false, 'message' => 'Servicio no encontrado'], 404);
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
                'desc'    => 'required|min_length[3]|max_length[50]',
                'id_tipo'            => 'required|integer'
            ];

        if (!$validation->setRules($rules)->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false, 
                'errors' => $validation->getErrors()
            ]);
        }

            $model = new ServicesModel();

            $data = [
                'description'   => $this->request->getPost('desc'),
                'idservice_classification'=> $this->request->getPost('id_tipo')
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
            $id = $this->request->getPost('id_servicio');

            if (!$id) {
                return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
            }

            $validation = \Config\Services::validation();

            $rules = [
                'desc'    => 'required|min_length[3]|max_length[50]',
                'id_tipo'            => 'required|integer'
            ];

            if (!$validation->setRules($rules)->withRequest($this->request)->run()) {
                return $this->respond([
                    'status' => false, 
                    'errors' => $validation->getErrors()
                ]);
            }

            $model = new ServicesModel();

            $data = [
                'description'   => $this->request->getPost('desc'),
                'idservice_classification'=> $this->request->getPost('id_tipo')

            ];

            $model->update($id, $data);

            return $this->respond([
                'status' => true,
                'message' => 'Servicio actualizado correctamente'
            ]);

        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }


       public function deletelogic($id = null)
    {
        try {
            $id = $this->request->getPost('id_servicio');

            if (!$id) {
                return $this->respond(['status' => false, 'message' => 'Falta el ID'], 400);
            }

            $model = new ServicesModel();

            $data = [
                'is_active'           => false

            ];

            $model->update($id, $data);

            return $this->respond([
                'status' => true,
                'message' => 'Servicio ha sido dado de baja'
            ]);

        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // Buscamos los servicios
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

        $model = new ServicesModel();

        // Hacemos JOIN con tipos y estados para obtener los nombres
        $data = $model
            ->select('services.*, services_classification.name as tipo')
            ->join('services_classification', 'services_classification.id = services.idservice_classification')
            ->groupStart()
                ->like('services.description', $search)
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
        $query = $db->table('services_classification')->select('id AS id, name AS nombre')->get()->getResult();

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


