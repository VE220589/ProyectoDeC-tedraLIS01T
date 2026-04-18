<?php 

namespace App\Models;

use CodeIgniter\Model;

class NotasModel extends Model
{
    protected $table      = 'ticket_activities';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true; 
    protected $returnType = 'array';

    protected $allowedFields = [
        'ticket_id',
        'actor_id',
        'action',
        'note_type'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    
public function getNotasConJoin($ticketId = null)
{
    //$ticketId = 1;
    return $this->select("
            ticket_activities.*,
            tickets.ticket_number,
            CONCAT(users.name, ' ', users.last_name) AS actor_name
        ")
        ->join('tickets', 'tickets.id = ticket_activities.ticket_id')
        ->join('users', 'users.id = ticket_activities.actor_id')
        ->where('ticket_activities.ticket_id', $ticketId)
        ->orderBy('ticket_activities.created_at', 'DESC')
        ->findAll();
}

}
