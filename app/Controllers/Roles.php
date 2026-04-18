<?php

namespace App\Controllers;

class Roles extends BaseController
{
    public function roles()
    {
        return view('dashboard/roles');
    }
}