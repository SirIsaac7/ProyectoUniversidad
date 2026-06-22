<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Cita;
use App\Models\DetalleCalificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CalificacionService
{
    public function __construct(
        protected NotificacionService $notificacionService
    ) {
    }

    public function calificacionesAdmin()
    {
        return Calificacion::query()
            ->with([
                'cita.solicitud.cliente',
                'cita.solicitud.perfilProveedor',
                'cita.solicitud.especialidad',
                'detalles.aspecto',
                'respuesta.user',
            ])
            ->latest()
            ->paginate(15);
    }

    public function calificacionesCliente(User $cliente)
    {
        return Calificacion::query()
            ->with([
                'cita.solicitud.perfilProveedor',
                'cita.solicitud.especialidad.rubroTipoServicio.rubro',
                'cita.solicitud.especialidad.rubroTipoServicio.tipoServicio',
                'detalles.aspecto',
                'respuesta',
            ])
            ->whereHas('cita.solicitud', fn ($query) => $query->where('cliente_user_id', $cliente->id))
            ->latest()
            ->paginate(10);
    }

    public function calificacionesProveedor(User $proveedor)
    {
        $perfilProveedor = $proveedor->perfilProveedor;

        return Calificacion::query()
            ->with([
                'cita.solicitud.cliente',
                'cita.solicitud.especialidad.rubroTipoServicio.rubro',
                'cita.solicitud.especialidad.rubroTipoServicio.tipoServicio',
                'detalles.aspecto',
                'respuesta.user',
            ])
            ->when($perfilProveedor, function ($query) use ($perfilProveedor) {
                $query->whereHas('cita.solicitud', fn ($subquery) => $subquery->where('perfil_proveedor_id', $perfilProveedor->id));
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->paginate(10);
    }

    public function citasCompletadasSinCalificar(User $cliente)
    {
        return Cita::query()
            ->with([
                'solicitud.perfilProveedor',
                'solicitud.especialidad.rubroTipoServicio.rubro',
                'solicitud.especialidad.rubroTipoServicio.tipoServicio',
            ])
            ->where('estado', 'completada')
            ->whereDoesntHave('calificacion')
            ->whereHas('solicitud', fn ($query) => $query->where('cliente_user_id', $cliente->id))
            ->latest()
            ->get();
    }

    public function resumenAdmin(): array
    {
        return $this->resumenDesdeConsulta(Calificacion::query());
    }

    public function resumenCliente(User $cliente): array
    {
        return $this->resumenDesdeConsulta(
            Calificacion::query()
                ->whereHas('cita.solicitud', fn ($query) => $query->where('cliente_user_id', $cliente->id))
        );
    }

    public function resumenProveedor(User $proveedor): array
    {
        $perfilProveedor = $proveedor->perfilProveedor;

        if (! $perfilProveedor) {
            return $this->resumenVacio();
        }

        return $this->resumenDesdeConsulta(
            Calificacion::query()
                ->whereHas('cita.solicitud', fn ($query) => $query->where('perfil_proveedor_id', $perfilProveedor->id))
        );
    }

    public function respuestasPendientesProveedor(User $proveedor)
    {
        $perfilProveedor = $proveedor->perfilProveedor;

        return Calificacion::query()
            ->with(['cita.solicitud.cliente', 'cita.solicitud.especialidad'])
            ->whereDoesntHave('respuesta')
            ->when($perfilProveedor, function ($query) use ($perfilProveedor) {
                $query->whereHas('cita.solicitud', fn ($subquery) => $subquery->where('perfil_proveedor_id', $perfilProveedor->id));
            }, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->limit(5)
            ->get();
    }

    public function createDesdeCliente(User $cliente, array $data): Calificacion
    {
        return DB::transaction(function () use ($cliente, $data) {
            $cita = Cita::query()
                ->with(['solicitud.cliente', 'solicitud.perfilProveedor.user'])
                ->whereKey($data['cita_id'])
                ->where('estado', 'completada')
                ->whereDoesntHave('calificacion')
                ->whereHas('solicitud', fn ($query) => $query->where('cliente_user_id', $cliente->id))
                ->firstOrFail();

            $calificacion = Calificacion::create([
                'cita_id' => $cita->id,
                'puntuacion' => $data['puntuacion'],
                'comentario' => $data['comentario'] ?? null,
                'estado' => 'visible',
            ]);

            foreach ($data['aspectos'] ?? [] as $aspectoId => $puntuacion) {
                $calificacion->detalles()->create([
                    'aspecto_calificacion_id' => $aspectoId,
                    'puntuacion' => $puntuacion,
                ]);
            }

            $proveedor = $cita->solicitud?->perfilProveedor?->user;

            if ($proveedor) {
                $this->notificacionService->calificacionRecibidaParaProveedor(
                    proveedor: $proveedor,
                    cliente: $cliente->name ?? 'Un cliente',
                    url: route('proveedor.calificaciones.index')
                );
            }

            return $calificacion->load(['cita.solicitud', 'detalles.aspecto']);
        });
    }

    public function actualizarEstado(Calificacion $calificacion, string $estado): Calificacion
    {
        $calificacion->update([
            'estado' => $estado,
        ]);

        return $calificacion;
    }

    public function eliminar(Calificacion $calificacion): void
    {
        $calificacion->delete();
    }

    protected function resumenDesdeConsulta(Builder $query): array
    {
        $total = (clone $query)->count();

        if ($total === 0) {
            return $this->resumenVacio();
        }

        $promedio = round((clone $query)->avg('puntuacion'), 1);
        $positivas = (clone $query)->whereIn('puntuacion', [4, 5])->count();
        $respuestas = (clone $query)->whereHas('respuesta')->count();
        $clientesUnicos = (clone $query)
            ->with('cita.solicitud')
            ->get()
            ->pluck('cita.solicitud.cliente_user_id')
            ->filter()
            ->unique()
            ->count();
        $conteos = [];

        foreach ([5, 4, 3, 2, 1] as $estrella) {
            $cantidad = (clone $query)->where('puntuacion', $estrella)->count();
            $conteos[$estrella] = [
                'cantidad' => $cantidad,
                'porcentaje' => $total > 0 ? round(($cantidad / $total) * 100) : 0,
            ];
        }

        return [
            'total' => $total,
            'promedio' => $promedio,
            'positivas' => [
                'cantidad' => $positivas,
                'porcentaje' => round(($positivas / $total) * 100, 1),
            ],
            'respuestas' => [
                'cantidad' => $respuestas,
                'porcentaje' => round(($respuestas / $total) * 100, 1),
            ],
            'clientes_unicos' => $clientesUnicos,
            'conteos' => $conteos,
            'aspectos' => $this->resumenAspectosDesdeConsulta($query),
        ];
    }

    protected function resumenVacio(): array
    {
        return [
            'total' => 0,
            'promedio' => 0,
            'positivas' => [
                'cantidad' => 0,
                'porcentaje' => 0,
            ],
            'respuestas' => [
                'cantidad' => 0,
                'porcentaje' => 0,
            ],
            'clientes_unicos' => 0,
            'conteos' => collect([5, 4, 3, 2, 1])
                ->mapWithKeys(fn ($estrella) => [$estrella => ['cantidad' => 0, 'porcentaje' => 0]])
                ->all(),
            'aspectos' => [
                'labels' => [],
                'series' => [],
                'total' => 0,
            ],
        ];
    }

    protected function resumenAspectosDesdeConsulta(Builder $query): array
    {
        $calificacionIds = (clone $query)->pluck('id');

        if ($calificacionIds->isEmpty()) {
            return [
                'labels' => [],
                'series' => [],
                'total' => 0,
            ];
        }

        $aspectos = DetalleCalificacion::query()
            ->select('aspectos_calificacion.nombre', DB::raw('count(*) as total'))
            ->join('aspectos_calificacion', 'aspectos_calificacion.id', '=', 'detalle_calificaciones.aspecto_calificacion_id')
            ->whereIn('detalle_calificaciones.calificacion_id', $calificacionIds)
            ->groupBy('aspectos_calificacion.id', 'aspectos_calificacion.nombre')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $aspectos->pluck('nombre')->values()->all(),
            'series' => $aspectos->pluck('total')->map(fn ($total) => (int) $total)->values()->all(),
            'total' => (int) $aspectos->sum('total'),
        ];
    }
}
