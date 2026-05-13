<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMiPerfilProveedorRequest;
use App\Services\MiPerfilProveedorService;

class MiPerfilProveedorController extends Controller
{
    public function __construct(
        protected MiPerfilProveedorService $miPerfilProveedorService
    ) {
        $this->middleware('permission:ver mi perfil proveedor')->only('index');
        $this->middleware('permission:editar mi perfil proveedor')->only('update');
    }

    public function index()
    {
        $perfilProveedor = $this->miPerfilProveedorService->getPerfilActual();

        return view('mi-perfil-proveedor.index', compact('perfilProveedor'));
    }

    public function update(UpdateMiPerfilProveedorRequest $request)
    {
        $this->miPerfilProveedorService->updatePerfilActual($request->validated());

        return redirect()
            ->route('mi-perfil-proveedor.index')
            ->with('success', 'Tu perfil de proveedor fue actualizado correctamente.');
    }
}
