<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;

class RoleService
{
    public function getAllRoles()
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    public function getAllPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        $role->update([
            'name' => $data['name'],
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? []);

        return $role->load('permissions');
    }

    public function deleteRole(Role $role): void
    {
        $role->delete();
    }

    protected function syncPermissions(Role $role, array $permissionIds): void
    {
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        $role->syncPermissions($permissions);
    }
}
