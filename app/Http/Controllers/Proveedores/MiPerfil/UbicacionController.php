<?php

namespace App\Http\Controllers\Proveedores\MiPerfil;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedores\MiPerfil\StoreMiUbicacionProveedorRequest;
use App\Services\Proveedores\MiPerfil\UbicacionService;

class UbicacionController extends Controller
{
    public function __construct(
        protected UbicacionService $ubicacionService
    ) {
        $this->middleware('permission:gestionar ubicacion proveedor');
    }

    public function index()
    {
        return view('proveedores.mi-perfil.ubicacion.index', $this->ubicacionService->obtenerDatosVista());
    }

    public function store(StoreMiUbicacionProveedorRequest $request)
    {
        $this->ubicacionService->guardarActual($request->validated());

        return back()->with('success', 'Ubicacion guardada correctamente.');
    }
}
