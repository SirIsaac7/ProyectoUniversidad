<?php

namespace Tests\Feature\Services;

use App\Exceptions\MotorBusquedaException;
use App\Models\EjecucionIndexacionMotor;
use App\Models\User;
use App\Services\BusquedaInteligente\Administracion\MotorIndexacionService;
use App\Services\BusquedaInteligente\MotorBusquedaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class MotorIndexacionServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registra_una_indexacion_manual_con_su_usuario(): void
    {
        $usuario = User::factory()->create();
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('iniciarIndexacion')->once()->andReturn([
            'identificador' => 'manual-101',
            'estado' => 'pendiente',
        ]);

        $ejecucion = (new MotorIndexacionService($motor))->iniciarManual($usuario);

        $this->assertSame('manual', $ejecucion->tipo);
        $this->assertSame($usuario->id, $ejecucion->usuario_id);
        $this->assertSame('manual-101', $ejecucion->fastapi_identificador);
        $this->assertDatabaseHas('ejecuciones_indexacion_motor', [
            'id' => $ejecucion->id,
            'tipo' => 'manual',
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_identifica_una_indexacion_automatica_como_sistema(): void
    {
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('iniciarIndexacion')->once()->andReturn([
            'identificador' => 'auto-201',
            'estado' => 'pendiente',
        ]);

        $ejecucion = (new MotorIndexacionService($motor))->iniciarAutomatica();

        $this->assertSame('automatica', $ejecucion->tipo);
        $this->assertNull($ejecucion->usuario_id);
    }

    public function test_impide_una_ejecucion_duplicada(): void
    {
        EjecucionIndexacionMotor::create([
            'fastapi_identificador' => 'activa-1',
            'tipo' => 'automatica',
            'estado' => 'en_ejecucion',
            'categoria_estado' => 'ejecucion',
            'iniciado_en' => now(),
        ]);
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldNotReceive('iniciarIndexacion');

        $this->expectException(MotorBusquedaException::class);
        $this->expectExceptionMessage('Ya existe una indexacion en curso.');

        (new MotorIndexacionService($motor))->iniciarAutomatica();
    }

    public function test_registra_el_error_si_fastapi_no_acepta_el_inicio(): void
    {
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('iniciarIndexacion')->once()->andThrow(
            new MotorBusquedaException('FastAPI desconectado', 'conexion'),
        );

        try {
            (new MotorIndexacionService($motor))->iniciarAutomatica();
            $this->fail('Se esperaba el error de conexion.');
        } catch (MotorBusquedaException) {
            $ejecucion = EjecucionIndexacionMotor::latest('id')->first();
            $this->assertSame('error', $ejecucion->categoria_estado);
            $this->assertSame(['FastAPI desconectado'], $ejecucion->errores);
            $this->assertNotNull($ejecucion->finalizado_en);
        }
    }

    public function test_actualiza_y_conserva_los_resultados_finales(): void
    {
        $ejecucion = EjecucionIndexacionMotor::create([
            'fastapi_identificador' => 'final-301',
            'tipo' => 'manual',
            'estado' => 'en_ejecucion',
            'categoria_estado' => 'ejecucion',
            'iniciado_en' => now()->subMinute(),
        ]);
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('consultarIndexacion')->once()->with('final-301')->andReturn([
            'identificador' => 'final-301',
            'estado' => 'completado',
            'fase_actual' => 'finalizacion',
            'total_proveedores' => 10,
            'proveedores_procesados' => 10,
            'resultados' => [
                'proveedores_nuevos' => 2,
                'proveedores_reindexados' => 8,
                'metadatos_actualizados' => 4,
                'proveedores_sin_modificaciones' => 3,
                'proveedores_eliminados' => 1,
            ],
            'fases' => [['nombre' => 'mysql'], ['nombre' => 'qdrant']],
        ]);

        $actualizada = (new MotorIndexacionService($motor))->actualizar($ejecucion);

        $this->assertSame('completado', $actualizada->categoria_estado);
        $this->assertSame(10, $actualizada->proveedores_procesados);
        $this->assertSame(2, $actualizada->proveedores_nuevos);
        $this->assertSame(8, $actualizada->proveedores_reindexados);
        $this->assertSame(4, $actualizada->metadatos_actualizados);
        $this->assertSame(3, $actualizada->proveedores_sin_modificaciones);
        $this->assertSame(1, $actualizada->proveedores_eliminados);
        $this->assertSame(['mysql', 'qdrant'], array_column($actualizada->fases, 'nombre'));
        $this->assertNotNull($actualizada->finalizado_en);
    }

    public function test_un_error_temporal_de_seguimiento_no_elimina_la_ejecucion_activa(): void
    {
        $ejecucion = EjecucionIndexacionMotor::create([
            'fastapi_identificador' => 'retry-401',
            'tipo' => 'automatica',
            'estado' => 'en_ejecucion',
            'categoria_estado' => 'ejecucion',
            'iniciado_en' => now(),
        ]);
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('consultarIndexacion')->once()->andThrow(
            new MotorBusquedaException('Conexion perdida', 'conexion'),
        );

        try {
            (new MotorIndexacionService($motor))->actualizar($ejecucion);
            $this->fail('Se esperaba el error temporal.');
        } catch (MotorBusquedaException) {
            $ejecucion->refresh();
            $this->assertSame('ejecucion', $ejecucion->categoria_estado);
            $this->assertSame('Conexion perdida', $ejecucion->ultimo_error_seguimiento);
        }
    }

    public function test_recupera_una_solicitud_interrumpida_sin_identificador(): void
    {
        $ejecucion = EjecucionIndexacionMotor::create([
            'tipo' => 'automatica',
            'estado' => 'solicitando',
            'categoria_estado' => 'pendiente',
            'iniciado_en' => now()->subMinutes(10),
        ]);
        $ejecucion->timestamps = false;
        $ejecucion->created_at = now()->subMinutes(10);
        $ejecucion->save();
        $motor = Mockery::mock(MotorBusquedaService::class);

        (new MotorIndexacionService($motor))->actualizarEnCurso();

        $this->assertSame('error', $ejecucion->refresh()->categoria_estado);
        $this->assertStringContainsString('reinicio', $ejecucion->errores[0]);
    }

    public function test_marca_error_si_fastapi_ya_no_conserva_la_ejecucion(): void
    {
        $ejecucion = EjecucionIndexacionMotor::create([
            'fastapi_identificador' => 'perdida-501',
            'tipo' => 'manual',
            'estado' => 'en_ejecucion',
            'categoria_estado' => 'ejecucion',
            'iniciado_en' => now(),
        ]);
        $motor = Mockery::mock(MotorBusquedaService::class);
        $motor->shouldReceive('consultarIndexacion')->once()->andThrow(
            new MotorBusquedaException('No encontrada', 'respuesta_http', 404),
        );

        $actualizada = (new MotorIndexacionService($motor))->actualizar($ejecucion);

        $this->assertSame('error', $actualizada->categoria_estado);
        $this->assertNotNull($actualizada->finalizado_en);
        $this->assertStringContainsString('reiniciado', $actualizada->errores[0]);
    }

    public function test_filtra_y_pagina_el_historial_permanente(): void
    {
        $usuario = User::factory()->create();
        foreach (range(1, 12) as $numero) {
            EjecucionIndexacionMotor::create([
                'tipo' => $numero % 2 === 0 ? 'automatica' : 'manual',
                'usuario_id' => $numero % 2 === 0 ? null : $usuario->id,
                'estado' => $numero <= 4 ? 'error' : 'completado',
                'categoria_estado' => $numero <= 4 ? 'error' : 'completado',
                'iniciado_en' => now()->subMinutes($numero + 1),
                'finalizado_en' => now()->subMinutes($numero),
            ]);
        }
        $motor = Mockery::mock(MotorBusquedaService::class);
        $servicio = new MotorIndexacionService($motor);

        $pagina = $servicio->historial(['tipo' => 'automatica'], 3);
        $errores = $servicio->historial(['estado' => 'error'], 10);

        $this->assertSame(6, $pagina->total());
        $this->assertCount(3, $pagina->items());
        $this->assertTrue(collect($pagina->items())->every(fn ($item) => $item->tipo === 'automatica'));
        $this->assertSame(4, $errores->total());
        $this->assertTrue(collect($errores->items())->every(fn ($item) => $item->categoria_estado === 'error'));
    }
}
