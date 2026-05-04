<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Services\UsuarioService;

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
        return view('usuarios.index');
    }

    public function create()
    {
        return view('usuarios.create');
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
        return view('usuarios.edit', compact('usuario'));
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

        return view('usuarios.roles', compact('usuario', 'roles'));
    }

    public function updateRoles(Request $request, User $usuario)
    {
        $request->validate([
            'role' => ['nullable', 'string', 'exists:' . (new Role())->getTable() . ',name'],
        ]);

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
