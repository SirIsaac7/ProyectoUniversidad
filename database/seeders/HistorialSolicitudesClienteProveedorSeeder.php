<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HistorialSolicitudesClienteProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $clienteEmail = 'lpze.isaacalejandro.tola.to@unifranz.edu.bo';
        $proveedorEmail = 'oblitasjosemiguel4@gmail.com';

        $cliente = DB::table('users')
            ->select('id', 'name', 'email')
            ->where('email', $clienteEmail)
            ->first();

        $proveedor = DB::table('users')
            ->select('id', 'name', 'email')
            ->where('email', $proveedorEmail)
            ->first();

        if (! $cliente) {
            throw new \RuntimeException("No se encontro el cliente {$clienteEmail}.");
        }

        if (! $proveedor) {
            throw new \RuntimeException("No se encontro el proveedor {$proveedorEmail}.");
        }

        $perfilProveedor = DB::table('perfiles_proveedores')
            ->select('id', 'user_id', 'nombre_publico')
            ->where('user_id', $proveedor->id)
            ->first();

        if (! $perfilProveedor) {
            throw new \RuntimeException('El proveedor no tiene perfil registrado.');
        }

        $solicitudes = DB::table('solicitudes as s')
            ->leftJoin('citas as c', 'c.solicitud_id', '=', 's.id')
            ->leftJoin('calificaciones as cal', 'cal.cita_id', '=', 'c.id')
            ->where('s.cliente_user_id', $cliente->id)
            ->where('s.perfil_proveedor_id', $perfilProveedor->id)
            ->orderBy('s.created_at')
            ->orderBy('s.id')
            ->get([
                's.id',
                's.estado',
                's.titulo',
                's.motivo_cancelacion',
                's.observaciones',
                's.created_at as solicitud_created_at',
                's.updated_at as solicitud_updated_at',
                'c.id as cita_id',
                'c.estado as cita_estado',
                'c.observaciones as cita_observaciones',
                'c.fecha_cita',
                'c.created_at as cita_created_at',
                'c.updated_at as cita_updated_at',
                'cal.id as calificacion_id',
                'cal.created_at as calificacion_created_at',
            ]);

        if ($solicitudes->isEmpty()) {
            throw new \RuntimeException('No se encontraron solicitudes para la relacion cliente-proveedor indicada.');
        }

        DB::transaction(function () use ($solicitudes, $cliente, $proveedor) {
            DB::table('historial_solicitudes')
                ->whereIn('solicitud_id', $solicitudes->pluck('id'))
                ->delete();

            $rows = [];

            foreach ($solicitudes as $solicitud) {
                $rows = array_merge(
                    $rows,
                    $this->historialParaSolicitud($solicitud, $cliente->id, $proveedor->id)
                );
            }

            if (! empty($rows)) {
                DB::table('historial_solicitudes')->insert($rows);
            }
        });

        $this->command?->info('Se registro el historial de solicitudes, citas y cierres de atencion para el cliente y proveedor indicados.');
    }

    private function historialParaSolicitud(object $solicitud, int $clienteId, int $proveedorId): array
    {
        $base = Carbon::parse($solicitud->solicitud_created_at ?? now())->startOfMinute();
        $citaBase = $solicitud->cita_created_at
            ? Carbon::parse($solicitud->cita_created_at)->startOfMinute()
            : $base->copy()->addHours(3);
        $cierreBase = $solicitud->cita_updated_at
            ? Carbon::parse($solicitud->cita_updated_at)->startOfMinute()
            : $base->copy()->addDay();
        $calificacionBase = $solicitud->calificacion_created_at
            ? Carbon::parse($solicitud->calificacion_created_at)->startOfMinute()
            : $cierreBase->copy()->addHours(4);

        $rows = collect([
            $this->row(
                solicitudId: $solicitud->id,
                userId: $clienteId,
                estadoAnterior: null,
                estadoNuevo: 'pendiente',
                comentario: 'Solicitud creada por el cliente',
                timestamp: $base
            ),
        ]);

        switch ($solicitud->estado) {
            case 'pendiente':
                break;

            case 'rechazada':
                $rows->push(
                    $this->row(
                        solicitudId: $solicitud->id,
                        userId: $proveedorId,
                        estadoAnterior: 'pendiente',
                        estadoNuevo: 'rechazada',
                        comentario: $solicitud->motivo_cancelacion ?: 'Solicitud rechazada por el proveedor',
                        timestamp: $base->copy()->addHours(2)
                    )
                );
                break;

            case 'cancelada':
                if ($solicitud->cita_id) {
                    $rows = $rows->merge($this->flujoAceptacion($solicitud->id, $proveedorId, $citaBase));
                    $rows->push(
                        $this->row(
                            solicitudId: $solicitud->id,
                            userId: $clienteId,
                            estadoAnterior: 'aceptada',
                            estadoNuevo: 'cancelada',
                            comentario: $solicitud->motivo_cancelacion ?: 'Solicitud cancelada por el cliente',
                            timestamp: $cierreBase
                        )
                    );

                    if ($solicitud->cita_estado === 'cancelada') {
                        $rows->push(
                            $this->row(
                                solicitudId: $solicitud->id,
                                userId: $proveedorId,
                                estadoAnterior: 'cancelada',
                                estadoNuevo: 'cancelada',
                                comentario: $solicitud->cita_observaciones ?: 'Cita cancelada',
                                timestamp: $cierreBase->copy()->addMinutes(25)
                            )
                        );
                    }
                } else {
                    $rows->push(
                        $this->row(
                            solicitudId: $solicitud->id,
                            userId: $clienteId,
                            estadoAnterior: 'pendiente',
                            estadoNuevo: 'cancelada',
                            comentario: $solicitud->motivo_cancelacion ?: 'Solicitud cancelada',
                            timestamp: $base->copy()->addHours(3)
                        )
                    );
                }
                break;

            case 'aceptada':
                $rows = $rows->merge($this->flujoAceptacion($solicitud->id, $proveedorId, $citaBase));

                if ($solicitud->cita_estado === 'reprogramada') {
                    $rows->push(
                        $this->row(
                            solicitudId: $solicitud->id,
                            userId: $proveedorId,
                            estadoAnterior: 'aceptada',
                            estadoNuevo: 'aceptada',
                            comentario: 'Estado de cita actualizado a reprogramada',
                            timestamp: $cierreBase
                        )
                    );
                }
                break;

            case 'en_proceso':
                $rows = $rows->merge($this->flujoAceptacion($solicitud->id, $proveedorId, $citaBase));
                $rows->push(
                    $this->row(
                        solicitudId: $solicitud->id,
                        userId: $proveedorId,
                        estadoAnterior: 'aceptada',
                        estadoNuevo: 'en_proceso',
                        comentario: $solicitud->cita_observaciones ?: 'Atencion iniciada por el proveedor',
                        timestamp: $cierreBase
                    )
                );
                break;

            case 'finalizada':
                $rows = $rows->merge($this->flujoAceptacion($solicitud->id, $proveedorId, $citaBase));
                $rows->push(
                    $this->row(
                        solicitudId: $solicitud->id,
                        userId: $proveedorId,
                        estadoAnterior: 'aceptada',
                        estadoNuevo: 'en_proceso',
                        comentario: 'Atencion iniciada por el proveedor',
                        timestamp: $cierreBase->copy()->subHours(2)
                    )
                );
                $rows->push(
                    $this->row(
                        solicitudId: $solicitud->id,
                        userId: $proveedorId,
                        estadoAnterior: 'en_proceso',
                        estadoNuevo: 'finalizada',
                        comentario: $solicitud->cita_observaciones ?: 'Servicio completado correctamente.',
                        timestamp: $cierreBase
                    )
                );

                if ($solicitud->calificacion_id) {
                    $rows->push(
                        $this->row(
                            solicitudId: $solicitud->id,
                            userId: $clienteId,
                            estadoAnterior: 'finalizada',
                            estadoNuevo: 'finalizada',
                            comentario: 'Calificacion registrada por el cliente',
                            timestamp: $calificacionBase
                        )
                    );
                }
                break;
        }

        return $rows
            ->sortBy(fn (array $row) => $row['created_at'])
            ->values()
            ->all();
    }

    private function flujoAceptacion(int $solicitudId, int $proveedorId, Carbon $timestamp): Collection
    {
        return collect([
            $this->row(
                solicitudId: $solicitudId,
                userId: $proveedorId,
                estadoAnterior: 'pendiente',
                estadoNuevo: 'aceptada',
                comentario: 'Solicitud aceptada al programar una cita',
                timestamp: $timestamp->copy()->subMinutes(35)
            ),
            $this->row(
                solicitudId: $solicitudId,
                userId: $proveedorId,
                estadoAnterior: 'aceptada',
                estadoNuevo: 'aceptada',
                comentario: 'Cita programada por el proveedor',
                timestamp: $timestamp
            ),
        ]);
    }

    private function row(
        int $solicitudId,
        int $userId,
        ?string $estadoAnterior,
        string $estadoNuevo,
        string $comentario,
        Carbon $timestamp
    ): array {
        return [
            'solicitud_id' => $solicitudId,
            'user_id' => $userId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'comentario' => $comentario,
            'created_at' => $timestamp->copy(),
            'updated_at' => $timestamp->copy(),
        ];
    }
}
