<?php

namespace App\Http\Controllers;

use App\Services\Inicio\InicioService;

class InicioController extends Controller
{
    public function __construct(
        protected InicioService $inicioService
    ) {
    }

    public function index()
    {
        return view('inicio.index', $this->inicioService->getData());
    }
}
