<?php

namespace Database\Seeders;

use App\Models\AspectoCalificacion;
use App\Models\Calificacion;
use App\Models\Cita;
use App\Models\DetalleCalificacion;
use App\Models\HistorialSolicitud;
use App\Models\HorarioProveedor;
use App\Models\PerfilProveedor;
use App\Models\PortafolioProveedor;
use App\Models\ProveedorEspecialidad;
use App\Models\Reporte;
use App\Models\RespuestaCalificacion;
use App\Models\Role;
use App\Models\Solicitud;
use App\Models\User;
use App\Models\UbicacionProveedor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatosSimuladosLaPazSeeder extends Seeder
{
    private Carbon $ahora;

    public function run(): void
    {
        $this->ahora = now();

        Model::withoutEvents(function () {
            DB::transaction(function () {
                $roles = $this->asegurarRoles();
                $zonas = $this->zonasLaPaz();
                $especialidades = $this->especialidadesActivas();
                $aspectos = AspectoCalificacion::query()
                    ->where('estado', true)
                    ->orderBy('orden')
                    ->get();

                if ($especialidades->isEmpty()) {
                    $this->command?->warn('No existen especialidades activas. Crea rubros, tipos de servicio y especialidades antes de ejecutar este seeder.');
                    return;
                }

                if ($aspectos->isEmpty()) {
                    $this->command?->warn('No existen aspectos de calificacion activos. Crea aspectos antes de ejecutar este seeder.');
                    return;
                }

                $admin = $this->crearAdminDemo($roles['admin']);
                $clientes = $this->crearClientes($roles['cliente']);
                $proveedores = $this->crearProveedores($roles['proveedor'], $zonas, $especialidades);

                $solicitudes = $this->crearSolicitudes($clientes, $proveedores, $especialidades, $zonas);
                $citasCompletadas = $this->crearCitas($solicitudes);
                $this->crearCalificaciones($citasCompletadas, $aspectos);
                $this->crearReportes($admin);

                $this->command?->info('Datos simulados de La Paz creados/actualizados correctamente.');
                $this->command?->warn('No se llenaron archivos fisicos de documentos ni imagenes de portafolio: esos campos requieren rutas reales a archivos existentes.');
            });
        });
    }

    private function asegurarRoles(): array
    {
        $admin = Role::firstOrCreate(['name' => 'ADMINISTRADOR', 'guard_name' => 'web']);
        $proveedor = Role::firstOrCreate(['name' => 'PROVEEDOR', 'guard_name' => 'web']);
        $cliente = Role::firstOrCreate(['name' => 'CLIENTE', 'guard_name' => 'web']);

        return compact('admin', 'proveedor', 'cliente');
    }

    private function crearAdminDemo(Role $rol): User
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin.demo.lapaz@gmail.com'],
            [
                'name' => 'Administracion TecnoConexion',
                'celular' => '60112001',
                'celular_verificado_at' => $this->ahora,
                'recibe_notificaciones_whatsapp' => true,
                'fecha_nacimiento' => '1992-03-14',
                'email_verified_at' => $this->ahora,
                'password' => Hash::make('Password123!'),
                'estado' => true,
            ]
        );

        $admin->syncRoles([$rol->name]);

        return $admin;
    }

    private function crearClientes(Role $rol): array
    {
        $nombres = [
            'Ana Gabriela Mamani', 'Luis Fernando Quispe', 'Mariana Flores', 'Diego Alvarez',
            'Carla Rocha', 'Rodrigo Choque', 'Valeria Paredes', 'Marco Antonio Gutierrez',
            'Paola Miranda', 'Javier Mendoza', 'Gabriela Salazar', 'Daniel Vargas',
            'Fernanda Rojas', 'Miguel Aruquipa', 'Natalia Caceres', 'Rene Copa',
            'Sofia Villca', 'Bruno Loayza', 'Andrea Apaza', 'Mateo Lima',
            'Daniela Ticona', 'Cristian Velasco', 'Lucia Teran', 'Samuel Condori',
            'Camila Bustillos', 'Pablo Heredia', 'Elena Aguilar', 'Victor Nina',
            'Adriana Soria', 'Hugo Ballivian',
        ];

        $clientes = [];

        foreach ($nombres as $index => $nombre) {
            $numero = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $cliente = User::updateOrCreate(
                ['email' => "cliente.lapaz{$numero}@gmail.com"],
                [
                    'name' => $nombre,
                    'celular' => '67' . str_pad((string) (200000 + $index * 137), 6, '0', STR_PAD_LEFT),
                    'celular_verificado_at' => $this->ahora->copy()->subDays($index % 9),
                    'recibe_notificaciones_whatsapp' => $index % 3 !== 0,
                    'fecha_nacimiento' => Carbon::create(1984 + ($index % 18), (($index % 12) + 1), (($index % 24) + 1))->toDateString(),
                    'email_verified_at' => $this->ahora,
                    'password' => Hash::make('Password123!'),
                    'estado' => true,
                ]
            );

            $cliente->syncRoles([$rol->name]);
            $clientes[] = $cliente;
        }

        return $clientes;
    }

    private function crearProveedores(Role $rol, array $zonas, $especialidades): array
    {
        $datos = [
            ['user' => 'Carlos Mendez', 'publico' => 'TecnoExpress Miraflores', 'zona' => 'Miraflores', 'exp' => 8, 'desc' => 'Reparacion de laptops, celulares y equipos de red con atencion en taller y domicilio.'],
            ['user' => 'Maria Gonzales', 'publico' => 'Soluciones Digitales Sopocachi', 'zona' => 'Sopocachi', 'exp' => 6, 'desc' => 'Soporte tecnico, configuracion de redes domesticas e instalacion de impresoras.'],
            ['user' => 'Luis Ramirez', 'publico' => 'Redes Seguras La Paz', 'zona' => 'San Pedro', 'exp' => 7, 'desc' => 'Instalacion de camaras de seguridad, puntos de red y conectividad para negocios.'],
            ['user' => 'Pedro Vargas', 'publico' => 'Laptop Center Obrajes', 'zona' => 'Obrajes', 'exp' => 9, 'desc' => 'Mantenimiento preventivo, cambio de SSD, memoria RAM y optimizacion de equipos.'],
            ['user' => 'Ana Torres', 'publico' => 'MovilFix Calacoto', 'zona' => 'Calacoto', 'exp' => 5, 'desc' => 'Diagnostico y reparacion de celulares, tablets y accesorios tecnologicos.'],
            ['user' => 'Ruben Alarcon', 'publico' => 'PC Doctor Achumani', 'zona' => 'Achumani', 'exp' => 10, 'desc' => 'Reparacion de computadoras de escritorio, fuentes, placas y sistemas operativos.'],
            ['user' => 'Claudia Herrera', 'publico' => 'Conecta Hogar Sur', 'zona' => 'Cota Cota', 'exp' => 4, 'desc' => 'Configuracion de routers, WiFi, repetidores y redes domesticas.'],
            ['user' => 'Oscar Salinas', 'publico' => 'Impresoras Pro Centro', 'zona' => 'Centro', 'exp' => 11, 'desc' => 'Mantenimiento y diagnostico de impresoras para hogares, oficinas y negocios.'],
            ['user' => 'Monica Poma', 'publico' => 'Smart TV y Consolas LP', 'zona' => 'San Miguel', 'exp' => 6, 'desc' => 'Soporte para Smart TV, consolas, monitores y configuracion multimedia.'],
            ['user' => 'Jorge Nina', 'publico' => 'Data Recovery La Paz', 'zona' => 'Villa Fatima', 'exp' => 12, 'desc' => 'Recuperacion de datos en discos duros, memorias USB, tarjetas SD y celulares.'],
            ['user' => 'Patricia Arias', 'publico' => 'InstalaSoft Bolivia', 'zona' => 'Tembladerani', 'exp' => 5, 'desc' => 'Instalacion de software, sistemas operativos, copias de seguridad y asistencia remota.'],
            ['user' => 'Fernando Callisaya', 'publico' => 'CamTech Periferica', 'zona' => 'Villa Copacabana', 'exp' => 7, 'desc' => 'Instalacion y diagnostico de camaras IP, DVR, NVR y redes cableadas.'],
        ];

        $proveedores = [];

        foreach ($datos as $index => $dato) {
            $numero = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $user = User::updateOrCreate(
                ['email' => "proveedor.lapaz{$numero}@gmail.com"],
                [
                    'name' => $dato['user'],
                    'celular' => '70' . str_pad((string) (310000 + $index * 149), 6, '0', STR_PAD_LEFT),
                    'celular_verificado_at' => $this->ahora->copy()->subDays($index % 7),
                    'recibe_notificaciones_whatsapp' => true,
                    'fecha_nacimiento' => Carbon::create(1978 + ($index % 16), (($index % 12) + 1), (($index % 20) + 2))->toDateString(),
                    'email_verified_at' => $this->ahora,
                    'password' => Hash::make('Password123!'),
                    'estado' => true,
                ]
            );

            $user->syncRoles([$rol->name]);

            $perfil = PerfilProveedor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre_publico' => $dato['publico'],
                    'descripcion' => $dato['desc'],
                    'foto_portada' => null,
                    'anios_experiencia' => $dato['exp'],
                    'estado_verificacion' => 'aprobado',
                    'motivo_rechazo' => null,
                    'estado' => true,
                ]
            );

            $zona = $this->buscarZona($zonas, $dato['zona'], $index);
            UbicacionProveedor::updateOrCreate(
                ['perfil_proveedor_id' => $perfil->id],
                [
                    'zona' => $zona['zona'],
                    'direccion' => $this->direccionParaZona($zona['zona'], $index),
                    'latitud' => $zona['latitud'],
                    'longitud' => $zona['longitud'],
                    'radio_cobertura_km' => ($index % 5) + 1,
                ]
            );

            $this->asignarEspecialidadesProveedor($perfil, $especialidades, $index);
            $this->crearHorariosProveedor($perfil, $index);
            $this->crearPortafolioProveedor($perfil, $index);

            $proveedores[] = $perfil->fresh(['user', 'ubicacion', 'proveedorEspecialidades.especialidad']);
        }

        return $proveedores;
    }

    private function asignarEspecialidadesProveedor(PerfilProveedor $perfil, $especialidades, int $index): void
    {
        $seleccionadas = $especialidades->slice(($index * 3) % max($especialidades->count(), 1), 3);

        if ($seleccionadas->count() < 3) {
            $seleccionadas = $seleccionadas->merge($especialidades->take(3 - $seleccionadas->count()));
        }

        foreach ($seleccionadas->values() as $posicion => $especialidad) {
            ProveedorEspecialidad::updateOrCreate(
                [
                    'perfil_proveedor_id' => $perfil->id,
                    'especialidad_id' => $especialidad->id,
                ],
                [
                    'es_principal' => $posicion === 0,
                    'estado' => true,
                ]
            );
        }
    }

    private function crearHorariosProveedor(PerfilProveedor $perfil, int $index): void
    {
        $rangos = [
            [['08:30', '12:30'], ['14:30', '18:30']],
            [['09:00', '13:00'], ['15:00', '19:00']],
            [['08:00', '12:00'], ['13:30', '17:30']],
        ];

        $tipoAtencion = ['domicilio', 'local', 'mixto', 'remoto'];
        $diasTrabajo = [1, 2, 3, 4, 5, $index % 2 === 0 ? 6 : 0];

        foreach ($diasTrabajo as $dia) {
            foreach ($rangos[$index % 3] as $rango) {
                HorarioProveedor::updateOrCreate(
                    [
                        'perfil_proveedor_id' => $perfil->id,
                        'dia_semana' => $dia,
                        'hora_inicio' => $rango[0],
                        'hora_fin' => $rango[1],
                    ],
                    [
                        'tipo_atencion' => $tipoAtencion[$index % count($tipoAtencion)],
                        'disponible' => true,
                        'estado' => true,
                    ]
                );
            }
        }
    }

    private function crearPortafolioProveedor(PerfilProveedor $perfil, int $index): void
    {
        $trabajos = [
            ['Mantenimiento integral de laptop empresarial', 'Limpieza interna, cambio de pasta termica y optimizacion del sistema.'],
            ['Instalacion de red WiFi para oficina', 'Configuracion de router, repetidores y pruebas de cobertura.'],
            ['Diagnostico y recuperacion de archivos', 'Revision de disco, recuperacion parcial y respaldo de informacion importante.'],
            ['Instalacion de camaras de seguridad', 'Montaje, configuracion y verificacion remota desde celular.'],
        ];

        foreach (array_slice($trabajos, 0, 3) as $posicion => $trabajo) {
            PortafolioProveedor::updateOrCreate(
                [
                    'perfil_proveedor_id' => $perfil->id,
                    'titulo' => $trabajo[0] . ' - ' . ($index + 1) . '.' . ($posicion + 1),
                ],
                [
                    'descripcion' => $trabajo[1],
                    'fecha_trabajo' => $this->ahora->copy()->subDays(20 + $index * 3 + $posicion)->toDateString(),
                    'estado' => $posicion !== 2 || $index % 4 !== 0,
                ]
            );
        }
    }

    private function crearSolicitudes(array $clientes, array $proveedores, $especialidades, array $zonas): array
    {
        $descripciones = [
            'Mi laptop enciende lento y se apaga despues de unos minutos. Necesito revision completa.',
            'Necesito instalar camaras de seguridad en mi negocio y poder verlas desde el celular.',
            'El router pierde senal en varios ambientes y necesito mejorar la cobertura WiFi.',
            'Mi celular no carga correctamente y el conector parece estar flojo.',
            'Quiero cambiar mi disco duro por SSD y pasar mis archivos sin perder informacion.',
            'La impresora imprime con manchas y hace ruido al tomar hojas.',
            'Necesito recuperar archivos de una memoria USB que ya no abre.',
            'Mi computadora de escritorio prende pero no muestra imagen en el monitor.',
            'Necesito instalar Windows y dejar programas basicos listos para trabajar.',
            'La camara IP no conecta a internet y no puedo verla desde la aplicacion.',
        ];

        $estados = ['pendiente', 'rechazada', 'cancelada', 'aceptada', 'finalizada'];
        $solicitudes = [];

        for ($i = 0; $i < 60; $i++) {
            $cliente = $clientes[$i % count($clientes)];
            $proveedor = $proveedores[$i % count($proveedores)];
            $especialidadesProveedor = $proveedor->proveedorEspecialidades
                ->where('estado', true)
                ->pluck('especialidad')
                ->filter()
                ->values();
            $especialidad = $especialidadesProveedor->isNotEmpty()
                ? $especialidadesProveedor[$i % $especialidadesProveedor->count()]
                : $especialidades[$i % $especialidades->count()];
            $zona = $this->buscarZona($zonas, $proveedor->ubicacion?->zona ?? 'Sopocachi', $i);
            $estado = $estados[$i % count($estados)];
            $fecha = $this->ahora->copy()->addDays(($i % 18) - 5);

            $solicitud = Solicitud::updateOrCreate(
                [
                    'cliente_user_id' => $cliente->id,
                    'titulo' => 'Solicitud simulada LPZ-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                ],
                [
                    'perfil_proveedor_id' => $proveedor->id,
                    'especialidad_id' => $especialidad->id,
                    'descripcion' => $descripciones[$i % count($descripciones)],
                    'tipo_atencion' => ['domicilio', 'local', 'remoto', 'mixto'][$i % 4],
                    'direccion' => $this->direccionParaZona($zona['zona'], $i + 20),
                    'zona' => $zona['zona'],
                    'latitud' => $zona['latitud'],
                    'longitud' => $zona['longitud'],
                    'fecha_solicitada' => $fecha->toDateString(),
                    'hora_solicitada' => sprintf('%02d:%02d', 8 + ($i % 8), $i % 2 === 0 ? 30 : 0),
                    'estado' => $estado,
                    'motivo_cancelacion' => in_array($estado, ['rechazada', 'cancelada'], true)
                        ? 'No se pudo coordinar la atencion en el horario solicitado.'
                        : null,
                    'observaciones' => $estado === 'pendiente'
                        ? 'Solicitud en espera de respuesta del proveedor.'
                        : 'Registro generado para pruebas funcionales del flujo de atencion.',
                ]
            );

            $this->registrarHistorialSolicitud($solicitud, $cliente, $estado);
            $solicitudes[] = $solicitud->fresh(['perfilProveedor', 'especialidad', 'cliente']);
        }

        return $solicitudes;
    }

    private function registrarHistorialSolicitud(Solicitud $solicitud, User $cliente, string $estado): void
    {
        HistorialSolicitud::firstOrCreate(
            [
                'solicitud_id' => $solicitud->id,
                'user_id' => $cliente->id,
                'estado_anterior' => null,
                'estado_nuevo' => 'pendiente',
                'comentario' => 'Solicitud registrada por el cliente.',
            ]
        );

        if ($estado !== 'pendiente') {
            HistorialSolicitud::firstOrCreate(
                [
                    'solicitud_id' => $solicitud->id,
                    'user_id' => $solicitud->perfilProveedor?->user_id ?? $cliente->id,
                    'estado_anterior' => 'pendiente',
                    'estado_nuevo' => $estado,
                    'comentario' => 'Cambio de estado generado para datos de prueba.',
                ]
            );
        }
    }

    private function crearCitas(array $solicitudes): array
    {
        $citasCompletadas = [];
        $estadosCita = ['programada', 'en_atencion', 'completada', 'cancelada', 'no_asistio', 'vencida'];

        foreach ($solicitudes as $index => $solicitud) {
            if (! in_array($solicitud->estado, ['aceptada', 'finalizada'], true)) {
                continue;
            }

            $estadoCita = $estadosCita[$index % count($estadosCita)];

            if ($solicitud->estado === 'finalizada') {
                $estadoCita = 'completada';
            }

            $fecha = $estadoCita === 'programada'
                ? $this->ahora->copy()->addDays(($index % 10) + 1)
                : $this->ahora->copy()->subDays(($index % 20) + 1);

            $horaInicio = sprintf('%02d:00', 9 + ($index % 7));
            $horaFin = sprintf('%02d:30', 10 + ($index % 7));

            $cita = Cita::updateOrCreate(
                ['solicitud_id' => $solicitud->id],
                [
                    'fecha_cita' => $fecha->toDateString(),
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'estado' => $estadoCita,
                    'observaciones' => match ($estadoCita) {
                        'programada' => 'Cita programada para atencion del servicio.',
                        'en_atencion' => 'Atencion iniciada por el proveedor.',
                        'completada' => 'Servicio completado correctamente.',
                        'cancelada' => 'Cita cancelada por reprogramacion externa.',
                        'no_asistio' => 'No se pudo realizar la atencion por inasistencia.',
                        'vencida' => 'Cita vencida sin inicio de atencion.',
                        default => null,
                    },
                ]
            );

            if ($estadoCita === 'completada') {
                $solicitud->update(['estado' => 'finalizada']);
                $citasCompletadas[] = $cita->fresh(['solicitud.perfilProveedor.user', 'solicitud.cliente']);
            }
        }

        return $citasCompletadas;
    }

    private function crearCalificaciones(array $citasCompletadas, $aspectos): void
    {
        $comentarios = [
            'Excelente atencion, explico todo con claridad y soluciono el problema.',
            'Muy buen servicio, llego a tiempo y dejo el equipo funcionando.',
            'Trabajo responsable, precio claro y buena comunicacion.',
            'La atencion fue buena y el proveedor fue muy cuidadoso.',
            'Recomendado, resolvio el problema sin complicaciones.',
        ];

        foreach ($citasCompletadas as $index => $cita) {
            $puntuacion = 4 + ($index % 2);
            $calificacion = Calificacion::updateOrCreate(
                ['cita_id' => $cita->id],
                [
                    'puntuacion' => $puntuacion,
                    'comentario' => $comentarios[$index % count($comentarios)],
                    'estado' => $index % 9 === 0 ? 'pendiente_revision' : 'visible',
                ]
            );

            foreach ($aspectos as $posicion => $aspecto) {
                DetalleCalificacion::updateOrCreate(
                    [
                        'calificacion_id' => $calificacion->id,
                        'aspecto_calificacion_id' => $aspecto->id,
                    ],
                    [
                        'puntuacion' => max(1, min(5, $puntuacion - (($index + $posicion) % 4 === 0 ? 1 : 0))),
                    ]
                );
            }

            if ($index % 2 === 0) {
                RespuestaCalificacion::updateOrCreate(
                    ['calificacion_id' => $calificacion->id],
                    [
                        'user_id' => $cita->solicitud?->perfilProveedor?->user_id,
                        'respuesta' => 'Gracias por confiar en nuestro servicio. Seguiremos mejorando para atenderte mejor.',
                        'estado' => 'visible',
                    ]
                );
            }
        }
    }

    private function crearReportes(User $admin): void
    {
        $reportes = [
            ['Reporte general de proveedores La Paz', 'proveedores'],
            ['Reporte de solicitudes y citas del mes', 'solicitudes_citas'],
            ['Reporte de calificaciones visibles', 'calificaciones'],
            ['Reporte de actividad administrativa', 'actividad'],
            ['Reporte de documentos por revisar', 'documentos'],
        ];

        foreach ($reportes as $index => [$nombre, $tipo]) {
            Reporte::updateOrCreate(
                ['user_id' => $admin->id, 'nombre' => $nombre],
                [
                    'tipo' => $tipo,
                    'fecha_inicio' => $this->ahora->copy()->subMonths(2)->startOfMonth()->toDateString(),
                    'fecha_fin' => $this->ahora->copy()->endOfMonth()->toDateString(),
                    'incluir_graficas' => true,
                    'incluir_imagenes' => $index % 2 === 0,
                    'estado' => true,
                    'opciones' => [
                        'generado_para' => 'datos_simulados_lapaz',
                        'nota' => 'Datos de prueba para validar reportes.',
                    ],
                ]
            );
        }
    }

    private function especialidadesActivas()
    {
        return DB::table('especialidades')
            ->join('rubro_tipo_servicio', 'especialidades.rubro_tipo_servicio_id', '=', 'rubro_tipo_servicio.id')
            ->join('tipos_servicio', 'rubro_tipo_servicio.tipo_servicio_id', '=', 'tipos_servicio.id')
            ->where('especialidades.estado', true)
            ->where('rubro_tipo_servicio.estado', true)
            ->where('tipos_servicio.estado', true)
            ->select('especialidades.*')
            ->orderBy('especialidades.id')
            ->get()
            ->map(fn ($row) => (object) (array) $row);
    }

    private function zonasLaPaz(): array
    {
        $path = public_path('assets/js/maps/LaPaz/zonasGAMPL.geojson');

        if (! is_file($path)) {
            return $this->zonasFallback();
        }

        $json = json_decode((string) file_get_contents($path), true);

        if (! is_array($json) || empty($json['features'])) {
            return $this->zonasFallback();
        }

        $zonas = [];

        foreach ($json['features'] as $feature) {
            $nombre = $feature['properties']['zona'] ?? $feature['properties']['zonaref'] ?? null;
            $coordenadas = $feature['geometry']['coordinates'] ?? [];

            if (! $nombre || str_contains($nombre, 'Ã')) {
                continue;
            }

            $puntos = $this->aplanarCoordenadas($coordenadas);

            if (empty($puntos)) {
                continue;
            }

            $latitud = array_sum(array_column($puntos, 'lat')) / count($puntos);
            $longitud = array_sum(array_column($puntos, 'lng')) / count($puntos);

            $zonas[] = [
                'zona' => Str::title(Str::lower($nombre)),
                'latitud' => round($latitud, 7),
                'longitud' => round($longitud, 7),
            ];
        }

        return $zonas ?: $this->zonasFallback();
    }

    private function aplanarCoordenadas(array $coordenadas): array
    {
        $puntos = [];

        $recorrer = function ($items) use (&$recorrer, &$puntos) {
            if (is_array($items) && count($items) >= 2 && is_numeric($items[0]) && is_numeric($items[1])) {
                $puntos[] = ['lng' => (float) $items[0], 'lat' => (float) $items[1]];
                return;
            }

            if (is_array($items)) {
                foreach ($items as $item) {
                    $recorrer($item);
                }
            }
        };

        $recorrer($coordenadas);

        return $puntos;
    }

    private function buscarZona(array $zonas, string $nombre, int $fallbackIndex): array
    {
        $normalizado = $this->normalizar($nombre);

        foreach ($zonas as $zona) {
            if ($this->normalizar($zona['zona']) === $normalizado) {
                return $zona;
            }
        }

        return $zonas[$fallbackIndex % count($zonas)] ?? $this->zonasFallback()[0];
    }

    private function normalizar(string $valor): string
    {
        return Str::of($valor)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }

    private function direccionParaZona(string $zona, int $index): string
    {
        $calles = [
            'Av. 6 de Agosto', 'Av. Arce', 'Calle 21', 'Av. Busch', 'Av. Ballivian',
            'Calle Mercado', 'Av. Saavedra', 'Av. Sanchez Lima', 'Calle Sagarnaga', 'Av. Camacho',
        ];

        return $calles[$index % count($calles)] . ' Nro ' . (120 + $index * 17) . ', zona ' . $zona;
    }

    private function zonasFallback(): array
    {
        return [
            ['zona' => 'Sopocachi', 'latitud' => -16.5092781, 'longitud' => -68.1260033],
            ['zona' => 'Miraflores', 'latitud' => -16.5034200, 'longitud' => -68.1192800],
            ['zona' => 'San Pedro', 'latitud' => -16.5039000, 'longitud' => -68.1352000],
            ['zona' => 'Obrajes', 'latitud' => -16.5229000, 'longitud' => -68.1129000],
            ['zona' => 'Calacoto', 'latitud' => -16.5409000, 'longitud' => -68.0777000],
            ['zona' => 'Achumani', 'latitud' => -16.5423000, 'longitud' => -68.0644000],
            ['zona' => 'Cota Cota', 'latitud' => -16.5376000, 'longitud' => -68.0665000],
            ['zona' => 'Centro', 'latitud' => -16.4959000, 'longitud' => -68.1336000],
            ['zona' => 'San Miguel', 'latitud' => -16.5412000, 'longitud' => -68.0799000],
            ['zona' => 'Villa Fatima', 'latitud' => -16.4798000, 'longitud' => -68.1195000],
            ['zona' => 'Tembladerani', 'latitud' => -16.5087000, 'longitud' => -68.1423000],
            ['zona' => 'Villa Copacabana', 'latitud' => -16.4929000, 'longitud' => -68.1099000],
        ];
    }
}
