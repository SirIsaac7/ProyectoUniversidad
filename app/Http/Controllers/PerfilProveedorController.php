<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePerfilProveedorRequest;
use App\Http\Requests\UpdatePerfilProveedorRequest;
use App\Models\PerfilProveedor;
use App\Models\User;
use App\Services\PerfilProveedorService;

class PerfilProveedorController extends Controller
{
    public function __construct(
        protected PerfilProveedorService $perfilProveedorService
    ) {
        $this->middleware('permission:ver proveedores')->only('index');
        $this->middleware('permission:crear proveedores')->only(['create', 'store']);
        $this->middleware('permission:editar proveedores')->only(['edit', 'update']);
        $this->middleware('permission:eliminar proveedores')->only('destroy');
    }

    public function index()
    {
        return view('perfiles-proveedores.index');
    }

    public function create()
    {
        $usuarios = User::where('estado', true)
            ->whereHas('roles', fn ($query) => $query->where('name', 'proveedor'))
            ->whereDoesntHave('perfilProveedor')
            ->orderBy('name')
            ->get();

        return view('perfiles-proveedores.create', compact('usuarios'));
    }

    public function store(StorePerfilProveedorRequest $request)
    {
        $this->perfilProveedorService->create($request->validated());

        return redirect()
            ->route('perfiles-proveedores.index')
            ->with('success', 'Perfil de proveedor creado correctamente.');
    }

    public function edit(PerfilProveedor $perfiles_proveedore)
    {
        $usuarios = User::where('estado', true)
            ->where(function ($query) use ($perfiles_proveedore) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'proveedor'))
                    ->orWhere('id', $perfiles_proveedore->user_id);
            })
            ->where(function ($query) use ($perfiles_proveedore) {
                $query->whereDoesntHave('perfilProveedor')
                    ->orWhere('id', $perfiles_proveedore->user_id);
            })
            ->orderBy('name')
            ->get();

        return view('perfiles-proveedores.edit', [
            'perfilProveedor' => $perfiles_proveedore->load('user'),
            'usuarios' => $usuarios,
        ]);
    }

    public function update(UpdatePerfilProveedorRequest $request, PerfilProveedor $perfiles_proveedore)
    {
        $this->perfilProveedorService->update($perfiles_proveedore, $request->validated());

        return redirect()
            ->route('perfiles-proveedores.index')
            ->with('success', 'Perfil de proveedor actualizado correctamente.');
    }

    public function destroy(PerfilProveedor $perfiles_proveedore)
    {
        $this->perfilProveedorService->toggleEstado($perfiles_proveedore);

        $mensaje = $perfiles_proveedore->estado
            ? 'Proveedor activado correctamente.'
            : 'Proveedor inactivado correctamente.';

        return redirect()
            ->route('perfiles-proveedores.index')
            ->with('success', $mensaje);
    }
}
