<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permisos = [
        'ver panel motor busqueda',
        'consultar estado motor busqueda',
        'ejecutar indexacion motor busqueda',
    ];

    public function up(): void
    {
        $ahora = now();

        foreach ($this->permisos as $permiso) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permiso,
                'guard_name' => 'web',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', $this->permisos)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
