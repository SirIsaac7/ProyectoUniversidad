<?php

namespace App\Http\Controllers\Acceso;

use App\Http\Controllers\Controller;

use App\Http\Requests\Acceso\Permisos\StorePermissionRequest;
use App\Http\Requests\Acceso\Permisos\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\Acceso\PermissionService;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {
        $this->middleware('permission:ver permisos')->only('index');
        $this->middleware('permission:crear permisos')->only(['create', 'store']);
        $this->middleware('permission:editar permisos')->only(['edit', 'update']);
        $this->middleware('permission:eliminar permisos')->only('destroy');
    }

    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();

        return view('acceso.permisos.index', compact('permissions'));
    }

    public function create()
    {
        return view('acceso.permisos.create');
    }

    public function store(StorePermissionRequest $request)
    {
        $this->permissionService->createPermission($request->validated());

        return redirect()
            ->route('permisos.index')
            ->with('success', 'Permiso creado correctamente.');
    }

    public function edit(Permission $permiso)
    {
        return view('acceso.permisos.edit', compact('permiso'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permiso)
    {
        $this->permissionService->updatePermission($permiso, $request->validated());

        return redirect()
            ->route('permisos.index')
            ->with('success', 'Permiso actualizado correctamente.');
    }

    public function destroy(Permission $permiso)
    {
        $this->permissionService->deletePermission($permiso);

        return redirect()
            ->route('permisos.index')
            ->with('success', 'Permiso eliminado correctamente.');
    }
}
