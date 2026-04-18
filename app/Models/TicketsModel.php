<?php 

namespace App\Models;

use CodeIgniter\Model;

class TicketsModel extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true; 
    protected $returnType = 'array';

    protected $allowedFields = [
        'ticket_number',
        'title',
        'description',
        'ticket_type',
        'status',
        'priority',
        'service_id',
        'created_by',
        'assigned_to',
        'closed_by',

    ];

   protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $closedField  = 'closed_at';
    protected $dueField  = 'sla_due_at';
    
public function getTicketsConJoin()
{
    return $this->select("
                tickets.*,
                s.description AS service_name,

                COALESCE(creator.name || ' ' || creator.last_name, 'Desconocido') AS creado_por,

                COALESCE(assigned.name || ' ' || assigned.last_name, 'Aún no asignado') AS asignado_a,

                COALESCE(closed.name || ' ' || closed.last_name, 'Aún no cerrado') AS cerrado_por
            ")
            ->join('users AS creator', 'creator.id = tickets.created_by') // siempre existe
            ->join('users AS assigned', 'assigned.id = tickets.assigned_to', 'left') 
            ->join('users AS closed', 'closed.id = tickets.closed_by', 'left')
            ->join('services AS s', 's.id = tickets.service_id', 'left')
            ->findAll();
}

public function getUserticketConJoin($idusuario = null)
{
    return $this->select("
                tickets.*,
                s.description AS service_name,

                COALESCE(creator.name || ' ' || creator.last_name, 'Desconocido') AS creado_por,

                COALESCE(assigned.name || ' ' || assigned.last_name, 'Aún no asignado') AS asignado_a,

                COALESCE(closed.name || ' ' || closed.last_name, 'Aún no cerrado') AS cerrado_por
            ")
            ->join('users AS creator', 'creator.id = tickets.created_by')
            ->join('users AS assigned', 'assigned.id = tickets.assigned_to', 'left')
            ->join('users AS closed', 'closed.id = tickets.closed_by', 'left')
            ->join('services AS s', 's.id = tickets.service_id', 'left')
            ->where('tickets.created_by', $idusuario) 
            ->findAll();
}

public function getSupportConJoin($idusuario = null)
{
    return $this->select("
                tickets.*,
                s.description AS service_name,

                COALESCE(creator.name || ' ' || creator.last_name, 'Desconocido') AS creado_por,

                COALESCE(assigned.name || ' ' || assigned.last_name, 'Aún no asignado') AS asignado_a,

                COALESCE(closed.name || ' ' || closed.last_name, 'Aún no cerrado') AS cerrado_por
            ")
            ->join('users AS creator', 'creator.id = tickets.created_by')
            ->join('users AS assigned', 'assigned.id = tickets.assigned_to', 'left')
            ->join('users AS closed', 'closed.id = tickets.closed_by', 'left')
            ->join('services AS s', 's.id = tickets.service_id', 'left')
            ->where('tickets.assigned_to', $idusuario) 
            ->findAll();
}



}

