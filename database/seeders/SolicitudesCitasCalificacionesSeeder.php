<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolicitudesCitasCalificacionesSeeder extends Seeder
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
            ->select('id', 'nombre_publico')
            ->where('user_id', $proveedor->id)
            ->first();

        if (! $perfilProveedor) {
            throw new \RuntimeException('El proveedor no tiene perfil registrado.');
        }

        $especialidades = DB::table('proveedor_especialidad as pe')
            ->join('especialidades as e', 'e.id', '=', 'pe.especialidad_id')
            ->where('pe.perfil_proveedor_id', $perfilProveedor->id)
            ->where('pe.estado', true)
            ->orderByDesc('pe.es_principal')
            ->orderBy('pe.especialidad_id')
            ->get([
                'pe.especialidad_id as id',
                'e.nombre',
            ]);

        if ($especialidades->isEmpty()) {
            throw new \RuntimeException('El proveedor no tiene especialidades activas.');
        }

        $zonas = [
            ['zona' => 'Sopocachi', 'direccion' => 'Av. 20 de Octubre #1540', 'lat' => -16.5098123, 'lng' => -68.1294412],
            ['zona' => 'Miraflores', 'direccion' => 'Calle Guerrilleros Lanza #845', 'lat' => -16.4987311, 'lng' => -68.1205532],
            ['zona' => 'Obrajes', 'direccion' => 'Av. Hernando Siles #2310', 'lat' => -16.5328142, 'lng' => -68.0879324],
            ['zona' => 'San Miguel', 'direccion' => 'Calle 21 de Calacoto #88', 'lat' => -16.5412014, 'lng' => -68.0771054],
            ['zona' => 'Achumani', 'direccion' => 'Av. Garcia Lanza #640', 'lat' => -16.5409112, 'lng' => -68.0620345],
            ['zona' => 'Tembladerani', 'direccion' => 'Calle Jose Saravia #415', 'lat' => -16.5167234, 'lng' => -68.1400855],
            ['zona' => 'Villa Fatima', 'direccion' => 'Av. Las Americas #121', 'lat' => -16.4872101, 'lng' => -68.1409204],
        ];

        $tiposAtencion = ['domicilio', 'local', 'remoto', 'mixto'];
        $comentarios = [
            'La atencion fue ordenada y el problema quedo resuelto el mismo dia.',
            'Muy buena explicacion tecnica y puntualidad en la visita.',
            'Quedo funcionando correctamente y se noto experiencia durante la revision.',
            'El proveedor fue claro con el diagnostico y dejo recomendaciones utiles.',
            'Buen servicio, buena comunicacion y seguimiento despues del trabajo.',
            'La reparacion quedo bien y el equipo volvio a operar con normalidad.',
        ];

        $observacionesSolicitud = [
            'Cliente solicita diagnostico y propuesta de solucion.',
            'Se requiere confirmacion de repuestos antes de la visita.',
            'El cliente pide prioridad porque usa el equipo para estudio.',
            'Se coordino evaluacion completa del equipo y pruebas funcionales.',
            'Se espera validacion final del presupuesto luego de la revision.',
        ];

        $observacionesCita = [
            'Se coordino visita tecnica con herramientas y repuestos basicos.',
            'Se realizo seguimiento previo para confirmar horario y ubicacion.',
            'Se reservo bloque de atencion para pruebas y validacion final.',
            'Se confirmo disponibilidad del proveedor para la fecha asignada.',
            'Se registro el servicio para revision detallada y entrega de observaciones.',
        ];

        $estadosPlan = array_merge(
            array_fill(0, 18, ['solicitud' => 'finalizada', 'cita' => 'completada', 'calificacion' => true]),
            array_fill(0, 5, ['solicitud' => 'aceptada', 'cita' => 'programada', 'calificacion' => false]),
            array_fill(0, 3, ['solicitud' => 'aceptada', 'cita' => 'reprogramada', 'calificacion' => false]),
            array_fill(0, 3, ['solicitud' => 'en_proceso', 'cita' => 'en_atencion', 'calificacion' => false]),
            array_fill(0, 2, ['solicitud' => 'pendiente', 'cita' => null, 'calificacion' => false]),
            array_fill(0, 2, ['solicitud' => 'rechazada', 'cita' => null, 'calificacion' => false]),
            array_fill(0, 2, ['solicitud' => 'cancelada', 'cita' => 'cancelada', 'calificacion' => false])
        );

        DB::transaction(function () use (
            $cliente,
            $perfilProveedor,
            $especialidades,
            $zonas,
            $tiposAtencion,
            $comentarios,
            $observacionesSolicitud,
            $observacionesCita,
            $estadosPlan
        ) {
            $solicitudIds = DB::table('solicitudes')
                ->where('cliente_user_id', $cliente->id)
                ->where('perfil_proveedor_id', $perfilProveedor->id)
                ->pluck('id');

            if ($solicitudIds->isNotEmpty()) {
                $citaIds = DB::table('citas')
                    ->whereIn('solicitud_id', $solicitudIds)
                    ->pluck('id');

                if ($citaIds->isNotEmpty()) {
                    DB::table('calificaciones')->whereIn('cita_id', $citaIds)->delete();
                    DB::table('citas')->whereIn('id', $citaIds)->delete();
                }

                DB::table('solicitudes')->whereIn('id', $solicitudIds)->delete();
            }

            $now = now();

            for ($i = 0; $i < 35; $i++) {
                $estado = $estadosPlan[$i];
                $especialidad = $especialidades[$i % $especialidades->count()];
                $ubicacion = $zonas[$i % count($zonas)];
                $tipoAtencion = $tiposAtencion[$i % count($tiposAtencion)];

                [$titulo, $descripcion] = $this->contenidoPorEspecialidad($especialidad->nombre, $i + 1);

                $fechaBase = match ($estado['solicitud']) {
                    'finalizada' => Carbon::now()->subDays(50 - $i),
                    'aceptada' => Carbon::now()->addDays(($i % 6) + 2),
                    'en_proceso' => Carbon::now()->subDays(($i % 3) + 1),
                    'pendiente' => Carbon::now()->addDays(($i % 4) + 1),
                    'rechazada' => Carbon::now()->subDays(($i % 6) + 5),
                    'cancelada' => Carbon::now()->subDays(($i % 4) + 2),
                    default => Carbon::now(),
                };

                $horaBase = Carbon::createFromTime(8 + ($i % 8), ($i % 2) * 30, 0);

                $solicitudId = DB::table('solicitudes')->insertGetId([
                    'cliente_user_id' => $cliente->id,
                    'perfil_proveedor_id' => $perfilProveedor->id,
                    'especialidad_id' => $especialidad->id,
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'tipo_atencion' => $tipoAtencion,
                    'direccion' => $tipoAtencion === 'remoto' ? null : $ubicacion['direccion'],
                    'zona' => $tipoAtencion === 'remoto' ? null : $ubicacion['zona'],
                    'latitud' => $tipoAtencion === 'remoto' ? null : $ubicacion['lat'],
                    'longitud' => $tipoAtencion === 'remoto' ? null : $ubicacion['lng'],
                    'fecha_solicitada' => $fechaBase->toDateString(),
                    'hora_solicitada' => $horaBase->format('H:i:s'),
                    'estado' => $estado['solicitud'],
                    'motivo_cancelacion' => $estado['solicitud'] === 'cancelada' ? 'El cliente reprogramo la necesidad del servicio por temas de agenda.' : null,
                    'observaciones' => $observacionesSolicitud[$i % count($observacionesSolicitud)],
                    'created_at' => $fechaBase->copy()->subHours(4),
                    'updated_at' => $fechaBase->copy()->subHours(2),
                ]);

                if ($estado['cita']) {
                    $fechaCita = match ($estado['cita']) {
                        'completada' => $fechaBase->copy()->addDay(),
                        'programada' => $fechaBase->copy(),
                        'reprogramada' => $fechaBase->copy()->addDays(2),
                        'en_atencion' => Carbon::now()->toDateString() === $fechaBase->toDateString()
                            ? Carbon::now()
                            : $fechaBase->copy()->addDay(),
                        'cancelada' => $fechaBase->copy()->addDay(),
                        default => $fechaBase->copy()->addDay(),
                    };

                    $horaInicio = $horaBase->copy();
                    $horaFin = $horaBase->copy()->addHours(2);

                    $citaId = DB::table('citas')->insertGetId([
                        'solicitud_id' => $solicitudId,
                        'fecha_cita' => $fechaCita->toDateString(),
                        'hora_inicio' => $horaInicio->format('H:i:s'),
                        'hora_fin' => $horaFin->format('H:i:s'),
                        'estado' => $estado['cita'],
                        'observaciones' => $observacionesCita[$i % count($observacionesCita)],
                        'created_at' => $fechaCita->copy()->subHours(3),
                        'updated_at' => $fechaCita->copy()->subHour(),
                    ]);

                    if ($estado['calificacion']) {
                        $puntuacion = [5, 5, 4, 5, 4, 5, 3, 4, 5, 4][($i % 10)];

                        DB::table('calificaciones')->insert([
                            'cita_id' => $citaId,
                            'puntuacion' => $puntuacion,
                            'comentario' => $comentarios[$i % count($comentarios)],
                            'estado' => $i % 7 === 0 ? 'pendiente_revision' : ($i % 5 === 0 ? 'oculta' : 'visible'),
                            'created_at' => $fechaCita->copy()->addHours(5),
                            'updated_at' => $fechaCita->copy()->addHours(6),
                        ]);
                    }
                }
            }
        });

        $this->command?->info('Se insertaron 35 solicitudes con citas y calificaciones variadas para el cliente y proveedor indicados.');
    }

    protected function contenidoPorEspecialidad(string $especialidad, int $indice): array
    {
        return match (mb_strtolower($especialidad)) {
            'celulares' => [
                "Revision tecnica de celular #{$indice}",
                'El equipo presenta fallas intermitentes en carga, temperatura elevada y cortes en el uso diario. Se requiere diagnostico, reparacion y validacion completa del funcionamiento.',
            ],
            'laptops' => [
                "Mantenimiento y reparacion de laptop #{$indice}",
                'La laptop enciende con lentitud, el ventilador trabaja con ruido y en algunos momentos se apaga. Se solicita revision de hardware, limpieza interna y pruebas de estabilidad.',
            ],
            'impresoras' => [
                "Servicio de impresora #{$indice}",
                'La impresora muestra atascos frecuentes, alimenta mal el papel y presenta errores al momento de imprimir. Se pide revision general, mantenimiento y ajuste del sistema de arrastre.',
            ],
            'monitores' => [
                "Diagnostico de monitor #{$indice}",
                'El monitor presenta parpadeos, perdida de brillo y en ocasiones no recibe imagen desde el equipo principal. Se requiere evaluacion tecnica y solucion del problema.',
            ],
            'computadoras de escritorio' => [
                "Soporte para PC de escritorio #{$indice}",
                'La computadora de escritorio presenta reinicios inesperados, ruido en el gabinete y bajo rendimiento al abrir programas. Se necesita diagnostico, mantenimiento y optimizacion.',
            ],
            default => [
                "Solicitud tecnica #{$indice}",
                'Se requiere una revision tecnica completa del equipo, identificacion de la falla principal y una propuesta clara de solucion con seguimiento de resultados.',
            ],
        };
    }
}
