<?php

namespace App\Http\Controllers\MiPerfilProveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiPerfilProveedor\StoreMiPortafolioProveedorRequest;
use App\Http\Requests\MiPerfilProveedor\UpdateMiPortafolioProveedorRequest;
use App\Models\PortafolioProveedor;
use App\Services\MiPerfilProveedor\PortafolioService;

class PortafolioController extends Controller
{
    public function __construct(
        protected PortafolioService $portafolioService
    ) {
        $this->middleware('permission:gestionar portafolio proveedor');
    }

    public function index()
    {
        return view('mi-perfil-proveedor.portafolio.index', [
            'perfilProveedor' => $this->portafolioService->getPerfilActual(),
        ]);
    }

    public function store(StoreMiPortafolioProveedorRequest $request)
    {
        $this->portafolioService->crearActual($request->validated());

        return back()->with('success', 'Trabajo agregado al portafolio correctamente.');
    }

    public function update(UpdateMiPortafolioProveedorRequest $request, PortafolioProveedor $portafolioProveedor)
    {
        $this->portafolioService->actualizarActual($portafolioProveedor, $request->validated());

        return back()->with('success', 'Trabajo actualizado correctamente.');
    }

    public function destroy(PortafolioProveedor $portafolioProveedor)
    {
        $this->portafolioService->eliminarActual($portafolioProveedor);

        return back()->with('success', 'Trabajo retirado del portafolio correctamente.');
    }
}
