<?php

namespace Tests\Feature\Cliente;

use App\Exceptions\MotorBusquedaException;
use App\Models\EjecucionBusquedaMotor;
use App\Models\User;
use App\Services\Admin\ConfiguracionBusquedaVisualService;
use App\Services\Cliente\BusquedaServicioService;
use App\Services\Cliente\MotorBusquedaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BusquedaSemanticaControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_la_busqueda_requiere_autenticacion_y_permiso(): void
    {
        $this->postJson(route('cliente.buscar-servicios.semantica'), ['consulta' => 'pantalla rota'])
            ->assertUnauthorized();

        $this->actingAs(User::factory()->create())
            ->postJson(route('cliente.buscar-servicios.semantica'), ['consulta' => 'pantalla rota'])
            ->assertForbidden();
    }

    public function test_valida_la_consulta_antes_de_contactar_al_motor(): void
    {
        $usuario = $this->usuarioCliente();
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldNotReceive('buscar');
        $this->app->instance(MotorBusquedaService::class, $motor);

        $this->actingAs($usuario)
            ->postJson(route('cliente.buscar-servicios.semantica'), [
                'consulta' => '   ',
                'tipo_atencion' => 'remoto',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['consulta', 'tipo_atencion']);
    }

    public function test_inicia_la_busqueda_con_seguimiento_y_persiste_su_propietario(): void
    {
        $usuario = $this->usuarioCliente();
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('iniciarBusqueda')->once()->with('pantalla negra', [
            'disponible' => true,
            'tipo_atencion' => 'domicilio',
        ])->andReturn([
            'id' => 'busqueda-segura-9',
            'estado' => 'pendiente',
        ]);
        $this->app->instance(MotorBusquedaService::class, $motor);

        $respuesta = $this->actingAs($usuario)
            ->postJson(route('cliente.buscar-servicios.semantica'), [
                'consulta' => ' pantalla negra ',
                'disponible' => true,
                'tipo_atencion' => 'domicilio',
            ])
            ->assertAccepted()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('ejecuciones_busqueda_motor', [
            'id' => $respuesta->json('data.id'),
            'fastapi_identificador' => 'busqueda-segura-9',
            'usuario_id' => $usuario->id,
            'consulta' => 'pantalla negra',
        ]);
    }

    public function test_un_fallo_de_fastapi_no_deja_una_excepcion_sin_controlar(): void
    {
        $usuario = $this->usuarioCliente();
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('iniciarBusqueda')->once()->andThrow(
            new MotorBusquedaException('No pudimos conectar con el buscador.', 'conexion'),
        );
        $this->app->instance(MotorBusquedaService::class, $motor);

        $this->actingAs($usuario)
            ->postJson(route('cliente.buscar-servicios.semantica'), ['consulta' => 'impresora no imprime'])
            ->assertStatus(503)
            ->assertJson([
                'ok' => false,
                'mensaje' => 'No pudimos conectar con el buscador.',
            ]);
    }

    public function test_solo_el_propietario_puede_consultar_estado_y_resultados(): void
    {
        $propietario = $this->usuarioCliente();
        $intruso = $this->usuarioCliente();
        $ejecucion = EjecucionBusquedaMotor::create([
            'fastapi_identificador' => 'privada-1',
            'usuario_id' => $propietario->id,
            'consulta' => 'consulta privada',
            'estado' => 'pendiente',
            'categoria_estado' => 'pendiente',
        ]);
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldNotReceive('consultarBusqueda');
        $motor->shouldNotReceive('resultadosBusqueda');
        $this->app->instance(MotorBusquedaService::class, $motor);

        $this->actingAs($intruso)
            ->getJson(route('cliente.buscar-servicios.semantica.estado', $ejecucion))
            ->assertForbidden();
        $this->actingAs($intruso)
            ->getJson(route('cliente.buscar-servicios.semantica.resultados', $ejecucion))
            ->assertForbidden();
    }

    public function test_sigue_fases_reales_y_entrega_resultados_sin_puntuaciones(): void
    {
        $usuario = $this->usuarioCliente();
        $ejecucion = EjecucionBusquedaMotor::create([
            'fastapi_identificador' => 'completa-2',
            'usuario_id' => $usuario->id,
            'consulta' => 'pantalla negra',
            'estado' => 'ejecutando',
            'categoria_estado' => 'ejecucion',
        ]);
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('consultarBusqueda')->once()->with('completa-2')->andReturn([
            'estado' => 'completado',
            'fase' => 'completado',
            'detalle' => 'Proceso finalizado',
            'historial_fases' => [
                ['fase' => 'generando_embedding', 'fecha' => now()->toIso8601String(), 'detalle' => 'Embedding'],
                ['fase' => 'completado', 'fecha' => now()->toIso8601String(), 'detalle' => 'Listo'],
            ],
            'finalizado_en' => now()->toIso8601String(),
        ]);
        $motor->shouldReceive('resultadosBusqueda')->once()->with('completa-2')->andReturn([
            'consulta' => 'pantalla negra',
            'total' => 1,
            'resultados' => [[
                'document_id' => 'proveedor:9',
                'semantic_score' => 0.98,
                'final_score' => 0.91,
                'explicacion' => ['Coincide con laptops.'],
            ]],
        ]);
        $proveedores = Mockery::mock(BusquedaServicioService::class);
        $proveedores->shouldReceive('proveedoresSemanticos')->once()->andReturn([]);
        $proveedores->shouldReceive('tiposAtencion')->once()->andReturn([]);
        $this->app->instance(MotorBusquedaService::class, $motor);
        $this->app->instance(BusquedaServicioService::class, $proveedores);

        $this->actingAs($usuario)
            ->getJson(route('cliente.buscar-servicios.semantica.estado', $ejecucion))
            ->assertOk()
            ->assertJsonPath('data.fase', 'completado')
            ->assertJsonPath('data.terminal', true);

        $respuesta = $this->actingAs($usuario)
            ->getJson(route('cliente.buscar-servicios.semantica.resultados', $ejecucion))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('total_motor', 1);

        $html = (string) $respuesta->json('html');
        $this->assertStringNotContainsString('semantic_score', $html);
        $this->assertStringNotContainsString('final_score', $html);
    }

    public function test_la_vista_aplica_la_personalizacion_sin_exponer_la_clave_del_motor(): void
    {
        $usuario = $this->usuarioCliente();
        $servicio = app(ConfiguracionBusquedaVisualService::class);
        $datos = $servicio->valoresPredeterminados();
        $datos['color_primario'] = '#123456';
        $datos['animacion'] = 'barra';
        $servicio->actualizar($datos);
        config()->set('services.motor_busqueda.key', 'secreto-que-no-debe-aparecer');

        $this->actingAs($usuario)
            ->get(route('cliente.buscar-servicios.index'))
            ->assertOk()
            ->assertSee('--busqueda-primary:#123456', false)
            ->assertSee('data-estado-url=', false)
            ->assertDontSee('secreto-que-no-debe-aparecer');
    }

    private function usuarioCliente(): User
    {
        Permission::findOrCreate('buscar servicios', 'web');
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('buscar servicios');

        return $usuario;
    }
}
