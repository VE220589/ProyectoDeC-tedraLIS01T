<?php

namespace App\Controllers;

use App\Models\NotasModel;
use App\Models\TicketsModel;
use CodeIgniter\RESTful\ResourceController;

class Tickets extends ResourceController
{
    protected $format = 'json';

    // =========================================
    // LISTAR TODOS LOS TICKETS (CON JOINS)
    // =========================================
    public function index()
    {
        try {
            // Bandeja global para administradores: muestra todos los tickets.
            $model = new TicketsModel();
            $data = $model->getTicketsConJoin();

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

    public function supporTickets()
    {
        // Bandeja del tecnico: filtra por el usuario asignado en la sesion.
        $idUsuario = session()->get('id_usuario');

        try {
            $model = new TicketsModel();
            $data = $model->getSupportConJoin($idUsuario);

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

    public function userTickets()
    {
        // Bandeja del usuario final: filtra por tickets creados por el usuario en sesion.
        $idUsuario = session()->get('id_usuario');

        try {
            $model = new TicketsModel();
            $data = $model->getUserticketConJoin($idUsuario);

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
    // LEER UN TICKET POR ID
    // =========================================
    public function readOne()
    {
        try {
            $errors = $this->runValidation($this->ticketIdRule(), $this->ticketIdMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $model = new TicketsModel();
            $data = $model->find((int) $this->request->getPost('id_ticket'));

            if (! $data) {
                return $this->respond(['status' => false, 'message' => 'Ticket no encontrado'], 404);
            }

            return $this->respond(['status' => true, 'dataset' => $data]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // CREAR TICKET
    // =========================================
    public function create()
    {
        try {
            $errors = $this->runValidation($this->ticketRules(), $this->ticketMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $idUsuario = session()->get('id_usuario');

            if (! $idUsuario) {
                return $this->respond(['status' => false, 'message' => 'Sesion no valida'], 401);
            }

            $serviceId = (int) $this->request->getPost('id_servicio');

            if (! $this->serviceExists($serviceId)) {
                return $this->validationResponse(['id_servicio' => 'Seleccione un servicio activo valido.']);
            }

            $assignedTo = $this->normalizeOptionalId('id_asignado');

            if ($assignedTo === false) {
                return $this->validationResponse(['id_asignado' => 'Seleccione un tecnico valido.']);
            }

            if ($assignedTo !== null && ! $this->supportUserExists($assignedTo)) {
                return $this->validationResponse(['id_asignado' => 'Seleccione un tecnico activo valido.']);
            }

            $model = new TicketsModel();
            $data = [
                'title' => trim((string) $this->request->getPost('title')),
                'description' => trim((string) $this->request->getPost('desc')),
                'ticket_type' => $this->request->getPost('id_tipo_ticket'),
                'priority' => $this->request->getPost('prioridad'),
                'service_id' => $serviceId,
                'created_by' => (int) $idUsuario,
                'assigned_to' => $assignedTo
            ];

            if (! $model->insert($data)) {
                return $this->respond([
                    'status' => false,
                    'errors' => $model->errors()
                ], 422);
            }

            $ticketId = $model->getInsertID();
            $ticketNumber = sprintf('TCK-%06d', $ticketId);
            $model->update($ticketId, ['ticket_number' => $ticketNumber]);

            return $this->respond([
                'status' => true,
                'message' => 'Ticket generado correctamente',
                'ticket_number' => $ticketNumber
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // ACTUALIZAR TICKET
    // =========================================
    public function update($id = null)
    {
        try {
            $rules = $this->ticketRules(true);
            $rules['estado'] = 'required|in_list[open,in_progress,mitigated,closed]';

            $errors = $this->runValidation($rules, $this->ticketMessages() + $this->ticketIdMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_ticket');
            $model = new TicketsModel();
            $ticketActual = $model->find($id);

            if (! $ticketActual) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Ticket no encontrado'
                ], 404);
            }

            $serviceId = (int) $this->request->getPost('id_servicio');

            if (! $this->serviceExists($serviceId)) {
                return $this->validationResponse(['id_servicio' => 'Seleccione un servicio activo valido.']);
            }

            $assignedToPost = $this->normalizeOptionalId('id_asignado');

            if ($assignedToPost === false) {
                return $this->validationResponse(['id_asignado' => 'Seleccione un tecnico valido.']);
            }

            if ($assignedToPost !== null && ! $this->supportUserExists($assignedToPost)) {
                return $this->validationResponse(['id_asignado' => 'Seleccione un tecnico activo valido.']);
            }

            $assignedTo = $ticketActual['assigned_to'] !== null && $assignedToPost === null
                ? (int) $ticketActual['assigned_to']
                : $assignedToPost;

            $data = [
                'title' => trim((string) $this->request->getPost('title')),
                'description' => trim((string) $this->request->getPost('desc')),
                'ticket_type' => $this->request->getPost('id_tipo_ticket'),
                'priority' => $this->request->getPost('prioridad'),
                'service_id' => $serviceId,
                'status' => $this->request->getPost('estado'),
                'assigned_to' => $assignedTo
            ];

            $model->update($id, $data);

            return $this->respond([
                'status' => true,
                'message' => 'Ticket actualizado correctamente'
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
            $errors = $this->runValidation($this->ticketIdRule(), $this->ticketIdMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $idUsuario = session()->get('id_usuario');

            if (! $idUsuario) {
                return $this->respond(['status' => false, 'message' => 'Sesion no valida'], 401);
            }

            $id = (int) $this->request->getPost('id_ticket');
            $ticketsModel = new TicketsModel();
            $ticket = $ticketsModel->find($id);

            if (! $ticket) {
                return $this->respond(['status' => false, 'message' => 'Ticket no encontrado'], 404);
            }

            if ($ticket['status'] === 'closed') {
                return $this->respond([
                    'status' => false,
                    'message' => 'El ticket ya se encuentra cerrado.'
                ], 409);
            }

            $activitiesModel = new NotasModel();
            $db = \Config\Database::connect();
            $db->transStart();

            // Cerrar el ticket y guardar quien realizo el cierre.
            $ticketsModel->update($id, [
                'status' => 'closed',
                'closed_by' => (int) $idUsuario
            ]);

            // Registrar la nota de cierre para conservar trazabilidad.
            $activitiesModel->insert([
                'ticket_id' => $id,
                'actor_id' => (int) $idUsuario,
                'action' => 'El ticket se ha cerrado exitosamente',
                'note_type' => 'approved'
            ]);

            $db->transComplete();

            if (! $db->transStatus()) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se pudo cerrar el ticket.'
                ], 500);
            }

            return $this->respond([
                'status' => true,
                'message' => 'El ticket ha sido cerrado y se registro la nota de cierre.'
            ]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    public function requestLogic($id = null)
    {
        try {
            $errors = $this->runValidation($this->ticketIdRule(), $this->ticketIdMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $idUsuario = session()->get('id_usuario');

            if (! $idUsuario) {
                return $this->respond(['status' => false, 'message' => 'Sesion no valida'], 401);
            }

            $id = (int) $this->request->getPost('id_ticket');
            $model = new TicketsModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Ticket no encontrado'], 404);
            }

            $model->update($id, [
                'status' => 'closed',
                'closed_by' => (int) $idUsuario
            ]);

            return $this->respond([
                'status' => true,
                'message' => 'El ticket ha sido cerrado.'
            ]);
        } catch (\Throwable $th) {
            return $this->respond(['status' => false, 'exception' => $th->getMessage()], 500);
        }
    }

    // =========================================
    // ELIMINAR TICKET
    // =========================================
    public function delete($id = null)
    {
        try {
            $errors = $this->runValidation($this->ticketIdRule(), $this->ticketIdMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $id = (int) $this->request->getPost('id_ticket');
            $model = new TicketsModel();

            if (! $model->find($id)) {
                return $this->respond(['status' => false, 'message' => 'Ticket no encontrado'], 404);
            }

            $model->delete($id);

            return $this->respond([
                'status' => true,
                'message' => 'Ticket eliminado correctamente'
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // BUSQUEDA DE TICKETS
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
            $model = new TicketsModel();

            $data = $model->select("
                    tickets.*,
                    s.description AS service_name,
                    COALESCE(creator.name || ' ' || creator.last_name, 'Desconocido') AS creado_por,
                    COALESCE(assigned.name || ' ' || assigned.last_name, 'Aun no asignado') AS asignado_a,
                    COALESCE(closed.name || ' ' || closed.last_name, 'Aun no cerrado') AS cerrado_por
                ")
                ->join('users AS creator', 'creator.id = tickets.created_by')
                ->join('users AS assigned', 'assigned.id = tickets.assigned_to', 'left')
                ->join('users AS closed', 'closed.id = tickets.closed_by', 'left')
                ->join('services AS s', 's.id = tickets.service_id', 'left')
                ->groupStart()
                    ->like('tickets.ticket_number', $search)
                    ->orLike('tickets.title', $search)
                    ->orLike('tickets.description', $search)
                    ->orLike('s.description', $search)
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

    public function getServices()
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->table('services')
                ->select('id AS id, description AS desc')
                ->where('is_active', true)
                ->get()
                ->getResult();

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

    public function getUsuarios()
    {
        try {
            $db = \Config\Database::connect();

            $query = $db->table('users')
                ->select("users.id AS id, users.name || ' ' || users.last_name AS nombre")
                ->join('roles', 'roles.id = users.role_id')
                ->where('roles.name', 'support')
                ->where('users.is_active', true)
                ->get()
                ->getResult();

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

    private function ticketRules(bool $withId = false): array
    {
        $rules = [
            'title' => 'required|min_length[5]|max_length[255]',
            'desc' => 'required|min_length[10]',
            'id_tipo_ticket' => 'required|in_list[incident,problem,change]',
            'prioridad' => 'required|in_list[C,B,A,S]',
            'id_servicio' => 'required|integer',
        ];

        if ($withId) {
            $rules['id_ticket'] = 'required|integer';
        }

        return $rules;
    }

    private function ticketMessages(): array
    {
        return [
            'title' => [
                'required' => 'Ingrese el titulo del ticket.',
                'min_length' => 'El titulo debe tener al menos 5 caracteres.',
                'max_length' => 'El titulo no puede superar 255 caracteres.'
            ],
            'desc' => [
                'required' => 'Ingrese la descripcion del ticket.',
                'min_length' => 'La descripcion debe explicar el problema con al menos 10 caracteres.'
            ],
            'id_tipo_ticket' => [
                'required' => 'Seleccione el tipo de ticket.',
                'in_list' => 'El tipo de ticket seleccionado no es valido.'
            ],
            'prioridad' => [
                'required' => 'Seleccione la prioridad.',
                'in_list' => 'La prioridad seleccionada no es valida.'
            ],
            'id_servicio' => [
                'required' => 'Seleccione el servicio relacionado.',
                'integer' => 'El servicio seleccionado no es valido.'
            ],
            'estado' => [
                'required' => 'Seleccione el estado del ticket.',
                'in_list' => 'El estado seleccionado no es valido.'
            ]
        ];
    }

    private function ticketIdRule(): array
    {
        return [
            'id_ticket' => 'required|integer'
        ];
    }

    private function ticketIdMessages(): array
    {
        return [
            'id_ticket' => [
                'required' => 'Falta el ID del ticket.',
                'integer' => 'El ID del ticket debe ser numerico.'
            ]
        ];
    }

    private function normalizeOptionalId(string $field)
    {
        $value = $this->request->getPost($field);

        if ($value === null || $value === '' || $value === 'null') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return false;
        }

        return (int) $value;
    }

    private function serviceExists(int $serviceId): bool
    {
        return \Config\Database::connect()
            ->table('services')
            ->where('id', $serviceId)
            ->where('is_active', true)
            ->countAllResults() > 0;
    }

    private function supportUserExists(int $userId): bool
    {
        return \Config\Database::connect()
            ->table('users')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $userId)
            ->where('users.is_active', true)
            ->where('roles.name', 'support')
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
