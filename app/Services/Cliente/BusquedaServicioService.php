<?php

namespace App\Services\Cliente;

use App\Models\Especialidad;
use App\Models\PerfilProveedor;
use App\Models\Rubro;
use App\Models\RubroTipoServicio;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BusquedaServicioService
{
    public function datosVista(array $filtros): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        return [
            'filtros' => $filtros,
            'tieneBusqueda' => $this->tieneBusqueda($filtros),
            'rubros' => $this->rubros(),
            'tiposServicio' => $this->tiposServicio($filtros['rubro_id']),
            'filtrosBase' => $this->filtrosBase($filtros),
            'proveedores' => $this->proveedores($filtros),
            'tiposAtencion' => $this->tiposAtencion(),
        ];
    }

    public function busquedaInteligente(array $data): array
    {
        $respuestaIa = $this->consultarApiBusquedaInteligente($data);
        $terminos = $this->terminosBusquedaInteligente($respuestaIa, $data['texto_problema']);
        $nombresExternos = collect($respuestaIa['tecnicos'] ?? [])
            ->pluck('nombre')
            ->filter()
            ->map(fn ($nombre) => $this->normalizarTexto($nombre))
            ->values();

        $proveedores = PerfilProveedor::query()
            ->with([
                'user:id,name,email,avatar',
                'ubicacion',
                'horarios' => fn ($query) => $query->where('estado', true)->orderBy('dia_semana')->orderBy('hora_inicio'),
                'portafolio' => fn ($query) => $query->where('estado', true)->latest()->take(6),
                'portafolio.imagenes' => fn ($query) => $query->where('estado', true),
                'documentos' => fn ($query) => $query->where('estado', true)->where('estado_revision', 'aprobado'),
                'documentos.tipoDocumentoProveedor:id,nombre,descripcion,obligatorio',
                'proveedorEspecialidades.especialidad.rubroTipoServicio.rubro',
                'proveedorEspecialidades.especialidad.rubroTipoServicio.tipoServicio',
                'solicitudes.cita.calificacion',
            ])
            ->where('estado', true)
            ->where('estado_verificacion', 'aprobado')
            ->whereHas('user', fn ($query) => $query->where('estado', true))
            ->whereHas('proveedorEspecialidades', function ($query) {
                $query->where('estado', true)
                    ->whereHas('especialidad', fn ($especialidadQuery) => $especialidadQuery->where('estado', true));
            })
            ->get()
            ->map(function (PerfilProveedor $perfilProveedor) use ($terminos, $nombresExternos, $data) {
                $score = $this->scoreProveedorInteligente($perfilProveedor, $terminos, $nombresExternos, $data);

                if ($score['total'] <= 0) {
                    return null;
                }

                return array_merge($this->mapearProveedor($perfilProveedor), [
                    'score_ia' => $score['total'],
                    'razones_ia' => $score['razones'],
                    'distancia_ia' => $score['distancia'],
                    'rating_ia' => $this->promedioCalificacionProveedor($perfilProveedor),
                ]);
            })
            ->filter()
            ->sortByDesc('score_ia')
            ->take(10)
            ->values();

        return [
            'api' => $respuestaIa,
            'terminos' => $terminos,
            'proveedores' => $proveedores,
            'total' => $proveedores->count(),
        ];
    }

    public function normalizarFiltros(array $filtros): array
    {
        return [
            'q' => trim($filtros['q'] ?? ''),
            'rubro_id' => $filtros['rubro_id'] ?? '',
            'tipo_servicio_id' => $filtros['tipo_servicio_id'] ?? '',
            'zona' => trim($filtros['zona'] ?? ''),
            'usar_ubicacion_actual' => (bool) ($filtros['usar_ubicacion_actual'] ?? false),
            'latitud' => $filtros['latitud'] ?? '',
            'longitud' => $filtros['longitud'] ?? '',
        ];
    }

    protected function rubros()
    {
        return Rubro::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'imagen']);
    }

    protected function tiposServicio(?string $rubroId = null)
    {
        return RubroTipoServicio::query()
            ->with('tipoServicio:id,nombre')
            ->where('estado', true)
            ->whereHas('tipoServicio', fn ($query) => $query->where('estado', true))
            ->when($rubroId, fn ($query) => $query->where('rubro_id', $rubroId))
            ->get()
            ->map(fn (RubroTipoServicio $rubroTipoServicio) => $rubroTipoServicio->tipoServicio)
            ->filter()
            ->unique('id')
            ->sortBy('nombre')
            ->values();
    }

    protected function filtrosBase(array $filtros): array
    {
        return array_filter([
            'q' => $filtros['q'],
            'usar_ubicacion_actual' => $filtros['usar_ubicacion_actual'] ? 1 : null,
            'latitud' => $filtros['latitud'],
            'longitud' => $filtros['longitud'],
        ], fn ($valor) => filled($valor));
    }

    protected function tieneBusqueda(array $filtros): bool
    {
        return $filtros['q'] !== ''
            || $filtros['tipo_servicio_id'] !== ''
            || $filtros['zona'] !== ''
            || ($filtros['usar_ubicacion_actual'] && $filtros['latitud'] !== '' && $filtros['longitud'] !== '');
    }

    protected function proveedores(array $filtros)
    {
        if (! $this->tieneBusqueda($filtros)) {
            return new LengthAwarePaginator([], 0, 5);
        }

        return PerfilProveedor::query()
            ->with([
                'user:id,name,email,avatar',
                'ubicacion',
                'horarios' => fn ($query) => $query->where('estado', true)->orderBy('dia_semana')->orderBy('hora_inicio'),
                'portafolio' => fn ($query) => $query->where('estado', true)->latest()->take(6),
                'portafolio.imagenes' => fn ($query) => $query->where('estado', true),
                'documentos' => fn ($query) => $query->where('estado', true)->where('estado_revision', 'aprobado'),
                'documentos.tipoDocumentoProveedor:id,nombre,descripcion,obligatorio',
                'proveedorEspecialidades.especialidad.rubroTipoServicio.rubro',
                'proveedorEspecialidades.especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->where('estado', true)
            ->where('estado_verificacion', 'aprobado')
            ->whereHas('user', fn ($query) => $query->where('estado', true))
            ->whereHas('proveedorEspecialidades', function ($query) use ($filtros) {
                $query->where('estado', true)
                    ->whereHas('especialidad', function ($especialidadQuery) use ($filtros) {
                        $especialidadQuery->where('estado', true);

                        if ($filtros['rubro_id']) {
                            $especialidadQuery->whereHas('rubroTipoServicio', function ($rubroTipoQuery) use ($filtros) {
                                $rubroTipoQuery->where('rubro_id', $filtros['rubro_id']);
                            });
                        }

                        if ($filtros['tipo_servicio_id']) {
                            $especialidadQuery->whereHas('rubroTipoServicio', function ($rubroTipoQuery) use ($filtros) {
                                $rubroTipoQuery->where('tipo_servicio_id', $filtros['tipo_servicio_id']);
                            });
                        }
                    });
            })
            ->when($filtros['q'] !== '', function ($query) use ($filtros) {
                $texto = $filtros['q'];

                $query->where(function ($subQuery) use ($texto) {
                    $subQuery->where('nombre_publico', 'like', '%' . $texto . '%')
                        ->orWhere('descripcion', 'like', '%' . $texto . '%')
                        ->orWhereHas('proveedorEspecialidades.especialidad', function ($especialidadQuery) use ($texto) {
                            $especialidadQuery->where('nombre', 'like', '%' . $texto . '%')
                                ->orWhere('descripcion', 'like', '%' . $texto . '%')
                                ->orWhereHas('rubroTipoServicio.rubro', function ($rubroQuery) use ($texto) {
                                    $rubroQuery->where('nombre', 'like', '%' . $texto . '%');
                                })
                                ->orWhereHas('rubroTipoServicio.tipoServicio', function ($tipoQuery) use ($texto) {
                                    $tipoQuery->where('nombre', 'like', '%' . $texto . '%');
                                });
                        });
                });
            })
            ->when($filtros['zona'] !== '', function ($query) use ($filtros) {
                $query->whereHas('ubicacion', function ($ubicacionQuery) use ($filtros) {
                    $ubicacionQuery->where('zona', 'like', '%' . $filtros['zona'] . '%')
                        ->orWhere('direccion', 'like', '%' . $filtros['zona'] . '%');
                });
            })
            ->when($this->tieneFiltroUbicacionActual($filtros), function ($query) use ($filtros) {
                $latitud = (float) $filtros['latitud'];
                $longitud = (float) $filtros['longitud'];

                $query->whereHas('ubicacion', function ($ubicacionQuery) use ($latitud, $longitud) {
                    $ubicacionQuery
                        ->whereNotNull('latitud')
                        ->whereNotNull('longitud')
                        ->whereRaw(
                            '(6371 * acos(least(1, greatest(-1, cos(radians(?)) * cos(radians(latitud)) * cos(radians(longitud) - radians(?)) + sin(radians(?)) * sin(radians(latitud)))))) <= coalesce(radio_cobertura_km, 1)',
                            [$latitud, $longitud, $latitud]
                        );
                });
            })
            ->orderBy('nombre_publico')
            ->paginate(5)
            ->through(fn (PerfilProveedor $perfilProveedor) => $this->mapearProveedor($perfilProveedor));
    }

    protected function consultarApiBusquedaInteligente(array $data): array
    {
        $archivo = $data['imagen'];
        $url = (string) config('services.busqueda_inteligente.url');

        if (! $archivo instanceof UploadedFile) {
            return [];
        }

        if (blank($url)) {
            throw new \RuntimeException('No se configuro la URL de la busqueda inteligente.');
        }

        try {
            $response = Http::timeout((int) config('services.busqueda_inteligente.timeout', 30))
                ->acceptJson()
                ->attach(
                    'file',
                    file_get_contents($archivo->getRealPath()),
                    $archivo->getClientOriginalName()
                )
                ->post($url, array_filter([
                    'texto_problema' => $data['texto_problema'],
                    'usar_ubicacion' => (bool) ($data['usar_ubicacion'] ?? false) ? '1' : '0',
                    'modo_clasificacion' => $data['modo_clasificacion'] ?? 'cnn',
                    'lat_cliente' => $data['lat_cliente'] ?? null,
                    'lon_cliente' => $data['lon_cliente'] ?? null,
                ], static fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException $exception) {
            Log::error('No se pudo contactar con la API de busqueda inteligente.', [
                'url' => $url,
                'message' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('No se pudo conectar con la API de busqueda inteligente.');
        }

        if (! $response->successful()) {
            Log::warning('La API de busqueda inteligente respondio con error.', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $json = $response->json();

            throw new \RuntimeException(
                is_array($json)
                    ? ($json['detalle'] ?? $json['error'] ?? 'No se pudo consultar la busqueda inteligente.')
                    : 'No se pudo consultar la busqueda inteligente.'
            );
        }

        $json = $response->json();

        if (! is_array($json) || isset($json['error'])) {
            throw new \RuntimeException($json['detalle'] ?? $json['error'] ?? 'La API de busqueda inteligente devolvio una respuesta invalida.');
        }

        return $json;
    }

    protected function terminosBusquedaInteligente(array $respuestaIa, string $textoProblema): array
    {
        $palabrasClave = $respuestaIa['palabras_clave'] ?? [];
        $terminos = collect([
            $respuestaIa['tipo_dispositivo'] ?? null,
            $respuestaIa['marca'] ?? null,
        ]);

        foreach (['problemas', 'componentes'] as $clave) {
            $terminos = $terminos->merge($palabrasClave[$clave] ?? []);
        }

        $terminos = $terminos
            ->merge(Str::of($textoProblema)->lower()->explode(' '))
            ->map(fn ($termino) => $this->normalizarTexto($termino))
            ->filter(fn ($termino) => mb_strlen($termino) >= 3)
            ->unique()
            ->values()
            ->all();

        return $terminos;
    }

    protected function scoreProveedorInteligente(PerfilProveedor $perfilProveedor, array $terminos, $nombresExternos, array $data): array
    {
        $textoProveedor = $this->normalizarTexto(collect([
            $perfilProveedor->nombre_publico,
            $perfilProveedor->descripcion,
            $perfilProveedor->user?->name,
            $perfilProveedor->ubicacion?->zona,
            $perfilProveedor->ubicacion?->direccion,
        ])
            ->merge($perfilProveedor->proveedorEspecialidades->map(function ($proveedorEspecialidad) {
                $especialidad = $proveedorEspecialidad->especialidad;

                return implode(' ', [
                    $especialidad?->nombre,
                    $especialidad?->descripcion,
                    $especialidad?->rubroTipoServicio?->rubro?->nombre,
                    $especialidad?->rubroTipoServicio?->tipoServicio?->nombre,
                ]);
            }))
            ->filter()
            ->implode(' '));

        $coincidencias = collect($terminos)
            ->filter(fn ($termino) => Str::contains($textoProveedor, $termino))
            ->values();

        $score = min(55, $coincidencias->count() * 9);
        $razones = $coincidencias
            ->take(4)
            ->map(fn ($termino) => 'Coincide con "' . $termino . '"')
            ->all();

        $nombreNormalizado = $this->normalizarTexto($perfilProveedor->user?->name . ' ' . $perfilProveedor->nombre_publico);

        if ($nombresExternos->contains(fn ($nombre) => Str::contains($nombreNormalizado, $nombre) || Str::contains($nombre, $nombreNormalizado))) {
            $score += 25;
            $razones[] = 'Coincide con el ranking externo';
        }

        $rating = $this->promedioCalificacionProveedor($perfilProveedor);
        $score += $rating > 0 ? min(10, $rating * 2) : 3;

        $distancia = null;
        if (
            (bool) ($data['usar_ubicacion'] ?? false)
            && is_numeric($data['lat_cliente'] ?? null)
            && is_numeric($data['lon_cliente'] ?? null)
            && $perfilProveedor->ubicacion?->latitud
            && $perfilProveedor->ubicacion?->longitud
        ) {
            $distancia = $this->calcularDistanciaKm(
                (float) $data['lat_cliente'],
                (float) $data['lon_cliente'],
                (float) $perfilProveedor->ubicacion->latitud,
                (float) $perfilProveedor->ubicacion->longitud
            );

            if ($distancia <= (int) ($perfilProveedor->ubicacion->radio_cobertura_km ?? 1)) {
                $score += 10;
                $razones[] = 'Esta dentro de su radio de cobertura';
            } elseif ($distancia <= 5) {
                $score += 5;
                $razones[] = 'Esta cerca de tu ubicacion';
            }
        }

        return [
            'total' => round($score, 2),
            'razones' => array_values(array_unique($razones)),
            'distancia' => $distancia,
        ];
    }

    protected function promedioCalificacionProveedor(PerfilProveedor $perfilProveedor): float
    {
        $calificaciones = $perfilProveedor->solicitudes
            ->map(fn ($solicitud) => $solicitud->cita?->calificacion?->puntuacion)
            ->filter();

        return $calificaciones->isEmpty()
            ? 0.0
            : round($calificaciones->avg(), 1);
    }

    protected function calcularDistanciaKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $radioTierra = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return round($radioTierra * (2 * atan2(sqrt($a), sqrt(1 - $a))), 2);
    }

    protected function normalizarTexto(?string $texto): string
    {
        return Str::of($texto ?? '')
            ->ascii()
            ->lower()
            ->squish()
            ->toString();
    }

    protected function tieneFiltroUbicacionActual(array $filtros): bool
    {
        return $filtros['usar_ubicacion_actual']
            && is_numeric($filtros['latitud'])
            && is_numeric($filtros['longitud']);
    }

    protected function mapearProveedor(PerfilProveedor $perfilProveedor): array
    {
        $especialidadPrincipal = $this->especialidadPrincipal($perfilProveedor);
        $horarios = $perfilProveedor->horarios
            ->map(fn ($horario) => [
                'dia' => $this->nombreDia((int) $horario->dia_semana),
                'dia_semana' => (int) $horario->dia_semana,
                'hora_inicio' => $horario->hora_inicio?->format('H:i') ?? '--:--',
                'hora_fin' => $horario->hora_fin?->format('H:i') ?? '--:--',
                'tipo_atencion' => ucfirst($horario->tipo_atencion),
                'disponible' => $horario->disponible,
            ])
            ->values();

        return [
            'id' => $perfilProveedor->id,
            'nombre_persona' => $perfilProveedor->user?->name ?? 'Proveedor',
            'foto_personal' => $perfilProveedor->user?->avatar_url,
            'nombre_publico' => $perfilProveedor->nombre_publico,
            'descripcion' => $perfilProveedor->descripcion ?: 'Proveedor de servicios disponible para atender solicitudes.',
            'foto_portada' => $perfilProveedor->foto_portada,
            'anios_experiencia' => $perfilProveedor->anios_experiencia,
            'zona' => $perfilProveedor->ubicacion?->zona ?? 'Sin zona registrada',
            'direccion' => $perfilProveedor->ubicacion?->direccion ?? 'Sin direccion registrada',
            'radio_cobertura_km' => $perfilProveedor->ubicacion?->radio_cobertura_km,
            'latitud' => $perfilProveedor->ubicacion?->latitud,
            'longitud' => $perfilProveedor->ubicacion?->longitud,
            'especialidad' => $especialidadPrincipal?->nombre ?? 'Sin especialidad principal',
            'especialidad_id' => $especialidadPrincipal?->id,
            'categoria' => $especialidadPrincipal?->rubroTipoServicio?->rubro?->nombre ?? 'Sin rubro',
            'tipo_servicio' => $especialidadPrincipal?->rubroTipoServicio?->tipoServicio?->nombre ?? 'Sin tipo',
            'especialidades' => $perfilProveedor->proveedorEspecialidades
                ->where('estado', true)
                ->map(function ($proveedorEspecialidad) {
                    $especialidad = $proveedorEspecialidad->especialidad;

                    return [
                        'id' => $especialidad?->id,
                        'nombre' => $especialidad?->nombre,
                        'descripcion' => $especialidad?->descripcion,
                        'rubro' => $especialidad?->rubroTipoServicio?->rubro?->nombre,
                        'tipo_servicio' => $especialidad?->rubroTipoServicio?->tipoServicio?->nombre,
                        'es_principal' => $proveedorEspecialidad->es_principal,
                    ];
                })
                ->filter(fn ($especialidad) => filled($especialidad['nombre']))
                ->unique('id')
                ->values(),
            'horarios' => $horarios,
            'resumen_horario' => $this->resumenHorario($horarios),
            'portafolio' => $perfilProveedor->portafolio
                ->map(fn ($trabajo) => [
                    'titulo' => $trabajo->titulo,
                    'descripcion' => $trabajo->descripcion,
                    'imagen' => $trabajo->imagenes->first()?->imagen,
                    'fecha_trabajo' => $trabajo->fecha_trabajo?->format('d/m/Y'),
                ])
                ->values(),
            'documentos' => $perfilProveedor->documentos
                ->map(fn ($documento) => [
                    'nombre' => $documento->tipoDocumentoProveedor?->nombre ?? 'Documento',
                    'descripcion' => $documento->tipoDocumentoProveedor?->descripcion,
                    'obligatorio' => (bool) $documento->tipoDocumentoProveedor?->obligatorio,
                    'fecha_revision' => $documento->fecha_revision?->format('d/m/Y'),
                ])
                ->values(),
        ];
    }

    protected function especialidadPrincipal(PerfilProveedor $perfilProveedor): ?Especialidad
    {
        return $perfilProveedor->proveedorEspecialidades
            ->where('estado', true)
            ->sortByDesc('es_principal')
            ->first()?->especialidad;
    }

    protected function resumenHorario($horarios): array
    {
        $ahora = now('America/La_Paz');
        $diaActual = (int) $ahora->dayOfWeekIso;
        $horaActual = $ahora->format('H:i');
        $horariosDisponibles = $horarios->where('disponible', true);
        $horarioActual = $horariosDisponibles
            ->where('dia_semana', $diaActual)
            ->first(fn ($horario) => $horaActual >= $horario['hora_inicio'] && $horaActual <= $horario['hora_fin']);
        $proximoHorario = $horariosDisponibles
            ->sortBy(fn ($horario) => (($horario['dia_semana'] - $diaActual + 7) % 7) . $horario['hora_inicio'])
            ->first();

        return [
            'disponible_ahora' => filled($horarioActual),
            'estado_texto' => filled($horarioActual) ? 'Disponible ahora' : 'No disponible ahora',
            'estado_ayuda' => filled($horarioActual) ? 'Puede atender solicitudes' : 'Puedes enviar tu solicitud para revision',
            'proximo_dia' => $proximoHorario['dia'] ?? 'Por confirmar',
            'proximo_horario' => $proximoHorario ? $proximoHorario['hora_inicio'] . ' - ' . $proximoHorario['hora_fin'] : 'Sin horario',
            'total_disponibles' => $horariosDisponibles->count(),
            'total_horarios' => $horarios->count(),
        ];
    }

    public function tiposAtencion(): array
    {
        return [
            'mixto' => 'Mixto',
            'domicilio' => 'Domicilio',
            'local' => 'En local',
            'remoto' => 'Remoto',
        ];
    }

    protected function nombreDia(int $dia): string
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
        ][$dia] ?? 'Dia no definido';
    }
}
