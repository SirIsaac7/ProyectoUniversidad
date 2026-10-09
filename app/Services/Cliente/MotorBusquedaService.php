<?php

namespace App\Services\Cliente;

use App\Exceptions\MotorBusquedaException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MotorBusquedaService
{
    public function buscar(string $consulta, array $filtros = []): array
    {
        $consulta = trim($consulta);
        $respuesta = $this->solicitarJson('POST', '/buscar', $this->payloadBusqueda($consulta, $filtros));

        if (! isset($respuesta['resultados']) || ! is_array($respuesta['resultados'])) {
            $this->registrarJsonInvalido('/buscar', $respuesta);

            throw new MotorBusquedaException(
                'El buscador devolvio una respuesta que no se pudo interpretar.',
                'json_invalido',
            );
        }

        return [
            'consulta' => is_string($respuesta['consulta'] ?? null) ? $respuesta['consulta'] : $consulta,
            'total' => is_numeric($respuesta['total'] ?? null) ? (int) $respuesta['total'] : count($respuesta['resultados']),
            'resultados' => array_values($respuesta['resultados']),
        ];
    }

    public function estado(): array
    {
        return $this->solicitarJson('GET', '/motor/estado', autenticar: true);
    }

    public function iniciarIndexacion(): array
    {
        return $this->solicitarJson('POST', '/motor/indexacion', autenticar: true);
    }

    public function consultarIndexacion(string $identificador): array
    {
        return $this->solicitarJson(
            'GET',
            '/motor/indexacion/'.$this->identificadorSeguro($identificador),
            autenticar: true,
        );
    }

    public function iniciarBusqueda(string $consulta, array $filtros = []): array
    {
        return $this->solicitarJson(
            'POST',
            '/motor/busquedas',
            $this->payloadBusqueda(trim($consulta), $filtros),
            autenticar: true,
        );
    }

    public function consultarBusqueda(string $identificador): array
    {
        return $this->solicitarJson(
            'GET',
            '/motor/busquedas/'.$this->identificadorSeguro($identificador),
            autenticar: true,
        );
    }

    public function resultadosBusqueda(string $identificador): array
    {
        return $this->solicitarJson(
            'GET',
            '/motor/busquedas/'.$this->identificadorSeguro($identificador).'/resultados',
            autenticar: true,
        );
    }

    private function solicitarJson(
        string $metodo,
        string $ruta,
        ?array $payload = null,
        bool $autenticar = false,
    ): array {
        $url = $this->url($ruta);
        $request = $this->request($autenticar);

        try {
            $opciones = $payload === null ? [] : ['json' => $payload];
            $response = $request->send($metodo, $url, $opciones);
        } catch (ConnectionException $exception) {
            $esTimeout = $this->esTimeout($exception);

            Log::error('Fallo de conexion con el motor de busqueda.', [
                'method' => $metodo,
                'url' => $url,
                'type' => $esTimeout ? 'timeout' : 'connection',
                'message' => $exception->getMessage(),
            ]);

            throw new MotorBusquedaException(
                $esTimeout
                    ? 'El motor de busqueda tardo demasiado en responder.'
                    : 'No pudimos conectar con el buscador. Intenta nuevamente en unos minutos.',
                $esTimeout ? 'timeout' : 'conexion',
                previous: $exception,
            );
        } catch (Throwable $exception) {
            Log::error('Error inesperado al consultar el motor de busqueda.', [
                'method' => $metodo,
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);

            throw new MotorBusquedaException(
                'No se pudo completar la comunicacion con el buscador.',
                'inesperado',
                previous: $exception,
            );
        }

        if (in_array($response->status(), [401, 403], true)) {
            Log::warning('El motor de busqueda rechazo la clave API.', [
                'method' => $metodo,
                'url' => $url,
                'status' => $response->status(),
            ]);

            throw new MotorBusquedaException(
                'Laravel no esta autorizado para comunicarse con el motor de busqueda.',
                'autorizacion',
                $response->status(),
            );
        }

        if (! $response->successful()) {
            try {
                $detalle = $this->detalleError($response->json());
            } catch (Throwable) {
                $detalle = null;
            }

            Log::warning('El motor de busqueda respondio con un estado HTTP inesperado.', [
                'method' => $metodo,
                'url' => $url,
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 1000),
            ]);

            throw new MotorBusquedaException(
                $detalle ?? 'El motor de busqueda no pudo procesar la solicitud.',
                'respuesta_http',
                $response->status(),
            );
        }

        try {
            $data = $response->json();
        } catch (Throwable) {
            $data = null;
        }

        if (! is_array($data)) {
            $this->registrarJsonInvalido($ruta, $response->body());

            throw new MotorBusquedaException(
                'El motor de busqueda devolvio una respuesta que no se pudo interpretar.',
                'json_invalido',
                $response->status(),
            );
        }

        return $data;
    }

    private function request(bool $autenticar): PendingRequest
    {
        $request = Http::acceptJson()
            ->asJson()
            ->connectTimeout((int) config('services.motor_busqueda.connect_timeout', 5))
            ->timeout((int) config('services.motor_busqueda.timeout', 60));

        if (! $autenticar) {
            return $request;
        }

        $key = trim((string) config('services.motor_busqueda.key'));

        if ($key === '') {
            Log::error('La clave API del motor de busqueda no esta configurada.');

            throw new MotorBusquedaException(
                'La conexion segura con el motor de busqueda no esta configurada.',
                'configuracion',
            );
        }

        return $request->withHeaders(['x-motor-key' => $key]);
    }

    private function url(string $ruta): string
    {
        $urlBase = rtrim((string) config('services.motor_busqueda.url'), '/');

        if ($urlBase === '') {
            Log::error('La URL del motor de busqueda no esta configurada.');

            throw new MotorBusquedaException(
                'El buscador no esta disponible en este momento.',
                'configuracion',
            );
        }

        return $urlBase.'/'.ltrim($ruta, '/');
    }

    private function payloadBusqueda(string $consulta, array $filtros): array
    {
        return [
            'consulta' => $consulta,
            'filtros' => [
                'disponible' => ($filtros['disponible'] ?? null) === true ? true : null,
                'tipo_atencion' => $filtros['tipo_atencion'] ?? null,
            ],
        ];
    }

    private function identificadorSeguro(string $identificador): string
    {
        $identificador = trim($identificador);

        if ($identificador === '') {
            throw new MotorBusquedaException(
                'El identificador de la operacion es obligatorio.',
                'identificador_invalido',
            );
        }

        return rawurlencode($identificador);
    }

    private function esTimeout(ConnectionException $exception): bool
    {
        $mensaje = Str::lower($exception->getMessage());

        return Str::contains($mensaje, ['timed out', 'timeout', 'curl error 28']);
    }

    private function registrarJsonInvalido(string $ruta, mixed $contenido): void
    {
        Log::warning('El motor de busqueda devolvio JSON invalido.', [
            'route' => $ruta,
            'body' => Str::limit(is_string($contenido) ? $contenido : (string) json_encode($contenido), 1000),
        ]);
    }

    private function detalleError(mixed $respuesta): ?string
    {
        if (! is_array($respuesta)) {
            return null;
        }

        foreach (['detalle', 'detail', 'mensaje', 'message', 'error'] as $clave) {
            $detalle = $respuesta[$clave] ?? null;

            if (is_string($detalle) && trim($detalle) !== '') {
                return Str::limit(trim($detalle), 500);
            }
        }

        return null;
    }
}
