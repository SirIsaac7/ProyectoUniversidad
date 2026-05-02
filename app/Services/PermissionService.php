<?php

namespace App\Services;

use App\Models\Permission;

class PermissionService
{
    public function getAllPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    public function createPermission(array $data): Permission
    {
        return Permission::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
    }

    public function updatePermission(Permission $permission, array $data): Permission
    {
        $permission->update([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? $permission->guard_name,
        ]);

        return $permission;
    }

    public function deletePermission(Permission $permission): void
    {
        $permission->delete();
    }
}
