<?php

namespace App\Controllers;

class Ticket extends BaseController
{
    public function ticket()
    {
        return view('dashboard/ticket');
    }
}