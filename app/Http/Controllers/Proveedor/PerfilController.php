<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiPerfilProveedor\UpdateMiPerfilProveedorRequest;
use App\Services\MiPerfilProveedor\PerfilService;

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
        return view('proveedor.perfil.index', [
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
