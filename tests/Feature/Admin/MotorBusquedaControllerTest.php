<?php

namespace Tests\Feature\Admin;

use App\Exceptions\MotorBusquedaException;
use App\Models\EjecucionIndexacionMotor;
use App\Models\User;
use App\Services\Admin\MotorAdministracionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MotorBusquedaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_un_usuario_sin_permiso_no_puede_consultar_el_estado(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->getJson(route('motor-busqueda.estado'))
            ->assertForbidden();
    }

    public function test_actualiza_el_estado_exclusivamente_desde_el_backend(): void
    {
        $usuario = $this->usuarioConPermiso('consultar estado motor busqueda');
        $administracion = Mockery::mock(MotorAdministracionService::class);
        $administracion->shouldReceive('estadoServicios')->once()->andReturn([
            'estado_general' => 'operativo',
            'servicios' => [
                'fastapi' => ['categoria' => 'activo'],
                'mysql' => ['categoria' => 'activo'],
                'qdrant' => ['categoria' => 'activo'],
            ],
        ]);
        $this->app->instance(MotorAdministracionService::class, $administracion);

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
        $administracion = Mockery::mock(MotorAdministracionService::class);
        $administracion->shouldReceive('iniciarIndexacion')
            ->once()
            ->withArgs(fn (User $responsable) => $responsable->is($usuario))
            ->andReturn([
                'ejecucion_id' => 41,
                'identificador' => 'idx-manual-41',
                'mensaje' => 'Aceptada',
            ]);
        $this->app->instance(MotorAdministracionService::class, $administracion);

        $this->actingAs($usuario)
            ->postJson(route('motor-busqueda.indexacion.store'))
            ->assertAccepted()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.identificador', 'idx-manual-41');
    }

    public function test_el_panel_convierte_un_fallo_del_motor_en_una_respuesta_controlada(): void
    {
        $usuario = $this->usuarioConPermiso('consultar estado motor busqueda');
        $administracion = Mockery::mock(MotorAdministracionService::class);
        $administracion->shouldReceive('estadoServicios')->once()->andThrow(
            new MotorBusquedaException('FastAPI no responde.', 'conexion'),
        );
        $this->app->instance(MotorAdministracionService::class, $administracion);

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
        $sinPermiso = User::factory()->create();
        $this->actingAs($sinPermiso)
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
            ->assertRedirect(route('motor-busqueda.index'));

        $this->assertDatabaseHas('configuraciones_indexacion_motor', [
            'id' => 1,
            'activo' => true,
            'frecuencia' => 'semanal',
            'dia_semana' => 5,
            'hora_ejecucion' => '03:15:00',
            'zona_horaria' => 'America/La_Paz',
        ]);
    }

    public function test_el_detalle_del_historial_esta_protegido_por_permiso(): void
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

        $this->actingAs($this->usuarioConPermiso('ver historial indexaciones motor'))
            ->get(route('motor-busqueda.historial.show', $ejecucion))
            ->assertOk()
            ->assertSee('Detalle de indexación #'.$ejecucion->id)
            ->assertSee('Sistema');
    }

    public function test_el_panel_muestra_programacion_e_historial_solo_al_usuario_autorizado(): void
    {
        $usuario = User::factory()->create();
        foreach ([
            'ver panel motor busqueda',
            'configurar programacion indexaciones motor',
            'ver historial indexaciones motor',
        ] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
            $usuario->givePermissionTo($permiso);
        }
        $administracion = Mockery::mock(MotorAdministracionService::class);
        $administracion->shouldReceive('identificadorActivo')->once()->andReturnNull();
        $this->app->instance(MotorAdministracionService::class, $administracion);

        $this->actingAs($usuario)
            ->get(route('motor-busqueda.index'))
            ->assertOk()
            ->assertSee('Programación de indexación')
            ->assertSee('Historial de indexaciones')
            ->assertSee('Próxima ejecución');
    }

    public function test_la_programacion_semanal_exige_un_dia_valido(): void
    {
        $usuario = $this->usuarioConPermiso('configurar programacion indexaciones motor');

        $this->actingAs($usuario)
            ->from(route('motor-busqueda.index'))
            ->put(route('motor-busqueda.programacion.update'), [
                'activo' => true,
                'frecuencia' => 'semanal',
                'hora_ejecucion' => '03:15',
            ])
            ->assertRedirect(route('motor-busqueda.index'))
            ->assertSessionHasErrors('dia_semana');
    }

    private function usuarioConPermiso(string $nombre): User
    {
        Permission::findOrCreate($nombre, 'web');
        $usuario = User::factory()->create();
        $usuario->givePermissionTo($nombre);

        return $usuario;
    }
}
