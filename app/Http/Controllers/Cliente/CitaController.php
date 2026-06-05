<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Services\CitaService;

class CitaController extends Controller
{
    public function __construct(
        protected CitaService $citaService
    ) {
        $this->middleware('permission:ver mis citas')->only('index');
    }

    public function index()
    {
        return response()->json($this->citaService->citasCliente(auth()->user()));
    }
}
