<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MotorBusquedaRoutesTest extends TestCase
{
    public function test_las_rutas_del_panel_exigen_autenticacion_y_permisos_diferenciados(): void
    {
        $esperados = [
            'motor-busqueda.index' => ['permission:ver panel motor busqueda'],
            'motor-busqueda.estado' => ['permission:consultar estado motor busqueda'],
            'motor-busqueda.indexacion.index' => ['permission:ver panel motor busqueda'],
            'motor-busqueda.indexacion.store' => ['permission:ejecutar indexacion motor busqueda'],
            'motor-busqueda.indexacion.show' => ['permission:consultar estado motor busqueda'],
            'motor-busqueda.programacion.edit' => ['permission:ver panel motor busqueda', 'permission:configurar programacion indexaciones motor'],
            'motor-busqueda.programacion.update' => ['permission:configurar programacion indexaciones motor'],
            'motor-busqueda.historial.index' => ['permission:ver panel motor busqueda', 'permission:ver historial indexaciones motor'],
            'motor-busqueda.historial.show' => ['permission:ver panel motor busqueda', 'permission:ver historial indexaciones motor'],
            'motor-busqueda.personalizacion.edit' => ['permission:ver panel motor busqueda', 'permission:ver personalizacion busqueda'],
            'motor-busqueda.personalizacion.update' => ['permission:modificar personalizacion busqueda'],
            'motor-busqueda.personalizacion.reset' => ['permission:restaurar personalizacion busqueda'],
        ];

        foreach ($esperados as $nombre => $permisos) {
            $middlewares = Route::getRoutes()->getByName($nombre)->gatherMiddleware();

            $this->assertContains('auth', $middlewares);
            $this->assertContains('verified', $middlewares);

            foreach ($permisos as $permiso) {
                $this->assertContains($permiso, $middlewares);
            }
        }
    }
}
