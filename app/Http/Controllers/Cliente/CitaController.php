<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;

class CitaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver mis citas')->only('index');
    }

    public function index()
    {
        return redirect()->route('cliente.solicitudes.index', ['tab' => 'citas']);
    }
}
