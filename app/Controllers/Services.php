<?php

namespace App\Controllers;

use App\Models\ServicesModel;
use CodeIgniter\RESTful\ResourceController;

class Services extends ResourceController
{
    protected $format = 'json';

    private const DESCRIPTION_RULE = 'required|min_length[3]|max_length[50]|regex_match[/^[\p{L}\p{N}\s.,#\-()\/]+$/u]';

    // =========================================
    // LISTAR TODOS LOS SERVICIOS (CON JOINS)
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
    // LEER UN SERVICIO POR ID
    // =========================================
    public function readOne()
    {
        try {
            $errors = $this->runValidation($this->idRule(), $this->idMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $model = new ServicesModel();
            $data = $model->find((int) $this->request->getPost('id_servicio'));

            if (! $data) {
                return $this->respond(['status' => false, 'message' => 'Servicio no encontrado'], 404);
            }

            return $this->respond(['status' => true, 'dataset' => $data]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // CREAR SERVICIO
    // =========================================
    public function create()
    {
        try {
            $errors = $this->runValidation($this->serviceRules(), $this->serviceMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $typeId = (int) $this->request->getPost('id_tipo');

            if (! $this->classificationExists($typeId)) {
                return $this->validationResponse(['id_tipo' => 'Seleccione una clasificacion valida.']);
            }

            $model = new ServicesModel();
            $model->insert($this->servicePayload());

            return $this->respond([
                'status' => true,
                'message' => 'Servicio creado correctamente'
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // ACTUALIZAR SERVICIO
    // =========================================
    public function update($id = null)
    {
        try {
            $rules = $this->serviceRules();
            $rules['id_servicio'] = 'required|integer';

            $messages = $this->serviceMessages() + $this->idMessages();
            $errors = $this->runValidation($rules, $messages);

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_servicio');
            $model = new ServicesModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Servicio no encontrado'], 404);
            }

            $typeId = (int) $this->request->getPost('id_tipo');

            if (! $this->classificationExists($typeId)) {
                return $this->validationResponse(['id_tipo' => 'Seleccione una clasificacion valida.']);
            }

            $model->update($id, $this->servicePayload());

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
            $errors = $this->runValidation($this->idRule(), $this->idMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_servicio');
            $model = new ServicesModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Servicio no encontrado'], 404);
            }

            $model->update($id, ['is_active' => false]);

            return $this->respond([
                'status' => true,
                'message' => 'Servicio ha sido dado de baja'
            ]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // ELIMINAR SERVICIO
    // =========================================
    public function delete($id = null)
    {
        try {
            $errors = $this->runValidation($this->idRule(), $this->idMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_servicio');
            $model = new ServicesModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Servicio no encontrado'], 404);
            }

            $model->delete($id);

            return $this->respond([
                'status' => true,
                'message' => 'Servicio eliminado correctamente'
            ]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // BUSCAR SERVICIOS
    // =========================================
    public function search()
    {
        try {
            $errors = $this->runValidation([
                'search' => 'required|min_length[2]|max_length[50]'
            ], [
                'search' => [
                    'required' => 'Debe ingresar un termino de busqueda.',
                    'min_length' => 'La busqueda debe tener al menos 2 caracteres.',
                    'max_length' => 'La busqueda no puede superar 50 caracteres.'
                ]
            ]);

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $search = trim((string) $this->request->getPost('search'));
            $model = new ServicesModel();

            $data = $model
                ->select('services.*, services_classification.name as tipo')
                ->join('services_classification', 'services_classification.id = services.idservice_classification')
                ->groupStart()
                    ->like('services.description', $search)
                    ->orLike('services_classification.name', $search)
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

    private function serviceRules(): array
    {
        return [
            'desc' => self::DESCRIPTION_RULE,
            'id_tipo' => 'required|integer'
        ];
    }

    private function serviceMessages(): array
    {
        return [
            'desc' => [
                'required' => 'Ingrese la descripcion del servicio.',
                'min_length' => 'La descripcion debe tener al menos 3 caracteres.',
                'max_length' => 'La descripcion no puede superar 50 caracteres.',
                'regex_match' => 'La descripcion contiene caracteres no permitidos.'
            ],
            'id_tipo' => [
                'required' => 'Seleccione la clasificacion del servicio.',
                'integer' => 'La clasificacion seleccionada no es valida.'
            ]
        ];
    }

    private function idRule(): array
    {
        return [
            'id_servicio' => 'required|integer'
        ];
    }

    private function idMessages(): array
    {
        return [
            'id_servicio' => [
                'required' => 'Falta el ID del servicio.',
                'integer' => 'El ID del servicio debe ser numerico.'
            ]
        ];
    }

    private function servicePayload(): array
    {
        return [
            'description' => trim((string) $this->request->getPost('desc')),
            'idservice_classification' => (int) $this->request->getPost('id_tipo')
        ];
    }

    private function classificationExists(int $typeId): bool
    {
        return \Config\Database::connect()
            ->table('services_classification')
            ->where('id', $typeId)
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
