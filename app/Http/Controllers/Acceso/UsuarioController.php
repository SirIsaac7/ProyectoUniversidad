<?php

namespace App\Http\Controllers\Acceso;

use App\Http\Controllers\Controller;
use App\Http\Requests\Acceso\Usuarios\StoreUsuarioRequest;
use App\Http\Requests\Acceso\Usuarios\UpdateUsuarioRequest;
use App\Http\Requests\Acceso\Usuarios\UpdateUsuarioRolesRequest;
use App\Models\User;
use App\Services\Acceso\UsuarioService;

class UsuarioController extends Controller
{
    public function __construct(
        protected UsuarioService $usuarioService
    ) {
        $this->middleware('permission:ver usuarios')->only('index');
        $this->middleware('permission:crear usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar usuarios')->only(['edit', 'update']);
        $this->middleware('permission:eliminar usuarios')->only('destroy');
        $this->middleware('permission:asignar rol usuarios')->only(['editRoles', 'updateRoles']);
    }

    public function index()
    {
        return view('acceso.usuarios.index');
    }

    public function create()
    {
        return view('acceso.usuarios.create');
    }

    public function store(StoreUsuarioRequest $request)
    {
        $this->usuarioService->createUsuario($request->validated());

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('acceso.usuarios.edit', compact('usuario'));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        $this->usuarioService->updateUsuario($usuario, $request->validated());

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function editRoles(User $usuario)
    {
        $roles = $this->usuarioService->getAllRoles();

        return view('acceso.usuarios.roles', compact('usuario', 'roles'));
    }

    public function updateRoles(UpdateUsuarioRolesRequest $request, User $usuario)
    {
        $this->usuarioService->syncUserRole($usuario, $request->input('role'));

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Rol asignado correctamente.');
    }

    public function destroy(User $usuario)
    {
        $this->usuarioService->toggleEstado($usuario);

        $mensaje = $usuario->estado
            ? 'Usuario activado correctamente.'
            : 'Usuario inactivado correctamente.';

        return redirect()
            ->route('usuarios.index')
            ->with('success', $mensaje);
    }
}
