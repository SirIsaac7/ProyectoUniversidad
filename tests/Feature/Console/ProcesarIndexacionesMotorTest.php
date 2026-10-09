<?php

namespace Tests\Feature\Console;

use App\Exceptions\MotorBusquedaException;
use App\Models\EjecucionIndexacionMotor;
use App\Services\Admin\ConfiguracionIndexacionMotorService;
use App\Services\Admin\MotorIndexacionService;
use Mockery;
use Tests\TestCase;

class ProcesarIndexacionesMotorTest extends TestCase
{
    public function test_actualiza_el_seguimiento_sin_iniciar_si_no_corresponde(): void
    {
        $indexaciones = Mockery::mock(MotorIndexacionService::class);
        $indexaciones->shouldReceive('actualizarEnCurso')->once()->andReturn([
            'consultadas' => 1,
            'actualizadas' => 1,
            'fallidas' => 0,
        ]);
        $indexaciones->shouldNotReceive('iniciarAutomatica');
        $configuracion = Mockery::mock(ConfiguracionIndexacionMotorService::class);
        $configuracion->shouldReceive('reclamarEjecucionProgramada')->once()->andReturnFalse();
        $this->app->instance(MotorIndexacionService::class, $indexaciones);
        $this->app->instance(ConfiguracionIndexacionMotorService::class, $configuracion);

        $this->artisan('motor:indexaciones-procesar')
            ->expectsOutput('Seguimiento: 1 actualizadas, 0 fallidas.')
            ->assertSuccessful();
    }

    public function test_inicia_una_indexacion_automatica_cuando_corresponde(): void
    {
        $ejecucion = new EjecucionIndexacionMotor([
            'fastapi_identificador' => 'auto-programada-1',
            'tipo' => 'automatica',
        ]);
        $indexaciones = Mockery::mock(MotorIndexacionService::class);
        $indexaciones->shouldReceive('actualizarEnCurso')->once()->andReturn([
            'consultadas' => 0,
            'actualizadas' => 0,
            'fallidas' => 0,
        ]);
        $indexaciones->shouldReceive('iniciarAutomatica')->once()->andReturn($ejecucion);
        $configuracion = Mockery::mock(ConfiguracionIndexacionMotorService::class);
        $configuracion->shouldReceive('reclamarEjecucionProgramada')->once()->andReturnTrue();
        $this->app->instance(MotorIndexacionService::class, $indexaciones);
        $this->app->instance(ConfiguracionIndexacionMotorService::class, $configuracion);

        $this->artisan('motor:indexaciones-procesar')
            ->expectsOutput('Indexacion automatica iniciada: auto-programada-1')
            ->assertSuccessful();
    }

    public function test_un_fallo_controlado_del_motor_no_rompe_el_scheduler(): void
    {
        $indexaciones = Mockery::mock(MotorIndexacionService::class);
        $indexaciones->shouldReceive('actualizarEnCurso')->once()->andReturn([
            'consultadas' => 0,
            'actualizadas' => 0,
            'fallidas' => 0,
        ]);
        $indexaciones->shouldReceive('iniciarAutomatica')->once()->andThrow(
            new MotorBusquedaException('FastAPI desconectado.', 'conexion'),
        );
        $configuracion = Mockery::mock(ConfiguracionIndexacionMotorService::class);
        $configuracion->shouldReceive('reclamarEjecucionProgramada')->once()->andReturnTrue();
        $this->app->instance(MotorIndexacionService::class, $indexaciones);
        $this->app->instance(ConfiguracionIndexacionMotorService::class, $configuracion);

        $this->artisan('motor:indexaciones-procesar')
            ->expectsOutput('FastAPI desconectado.')
            ->assertSuccessful();
    }
}
