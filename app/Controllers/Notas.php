<?php

namespace App\Controllers;

use App\Models\NotasModel;
use App\Models\TicketsModel;
use CodeIgniter\RESTful\ResourceController;

class Notas extends ResourceController
{
    protected $format = 'json';

    // =========================================
    // LISTAR NOTAS DE UN TICKET
    // =========================================
    public function index()
    {
        try {
            $errors = $this->runValidation($this->ticketIdRule(), $this->ticketIdMessages());

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $idTicket = (int) $this->request->getPost('id_ticketnota');

            if (! $this->ticketExists($idTicket)) {
                return $this->respond(['status' => false, 'message' => 'Ticket no encontrado'], 404);
            }

            $model = new NotasModel();
            $data = $model->getNotasConJoin($idTicket);

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

    public function create()
    {
        try {
            $rules = $this->ticketIdRule();
            $rules['descnote'] = 'required|min_length[3]|max_length[100]';

            $errors = $this->runValidation($rules, $this->ticketIdMessages() + [
                'descnote' => [
                    'required' => 'Ingrese la nota.',
                    'min_length' => 'La nota debe tener al menos 3 caracteres.',
                    'max_length' => 'La nota no puede superar 100 caracteres.'
                ]
            ]);

            if ($errors) {
                return $this->validationResponse($errors);
            }

            $idUsuario = session()->get('id_usuario');

            if (! $idUsuario) {
                return $this->respond(['status' => false, 'message' => 'Sesion no valida'], 401);
            }

            $idTicket = (int) $this->request->getPost('id_ticketnota');
            $ticketsModel = new TicketsModel();
            $ticket = $ticketsModel->find($idTicket);

            if (! $ticket) {
                return $this->respond(['status' => false, 'message' => 'Ticket no encontrado'], 404);
            }

            if ($ticket['status'] === 'closed') {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se pueden agregar notas a un ticket cerrado.'
                ], 409);
            }

            $notasModel = new NotasModel();
            $db = \Config\Database::connect();
            $db->transStart();

            $notasModel->insert([
                'ticket_id' => $idTicket,
                'actor_id' => (int) $idUsuario,
                'action' => trim((string) $this->request->getPost('descnote'))
            ]);

            if ($ticket['status'] === 'open') {
                $ticketsModel->update($idTicket, [
                    'status' => 'in_progress'
                ]);
            }

            $db->transComplete();

            if (! $db->transStatus()) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se pudo guardar la nota.'
                ], 500);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Nota creada correctamente'
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    public function createRequest()
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

            $idTicket = (int) $this->request->getPost('id_ticketnota');
            $activitiesModel = new NotasModel();
            $ticketsModel = new TicketsModel();
            $ticket = $ticketsModel->find($idTicket);

            if (! $ticket) {
                return $this->respond(['status' => false, 'message' => 'Ticket no encontrado'], 404);
            }

            if (in_array($ticket['status'], ['mitigated', 'closed'], true)) {
                return $this->respond([
                    'status' => false,
                    'message' => 'El ticket ya tiene una solicitud de cierre o esta cerrado.'
                ], 409);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $activitiesModel->insert([
                'ticket_id' => $idTicket,
                'actor_id' => (int) $idUsuario,
                'action' => 'El usuario ha solicitado el cierre del ticket',
                'note_type' => 'request'
            ]);

            $ticketsModel->update($idTicket, [
                'status' => 'mitigated'
            ]);

            $db->transComplete();

            if (! $db->transStatus()) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se pudo registrar la solicitud de cierre.'
                ], 500);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Solicitud de cierre registrada y ticket mitigado correctamente'
            ]);
        } catch (\Throwable $th) {
            return $this->respond([
                'status' => false,
                'exception' => $th->getMessage()
            ], 500);
        }
    }

    private function ticketIdRule(): array
    {
        return [
            'id_ticketnota' => 'required|integer'
        ];
    }

    private function ticketIdMessages(): array
    {
        return [
            'id_ticketnota' => [
                'required' => 'Falta el ID del ticket.',
                'integer' => 'El ID del ticket debe ser numerico.'
            ]
        ];
    }

    private function ticketExists(int $ticketId): bool
    {
        return \Config\Database::connect()
            ->table('tickets')
            ->where('id', $ticketId)
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
