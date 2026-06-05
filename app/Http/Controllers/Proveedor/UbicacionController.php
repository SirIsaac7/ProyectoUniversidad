<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiPerfilProveedor\StoreMiUbicacionProveedorRequest;
use App\Services\MiPerfilProveedor\UbicacionService;

class UbicacionController extends Controller
{
    public function __construct(
        protected UbicacionService $ubicacionService
    ) {
        $this->middleware('permission:gestionar ubicacion proveedor');
    }

    public function index()
    {
        return view('proveedor.ubicacion.index', $this->ubicacionService->obtenerDatosVista());
    }

    public function store(StoreMiUbicacionProveedorRequest $request)
    {
        $this->ubicacionService->guardarActual($request->validated());

        return back()->with('success', 'Ubicacion guardada correctamente.');
    }
}
