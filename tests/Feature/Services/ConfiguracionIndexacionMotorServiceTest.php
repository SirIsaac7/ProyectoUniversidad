<?php

namespace Tests\Feature\Services;

use App\Services\BusquedaInteligente\Administracion\ConfiguracionIndexacionMotorService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConfiguracionIndexacionMotorServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_la_programacion_desactivada_no_reclama_ejecuciones(): void
    {
        $servicio = app(ConfiguracionIndexacionMotorService::class);
        $servicio->actualizar([
            'activo' => false,
            'frecuencia' => 'diario',
            'hora_ejecucion' => '02:00',
        ]);

        $this->assertFalse($servicio->reclamarEjecucionProgramada(
            CarbonImmutable::parse('2026-10-09 02:00:00', 'America/La_Paz'),
        ));
        $this->assertNull($servicio->proximaEjecucion());
    }

    public function test_reclama_una_programacion_diaria_una_sola_vez_por_minuto(): void
    {
        $servicio = app(ConfiguracionIndexacionMotorService::class);
        $servicio->actualizar([
            'activo' => true,
            'frecuencia' => 'diario',
            'hora_ejecucion' => '14:30',
        ]);
        $momento = CarbonImmutable::parse('2026-10-09 14:30:15', 'America/La_Paz');

        $this->assertTrue($servicio->reclamarEjecucionProgramada($momento));
        $this->assertFalse($servicio->reclamarEjecucionProgramada($momento->addSeconds(20)));
    }

    public function test_la_programacion_semanal_respeta_el_dia_configurado(): void
    {
        $servicio = app(ConfiguracionIndexacionMotorService::class);
        $servicio->actualizar([
            'activo' => true,
            'frecuencia' => 'semanal',
            'dia_semana' => 5,
            'hora_ejecucion' => '09:15',
        ]);

        $this->assertFalse($servicio->reclamarEjecucionProgramada(
            CarbonImmutable::parse('2026-10-08 09:15:00', 'America/La_Paz'),
        ));
        $this->assertTrue($servicio->reclamarEjecucionProgramada(
            CarbonImmutable::parse('2026-10-09 09:15:00', 'America/La_Paz'),
        ));
    }

    public function test_calcula_la_proxima_ejecucion_en_la_zona_horaria_configurada(): void
    {
        $servicio = app(ConfiguracionIndexacionMotorService::class);
        $servicio->actualizar([
            'activo' => true,
            'frecuencia' => 'semanal',
            'dia_semana' => 5,
            'hora_ejecucion' => '09:15',
        ]);

        $proxima = $servicio->proximaEjecucion(
            CarbonImmutable::parse('2026-10-09 10:00:00', 'America/La_Paz'),
        );

        $this->assertSame('America/La_Paz', $proxima->timezoneName);
        $this->assertSame('2026-10-16 09:15', $proxima->format('Y-m-d H:i'));
    }
}
