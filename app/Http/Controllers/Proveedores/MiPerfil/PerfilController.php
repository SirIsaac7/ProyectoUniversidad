<?php

namespace App\Http\Controllers\Proveedores\MiPerfil;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedores\MiPerfil\UpdateMiPerfilProveedorRequest;
use App\Services\Proveedores\MiPerfil\PerfilService;

class PerfilController extends Controller
{
    public function __construct(
        protected PerfilService $perfilService
    ) {
        $this->middleware('permission:visualizar perfil proveedor')->only('index');
        $this->middleware('permission:actualizar perfil proveedor')->only('update');
    }

    public function index()
    {
        return view('proveedores.mi-perfil.perfil.index', [
            'perfilProveedor' => $this->perfilService->getPerfilActual(),
        ]);
    }

    public function update(UpdateMiPerfilProveedorRequest $request)
    {
        $this->perfilService->updatePerfilActual($request->validated());

        return redirect()
            ->route('mi-perfil-proveedor.index')
            ->with('success', 'Tu perfil de proveedor fue actualizado correctamente.');
    }
}
