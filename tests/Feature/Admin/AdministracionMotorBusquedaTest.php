<?php

namespace Tests\Feature\Admin;

use App\Exceptions\MotorBusquedaException;
use App\Models\EjecucionIndexacionMotor;
use App\Models\User;
use App\Services\BusquedaInteligente\Administracion\EstadoMotorService;
use App\Services\BusquedaInteligente\Administracion\MotorIndexacionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdministracionMotorBusquedaTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_un_usuario_sin_permiso_no_puede_consultar_el_estado(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('motor-busqueda.estado'))
            ->assertForbidden();
    }

    public function test_el_menu_y_el_resumen_dependen_del_permiso_ver_panel(): void
    {
        $soloPermisoInterno = $this->usuarioConPermiso('consultar estado motor busqueda');

        $this->actingAs($soloPermisoInterno)
            ->get(route('motor-busqueda.index'))
            ->assertForbidden();

        $usuarioPanel = $this->usuarioConPermiso('ver panel motor busqueda');

        $this->actingAs($usuarioPanel)
            ->get(route('motor-busqueda.index'))
            ->assertOk()
            ->assertSee('Motor inteligente')
            ->assertSee('Resumen y servicios');
    }

    public function test_actualiza_el_estado_exclusivamente_desde_el_backend(): void
    {
        $usuario = $this->usuarioConPermiso('consultar estado motor busqueda');
        $estadoMotor = Mockery::mock(EstadoMotorService::class);
        $estadoMotor->shouldReceive('estadoServicios')->once()->andReturn([
            'estado_general' => 'operativo',
            'servicios' => [
                'fastapi' => ['categoria' => 'activo'],
                'mysql' => ['categoria' => 'activo'],
                'qdrant' => ['categoria' => 'activo'],
            ],
        ]);
        $this->app->instance(EstadoMotorService::class, $estadoMotor);

        $this->actingAs($usuario)
            ->getJson(route('motor-busqueda.estado'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.estado_general', 'operativo')
            ->assertJsonPath('data.servicios.qdrant.categoria', 'activo');
    }

    public function test_inicia_una_indexacion_manual_y_devuelve_202(): void
    {
        $usuario = $this->usuarioConPermiso('ejecutar indexacion motor busqueda');
        $ejecucion = new EjecucionIndexacionMotor([
            'fastapi_identificador' => 'idx-manual-41',
            'respuesta_inicio' => ['mensaje' => 'Aceptada'],
        ]);
        $ejecucion->id = 41;
        $indexaciones = Mockery::mock(MotorIndexacionService::class);
        $indexaciones->shouldReceive('iniciarManual')
            ->once()
            ->withArgs(fn (User $responsable) => $responsable->is($usuario))
            ->andReturn($ejecucion);
        $this->app->instance(MotorIndexacionService::class, $indexaciones);

        $this->actingAs($usuario)
            ->postJson(route('motor-busqueda.indexacion.store'))
            ->assertAccepted()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.identificador', 'idx-manual-41');
    }

    public function test_un_fallo_del_motor_se_convierte_en_una_respuesta_controlada(): void
    {
        $usuario = $this->usuarioConPermiso('consultar estado motor busqueda');
        $estadoMotor = Mockery::mock(EstadoMotorService::class);
        $estadoMotor->shouldReceive('estadoServicios')->once()->andThrow(
            new MotorBusquedaException('FastAPI no responde.', 'conexion'),
        );
        $this->app->instance(EstadoMotorService::class, $estadoMotor);

        $this->actingAs($usuario)
            ->getJson(route('motor-busqueda.estado'))
            ->assertStatus(503)
            ->assertJson([
                'ok' => false,
                'mensaje' => 'FastAPI no responde.',
                'tipo' => 'conexion',
            ]);
    }

    public function test_solo_un_usuario_autorizado_puede_actualizar_la_programacion(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('motor-busqueda.programacion.update'), [
                'activo' => true,
                'frecuencia' => 'diario',
                'hora_ejecucion' => '03:15',
            ])
            ->assertForbidden();

        $autorizado = $this->usuarioConPermiso('configurar programacion indexaciones motor');
        $this->actingAs($autorizado)
            ->put(route('motor-busqueda.programacion.update'), [
                'activo' => true,
                'frecuencia' => 'semanal',
                'dia_semana' => 5,
                'hora_ejecucion' => '03:15',
            ])
            ->assertRedirect(route('motor-busqueda.programacion.edit'));

        $this->assertDatabaseHas('configuraciones_indexacion_motor', [
            'activo' => true,
            'frecuencia' => 'semanal',
            'dia_semana' => 5,
            'hora_ejecucion' => '03:15:00',
            'zona_horaria' => 'America/La_Paz',
        ]);
    }

    public function test_el_detalle_del_historial_esta_protegido_por_permisos(): void
    {
        $ejecucion = EjecucionIndexacionMotor::create([
            'tipo' => 'automatica',
            'estado' => 'completado',
            'categoria_estado' => 'completado',
            'iniciado_en' => now()->subMinute(),
            'finalizado_en' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('motor-busqueda.historial.show', $ejecucion))
            ->assertForbidden();

        $this->actingAs($this->usuarioConPermisos([
            'ver panel motor busqueda',
            'ver historial indexaciones motor',
        ]))
            ->get(route('motor-busqueda.historial.show', $ejecucion))
            ->assertOk()
            ->assertSee('Sistema');
    }

    public function test_cada_opcion_administrativa_tiene_su_propia_pagina(): void
    {
        $usuario = $this->usuarioConPermisos([
            'ver panel motor busqueda',
            'configurar programacion indexaciones motor',
            'ver historial indexaciones motor',
        ]);

        $this->actingAs($usuario)
            ->get(route('motor-busqueda.index'))
            ->assertOk()
            ->assertSee('Resumen del motor inteligente');

        $this->actingAs($usuario)
            ->get(route('motor-busqueda.programacion.edit'))
            ->assertOk()
            ->assertSee('Programación automática')
            ->assertSee('Próxima ejecución');

        $this->actingAs($usuario)
            ->get(route('motor-busqueda.historial.index'))
            ->assertOk()
            ->assertSee('Historial de indexaciones');
    }

    public function test_la_programacion_semanal_exige_un_dia_valido(): void
    {
        $usuario = $this->usuarioConPermiso('configurar programacion indexaciones motor');

        $this->actingAs($usuario)
            ->from(route('motor-busqueda.programacion.edit'))
            ->put(route('motor-busqueda.programacion.update'), [
                'activo' => true,
                'frecuencia' => 'semanal',
                'hora_ejecucion' => '03:15',
            ])
            ->assertRedirect(route('motor-busqueda.programacion.edit'))
            ->assertSessionHasErrors('dia_semana');
    }

    private function usuarioConPermiso(string $nombre): User
    {
        return $this->usuarioConPermisos([$nombre]);
    }

    private function usuarioConPermisos(array $nombres): User
    {
        $usuario = User::factory()->create();

        foreach ($nombres as $nombre) {
            Permission::findOrCreate($nombre, 'web');
            $usuario->givePermissionTo($nombre);
        }

        return $usuario;
    }
}
