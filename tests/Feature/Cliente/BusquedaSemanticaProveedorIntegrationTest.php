<?php

namespace Tests\Feature\Cliente;

use App\Models\Especialidad;
use App\Models\PerfilProveedor;
use App\Models\ProveedorEspecialidad;
use App\Models\Rubro;
use App\Models\RubroTipoServicio;
use App\Models\TipoServicio;
use App\Models\User;
use App\Services\Cliente\BusquedaServicioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusquedaSemanticaProveedorIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_conserva_el_ranking_y_excluye_proveedores_no_vigentes_en_mysql(): void
    {
        $especialidad = $this->crearEspecialidadActiva();
        $primero = $this->crearProveedor($especialidad, 'Primero en ranking');
        $segundo = $this->crearProveedor($especialidad, 'Segundo en ranking');
        $suspendido = $this->crearProveedor($especialidad, 'Proveedor suspendido', estado: false);
        $noAprobado = $this->crearProveedor($especialidad, 'Proveedor pendiente', verificacion: 'pendiente');
        $usuarioInactivo = $this->crearProveedor($especialidad, 'Usuario inactivo', usuarioActivo: false);

        $resultados = app(BusquedaServicioService::class)->proveedoresSemanticos([
            [
                'document_id' => 'proveedor:'.$segundo->id,
                'final_score' => 0.99,
                'ranking' => ['servicios_completados' => 12],
                'explicacion' => ['Su especialidad coincide con la consulta.'],
            ],
            ['document_id' => 'proveedor:'.$suspendido->id, 'final_score' => 0.98],
            ['document_id' => 'proveedor:'.$noAprobado->id, 'final_score' => 0.97],
            ['document_id' => 'proveedor:'.$usuarioInactivo->id, 'final_score' => 0.96],
            ['document_id' => 'proveedor:'.$primero->id, 'final_score' => 0.50],
        ]);

        $this->assertSame([$segundo->id, $primero->id], array_column($resultados, 'id'));
        $this->assertSame(['Segundo en ranking', 'Primero en ranking'], array_column($resultados, 'nombre_publico'));
        $this->assertSame(12, $resultados[0]['servicios_completados']);
        $this->assertSame(['Su especialidad coincide con la consulta.'], $resultados[0]['explicacion']);
    }

    private function crearEspecialidadActiva(): Especialidad
    {
        $sufijo = Str::lower(Str::random(8));
        $rubro = Rubro::create(['nombre' => 'Rubro '.$sufijo, 'estado' => true]);
        $tipo = TipoServicio::create(['nombre' => 'Tipo '.$sufijo, 'estado' => true]);
        $relacion = RubroTipoServicio::create([
            'rubro_id' => $rubro->id,
            'tipo_servicio_id' => $tipo->id,
            'estado' => true,
        ]);

        return Especialidad::create([
            'rubro_tipo_servicio_id' => $relacion->id,
            'nombre' => 'Especialidad '.$sufijo,
            'estado' => true,
        ]);
    }

    private function crearProveedor(
        Especialidad $especialidad,
        string $nombre,
        bool $estado = true,
        string $verificacion = 'aprobado',
        bool $usuarioActivo = true,
    ): PerfilProveedor {
        $usuario = User::factory()->create(['estado' => $usuarioActivo]);
        $perfil = PerfilProveedor::create([
            'user_id' => $usuario->id,
            'nombre_publico' => $nombre,
            'descripcion' => 'Servicio técnico de prueba',
            'estado_verificacion' => $verificacion,
            'estado' => $estado,
        ]);
        ProveedorEspecialidad::create([
            'perfil_proveedor_id' => $perfil->id,
            'especialidad_id' => $especialidad->id,
            'es_principal' => true,
            'estado' => true,
        ]);

        return $perfil;
    }
}
