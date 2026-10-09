<?php

namespace Tests\Unit\Services\Cliente;

use App\Exceptions\MotorBusquedaException;
use App\Services\Cliente\MotorBusquedaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MotorBusquedaServiceTest extends TestCase
{
    public function test_busca_sin_filtros_y_no_envia_limite(): void
    {
        $this->configurarMotor();
        Http::fake([
            'http://127.0.0.1:8010/buscar' => Http::response([
                'consulta' => 'pantalla negra',
                'total' => 2,
                'resultados' => [
                    ['document_id' => 'proveedor:9', 'final_score' => 0.91],
                    ['document_id' => 'proveedor:2', 'final_score' => 0.75],
                ],
            ]),
        ]);

        $resultado = app(MotorBusquedaService::class)->buscar('pantalla negra');

        $this->assertSame(['proveedor:9', 'proveedor:2'], array_column($resultado['resultados'], 'document_id'));
        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:8010/buscar'
                && $request['consulta'] === 'pantalla negra'
                && $request['filtros'] === ['disponible' => null, 'tipo_atencion' => null]
                && ! array_key_exists('limite', $request->data())
                && ! $request->hasHeader('x-motor-key');
        });
    }

    #[DataProvider('filtrosProvider')]
    public function test_envia_los_filtros_admitidos(array $filtros, array $esperados): void
    {
        $this->configurarMotor();
        Http::fake(['*' => Http::response(['consulta' => 'equipo', 'total' => 0, 'resultados' => []])]);

        app(MotorBusquedaService::class)->buscar('equipo', $filtros);

        Http::assertSent(fn ($request) => $request['filtros'] === $esperados);
    }

    public static function filtrosProvider(): array
    {
        return [
            'domicilio' => [['tipo_atencion' => 'domicilio'], ['disponible' => null, 'tipo_atencion' => 'domicilio']],
            'local' => [['tipo_atencion' => 'local'], ['disponible' => null, 'tipo_atencion' => 'local']],
            'disponibles' => [['disponible' => true], ['disponible' => true, 'tipo_atencion' => null]],
        ];
    }

    public function test_acepta_una_busqueda_sin_resultados(): void
    {
        $this->configurarMotor();
        Http::fake(['*' => Http::response(['consulta' => 'inexistente', 'total' => 0, 'resultados' => []])]);

        $resultado = app(MotorBusquedaService::class)->buscar('inexistente');

        $this->assertSame(0, $resultado['total']);
        $this->assertSame([], $resultado['resultados']);
    }

    public function test_muestra_un_error_controlado_si_fastapi_no_responde(): void
    {
        $this->configurarMotor();
        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $this->expectException(MotorBusquedaException::class);
        $this->expectExceptionMessage('No pudimos conectar con el buscador.');

        app(MotorBusquedaService::class)->buscar('pantalla rota');
    }

    public function test_rechaza_una_respuesta_json_invalida(): void
    {
        $this->configurarMotor();
        Http::fake(['*' => Http::response('respuesta no valida', 200, ['Content-Type' => 'text/plain'])]);

        $this->expectException(MotorBusquedaException::class);

        app(MotorBusquedaService::class)->buscar('pantalla rota');
    }

    public function test_consulta_el_estado_con_la_clave_solo_desde_backend(): void
    {
        $this->configurarMotor();
        Http::fake([
            'http://127.0.0.1:8010/motor/estado' => Http::response([
                'fastapi' => 'ok',
                'mysql' => 'ok',
                'qdrant' => 'ok',
            ]),
        ]);

        $estado = app(MotorBusquedaService::class)->estado();

        $this->assertSame('ok', $estado['fastapi']);
        $this->assertSame('ok', $estado['mysql']);
        $this->assertSame('ok', $estado['qdrant']);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request->url() === 'http://127.0.0.1:8010/motor/estado'
            && $request->hasHeader('x-motor-key', 'clave-secreta'));
    }

    public function test_prepara_todos_los_endpoints_de_operaciones_del_motor(): void
    {
        $this->configurarMotor();
        Http::fake(fn ($request) => Http::response([
            'metodo' => $request->method(),
            'url' => $request->url(),
            'payload' => $request->data(),
        ], str_contains($request->url(), '/motor/indexacion') || str_ends_with($request->url(), '/motor/busquedas') ? 202 : 200));

        $servicio = app(MotorBusquedaService::class);
        $servicio->iniciarIndexacion();
        $servicio->consultarIndexacion('idx-123');
        $servicio->iniciarBusqueda('pantalla rota', ['tipo_atencion' => 'local']);
        $servicio->consultarBusqueda('bus-456');
        $servicio->resultadosBusqueda('bus-456');

        $solicitudes = collect(Http::recorded())->map(fn ($registro) => [
            'method' => $registro[0]->method(),
            'url' => $registro[0]->url(),
            'key' => $registro[0]->header('x-motor-key')[0] ?? null,
            'data' => $registro[0]->data(),
        ])->all();

        $this->assertSame([
            ['method' => 'POST', 'url' => 'http://127.0.0.1:8010/motor/indexacion', 'key' => 'clave-secreta', 'data' => []],
            ['method' => 'GET', 'url' => 'http://127.0.0.1:8010/motor/indexacion/idx-123', 'key' => 'clave-secreta', 'data' => []],
            [
                'method' => 'POST',
                'url' => 'http://127.0.0.1:8010/motor/busquedas',
                'key' => 'clave-secreta',
                'data' => [
                    'consulta' => 'pantalla rota',
                    'filtros' => ['disponible' => null, 'tipo_atencion' => 'local'],
                ],
            ],
            ['method' => 'GET', 'url' => 'http://127.0.0.1:8010/motor/busquedas/bus-456', 'key' => 'clave-secreta', 'data' => []],
            ['method' => 'GET', 'url' => 'http://127.0.0.1:8010/motor/busquedas/bus-456/resultados', 'key' => 'clave-secreta', 'data' => []],
        ], $solicitudes);
    }

    public function test_no_realiza_la_solicitud_protegida_si_falta_la_clave(): void
    {
        $this->configurarMotor();
        config()->set('services.motor_busqueda.key', null);
        Http::fake();

        try {
            app(MotorBusquedaService::class)->estado();
            $this->fail('Se esperaba una excepcion de configuracion.');
        } catch (MotorBusquedaException $exception) {
            $this->assertSame('configuracion', $exception->tipo);
        }

        Http::assertNothingSent();
    }

    #[DataProvider('erroresHttpProvider')]
    public function test_convierte_respuestas_http_en_errores_controlados(int $status, string $tipo): void
    {
        $this->configurarMotor();
        Http::fake(['*' => Http::response(['detalle' => 'error tecnico'], $status)]);

        try {
            app(MotorBusquedaService::class)->estado();
            $this->fail('Se esperaba una excepcion del motor.');
        } catch (MotorBusquedaException $exception) {
            $this->assertSame($tipo, $exception->tipo);
            $this->assertSame($status, $exception->statusHttp);
        }
    }

    public static function erroresHttpProvider(): array
    {
        return [
            'clave incorrecta' => [401, 'autorizacion'],
            'clave sin permisos' => [403, 'autorizacion'],
            'respuesta inesperada' => [500, 'respuesta_http'],
        ];
    }

    public function test_distingue_un_timeout_de_un_error_de_conexion(): void
    {
        $this->configurarMotor();
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out'));

        try {
            app(MotorBusquedaService::class)->estado();
            $this->fail('Se esperaba una excepcion de timeout.');
        } catch (MotorBusquedaException $exception) {
            $this->assertSame('timeout', $exception->tipo);
        }
    }

    private function configurarMotor(): void
    {
        config()->set('services.motor_busqueda.url', 'http://127.0.0.1:8010');
        config()->set('services.motor_busqueda.key', 'clave-secreta');
        config()->set('services.motor_busqueda.connect_timeout', 5);
        config()->set('services.motor_busqueda.timeout', 60);
    }
}
