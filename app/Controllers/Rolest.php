<?php

namespace App\Controllers;

use App\Models\RolesModel;
use CodeIgniter\RESTful\ResourceController;

class Rolest extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        try {
            $model = new RolesModel();
            $data = $model->findAll();

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


}


