<?php

namespace App\Http\Controllers\MiPerfilProveedor;

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
        return view('mi-perfil-proveedor.ubicacion.index', [
            'perfilProveedor' => $this->ubicacionService->getPerfilActual(),
        ]);
    }

    public function store(StoreMiUbicacionProveedorRequest $request)
    {
        $this->ubicacionService->guardarActual($request->validated());

        return back()->with('success', 'Ubicacion guardada correctamente.');
    }
}
