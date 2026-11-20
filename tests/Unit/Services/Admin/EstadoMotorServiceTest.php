<?php

namespace Tests\Unit\Services\Admin;

use App\Services\BusquedaInteligente\Administracion\EstadoMotorService;
use App\Services\BusquedaInteligente\MotorBusquedaService;
use Mockery;
use Tests\TestCase;

class EstadoMotorServiceTest extends TestCase
{
    public function test_normaliza_el_estado_individual_de_los_servicios(): void
    {
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('estado')->once()->andReturn([
            'servicios' => [
                'fastapi' => ['estado' => 'activo'],
                'mysql' => ['estado' => 'desconectado', 'detalle' => 'Sin conexion'],
                'qdrant' => ['estado' => 'ok'],
            ],
        ]);

        $estado = (new EstadoMotorService($motor))->estadoServicios();

        $this->assertSame('degradado', $estado['estado_general']);
        $this->assertSame('activo', $estado['servicios']['fastapi']['categoria']);
        $this->assertSame('desconectado', $estado['servicios']['mysql']['categoria']);
        $this->assertSame('activo', $estado['servicios']['qdrant']['categoria']);
    }
}
