<?php

namespace Tests\Unit\Services\Admin;

use App\Models\EjecucionIndexacionMotor;
use App\Models\User;
use App\Services\BusquedaInteligente\Administracion\MotorAdministracionService;
use App\Services\BusquedaInteligente\Administracion\MotorIndexacionService;
use App\Services\BusquedaInteligente\MotorBusquedaService;
use Mockery;
use Tests\TestCase;

class MotorAdministracionServiceTest extends TestCase
{
    public function test_normaliza_el_estado_individual_de_los_servicios(): void
    {
        $motor = Mockery::mock(MotorBusquedaService::class);
        $indexaciones = Mockery::mock(MotorIndexacionService::class);
        $motor->shouldReceive('estado')->once()->andReturn([
            'servicios' => [
                'fastapi' => ['estado' => 'activo'],
                'mysql' => ['estado' => 'desconectado', 'detalle' => 'Sin conexion'],
                'qdrant' => ['estado' => 'ok'],
            ],
        ]);

        $estado = (new MotorAdministracionService($motor, $indexaciones))->estadoServicios();

        $this->assertSame('degradado', $estado['estado_general']);
        $this->assertSame('activo', $estado['servicios']['fastapi']['categoria']);
        $this->assertSame('desconectado', $estado['servicios']['mysql']['categoria']);
        $this->assertSame('activo', $estado['servicios']['qdrant']['categoria']);
    }

    public function test_delega_la_indexacion_manual_y_devuelve_su_identificador(): void
    {
        $motor = Mockery::mock(MotorBusquedaService::class);
        $indexaciones = Mockery::mock(MotorIndexacionService::class);
        $usuario = new User;
        $usuario->id = 15;
        $ejecucion = new EjecucionIndexacionMotor([
            'fastapi_identificador' => 'idx-100',
            'respuesta_inicio' => ['mensaje' => 'Aceptada'],
        ]);
        $ejecucion->id = 8;
        $indexaciones->shouldReceive('iniciarManual')->once()->with($usuario)->andReturn($ejecucion);

        $resultado = (new MotorAdministracionService($motor, $indexaciones))->iniciarIndexacion($usuario);

        $this->assertSame(8, $resultado['ejecucion_id']);
        $this->assertSame('idx-100', $resultado['identificador']);
        $this->assertSame('Aceptada', $resultado['mensaje']);
    }
}
