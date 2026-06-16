<?php

namespace App\Services\Cliente;

use App\Models\Especialidad;
use App\Models\PerfilProveedor;
use App\Models\Rubro;
use App\Models\RubroTipoServicio;
use Illuminate\Pagination\LengthAwarePaginator;
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
            'foto_personal' => $perfilProveedor->user?->avatar,
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

    protected function tiposAtencion(): array
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
