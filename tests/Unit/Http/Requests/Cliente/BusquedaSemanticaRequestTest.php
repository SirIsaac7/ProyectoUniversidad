<?php

namespace Tests\Unit\Http\Requests\Cliente;

use App\Http\Requests\BusquedaInteligente\Publica\BusquedaSemanticaRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BusquedaSemanticaRequestTest extends TestCase
{
    public function test_rechaza_una_consulta_vacia(): void
    {
        $request = new BusquedaSemanticaRequest;
        $validator = Validator::make(['consulta' => ''], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('consulta', $validator->errors()->toArray());
    }

    #[DataProvider('modalidadesProvider')]
    public function test_acepta_las_modalidades_permitidas(?string $modalidad): void
    {
        $request = new BusquedaSemanticaRequest;
        $validator = Validator::make([
            'consulta' => 'Necesito reparar mi laptop',
            'tipo_atencion' => $modalidad,
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public static function modalidadesProvider(): array
    {
        return [[null], ['domicilio'], ['local']];
    }

    public function test_rechaza_una_modalidad_no_admitida(): void
    {
        $request = new BusquedaSemanticaRequest;
        $validator = Validator::make([
            'consulta' => 'Necesito reparar mi laptop',
            'tipo_atencion' => 'remoto',
        ], $request->rules());

        $this->assertTrue($validator->fails());
    }
}
