<?php

namespace Tests\Unit\Services\Cliente;

use App\Services\BusquedaInteligente\Publica\BusquedaServicioService;
use Tests\TestCase;

class BusquedaSemanticaProveedorTest extends TestCase
{
    public function test_extrae_ids_validos_y_conserva_el_orden_del_motor(): void
    {
        $resultados = [
            ['document_id' => 'proveedor:27'],
            ['document_id' => 'usuario:4'],
            ['document_id' => 'proveedor:3'],
            ['document_id' => 'proveedor:27'],
            ['document_id' => 'proveedor:0'],
            ['document_id' => 'proveedor:15 texto'],
        ];

        $ids = app(BusquedaServicioService::class)->idsProveedoresSemanticos($resultados);

        $this->assertSame([27, 3], $ids);
    }
}
