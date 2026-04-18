<?php 

namespace App\Models;

use CodeIgniter\Model;

class ServicesModel extends Model
{
    protected $table      = 'services';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true; 
    protected $returnType = 'array';

    protected $allowedFields = [
        'description',
        'idservice_classification',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
public function getServiciosConJoin()
{
    $builder = $this->select('services.*, services_classification.name as tipo')
                    ->join('services_classification', 'services_classification.id = services.idservice_classification')
                    ->where('services.is_active', true);

    return $builder->findAll();
}

}
