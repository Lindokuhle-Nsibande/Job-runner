<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Example extends Controller
{
    public function handle($param1, $param2)
    {
        sleep(10);
    }
}