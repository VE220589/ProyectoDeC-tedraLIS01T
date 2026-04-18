<?php

namespace App\Controllers;

use App\Models\NotasModel;
use App\Models\TicketsModel;
use CodeIgniter\RESTful\ResourceController;

class Notas extends ResourceController
{
    protected $format = 'json';

    // =========================================
    // LISTAR TODOS LAS NOTAS (CON JOINS)
    // =========================================
    public function index()
    {
        try {
            $idtick = $this->request->getPost('id_ticketnota');
            $model = new NotasModel();
            $data = $model->getNotasConJoin($idtick);

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
        $notasModel = new NotasModel();
        $ticketsModel = new TicketsModel();

        $idUsuario = session()->get('id_usuario');
        $idTicket = $this->request->getPost('id_ticketnota');

        // 1. Insertar la nota
        $data = [
            'ticket_id' => $idTicket,
            'actor_id'  => $idUsuario,
            'action'    => $this->request->getPost('descnote')
        ];

        $notasModel->insert($data);

        // 2. Obtener el ticket actual
        $ticket = $ticketsModel->find($idTicket);

        if ($ticket) {
            // 3. Verificar estado y actualizar si es necesario
            if ($ticket['status'] === 'open') {
                $ticketsModel->update($idTicket, [
                    'status' => 'in_progress'
                ]);
            }
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
        $activitiesModel = new NotasModel(); // <-- tu modelo de ticket_activities
        $ticketsModel    = new TicketsModel();

        $idUsuario = session()->get('id_usuario');
        $idTicket  = $this->request->getPost('id_ticketnota');

        // --------------------------------------------------------
        // 1. Crear nota indicando solicitud de cierre
        // --------------------------------------------------------
        $data = [
            'ticket_id' => $idTicket,
            'actor_id'  => $idUsuario,
            'action'    => 'El usuario ha solicitado el cierre del ticket',
            'note_type' => 'request'  // <-- Tipo de nota solicitado
        ];

        $activitiesModel->insert($data);

        // --------------------------------------------------------
        // 2. Cambiar estado del ticket a "mitigated"
        // --------------------------------------------------------
        $ticketsModel->update($idTicket, [
            'status' => 'mitigated'
        ]);

        return $this->respond([
            'status'  => true,
            'message' => 'Solicitud de cierre registrada y ticket mitigado correctamente'
        ]);

    } catch (\Throwable $th) {
        return $this->respond([
            'status'    => false,
            'exception' => $th->getMessage()
        ], 500);
    }
}


}
