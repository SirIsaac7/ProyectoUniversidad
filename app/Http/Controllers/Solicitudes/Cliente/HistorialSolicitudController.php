<?php

namespace App\Http\Controllers\Solicitudes\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;

class HistorialSolicitudController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver mis solicitudes');
    }

    public function index()
    {
        return redirect()->route('cliente.solicitudes.index', ['tab' => 'historial']);
    }

    public function show(Solicitud $solicitud)
    {
        $this->authorize('view', $solicitud);

        return redirect()->route('cliente.solicitudes.index', ['tab' => 'historial']);
    }
}
