<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reporte->nombre }}</title>
    <style>
        @php
            $colorPrincipal = $configuracionPdf->color_principal ?: '#635bff';
            $logoAbsoluto = $configuracionPdf->logo_path ? public_path($configuracionPdf->logo_path) : null;
        @endphp

        @page {
            margin: 26px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 12px;
            line-height: 1.45;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            border-bottom: 2px solid {{ $colorPrincipal }};
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .logo {
            max-width: 115px;
            max-height: 62px;
            object-fit: contain;
            text-align: right;
        }

        .header h1 {
            font-size: 22px;
            color: #111827;
        }

        .muted {
            color: #6b7280;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            background: #eef2ff;
            color: {{ $colorPrincipal }};
            font-size: 10px;
            font-weight: bold;
        }

        .grid {
            width: 100%;
            margin-bottom: 18px;
        }

        .card {
            border: 1px solid #d8dee9;
            border-radius: 8px;
            padding: 12px;
            background: #ffffff;
        }

        .metric {
            width: 24%;
            display: inline-block;
            vertical-align: top;
            margin-right: 1%;
            margin-bottom: 10px;
            min-height: 76px;
        }

        .metric h3 {
            font-size: 22px;
            margin-top: 6px;
        }

        .section {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .section h2 {
            font-size: 16px;
            margin-bottom: 9px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f3f4f6;
            color: #374151;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 7px;
            vertical-align: top;
        }

        .chart-row {
            margin-bottom: 8px;
        }

        .chart-label {
            width: 32%;
            display: inline-block;
            font-weight: bold;
        }

        .chart-track {
            width: 55%;
            display: inline-block;
            height: 13px;
            background: #eef2ff;
            border-radius: 8px;
            vertical-align: middle;
            overflow: hidden;
        }

        .chart-bar {
            height: 13px;
            background: {{ $colorPrincipal }};
            border-radius: 8px;
        }

        .chart-value {
            width: 10%;
            display: inline-block;
            text-align: right;
            font-weight: bold;
        }

        .image-grid img {
            width: 31%;
            height: 105px;
            object-fit: cover;
            border: 1px solid #d8dee9;
            border-radius: 8px;
            margin-right: 1%;
            margin-bottom: 8px;
        }

        .footer {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <span class="badge">{{ $tipoNombre }}</span>
                    <h1>{{ $configuracionPdf->titulo_encabezado ?: $reporte->nombre }}</h1>
                    <p class="muted">
                        @if ($configuracionPdf->mostrar_generado_por)
                            Generado por {{ $generadoPor?->name ?? 'Sistema' }}.
                        @endif
                        @if ($configuracionPdf->mostrar_fecha)
                            Fecha: {{ $generadoEn->format('d/m/Y H:i') }}.
                        @endif
                        @if ($reporte->fecha_inicio || $reporte->fecha_fin)
                            Rango: {{ $reporte->fecha_inicio?->format('d/m/Y') ?? 'Inicio' }} -
                            {{ $reporte->fecha_fin?->format('d/m/Y') ?? 'Hoy' }}.
                        @else
                            Rango: todo el historial.
                        @endif
                    </p>
                </td>
                @if ($configuracionPdf->mostrar_logo && $logoAbsoluto && file_exists($logoAbsoluto))
                    <td style="width: 130px; text-align: right;">
                        <img class="logo" src="{{ $logoAbsoluto }}" alt="Logo">
                    </td>
                @endif
            </tr>
        </table>
    </div>

    @if (! empty($datos['tarjetas']))
        <div class="grid">
            @foreach ($datos['tarjetas'] as $tarjeta)
                <div class="card metric">
                    <p class="muted">{{ $tarjeta['titulo'] }}</p>
                    <h3>{{ $tarjeta['valor'] }}</h3>
                    <p class="muted">{{ $tarjeta['detalle'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($reporte->incluir_graficas && ! empty($datos['graficas']))
        <div class="section">
            <h2>Graficas</h2>
            @foreach ($datos['graficas'] as $titulo => $grafica)
                @php
                    $maximo = max(array_values($grafica) ?: [1]);
                @endphp
                <div class="card" style="margin-bottom: 10px;">
                    <h3 style="font-size: 13px; margin-bottom: 8px;">{{ str($titulo)->replace('_', ' ')->title() }}</h3>
                    @foreach ($grafica as $label => $valor)
                        @php
                            $porcentaje = $maximo > 0 ? max(3, ($valor / $maximo) * 100) : 3;
                        @endphp
                        <div class="chart-row">
                            <span class="chart-label">{{ str($label)->replace('_', ' ')->title() }}</span>
                            <span class="chart-track">
                                <span class="chart-bar" style="width: {{ $porcentaje }}%;"></span>
                            </span>
                            <span class="chart-value">{{ $valor }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    @if (! empty($datos['imagenes']) && $datos['imagenes']->isNotEmpty())
        <div class="section">
            <h2>Imagenes relacionadas</h2>
            <div class="image-grid">
                @foreach ($datos['imagenes'] as $imagen)
                    <img src="{{ $imagen['ruta'] }}" alt="{{ $imagen['titulo'] }}">
                @endforeach
            </div>
        </div>
    @endif

    <div class="section">
        <h2>Detalle</h2>

        @if ($reporte->tipo === 'proveedores')
            <table>
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Usuario</th>
                        <th>Zona</th>
                        <th>Verificacion</th>
                        <th>Especialidades</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datos['filas'] as $proveedor)
                        <tr>
                            <td>{{ $proveedor->nombre_publico }}</td>
                            <td>{{ $proveedor->user?->name }}<br><span class="muted">{{ $proveedor->user?->email }}</span></td>
                            <td>{{ $proveedor->ubicacion?->zona ?? 'Sin ubicacion' }}</td>
                            <td>{{ str($proveedor->estado_verificacion)->title() }}</td>
                            <td>{{ $proveedor->proveedorEspecialidades->count() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($reporte->tipo === 'solicitudes_citas')
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Proveedor</th>
                        <th>Especialidad</th>
                        <th>Horario</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datos['filas'] as $cita)
                        <tr>
                            <td>{{ $cita->fecha_cita?->format('d/m/Y') }}</td>
                            <td>{{ $cita->solicitud?->cliente?->name }}</td>
                            <td>{{ $cita->solicitud?->perfilProveedor?->nombre_publico }}</td>
                            <td>{{ $cita->solicitud?->especialidad?->nombre }}</td>
                            <td>{{ $cita->hora_inicio?->format('H:i') }} - {{ $cita->hora_fin?->format('H:i') }}</td>
                            <td>{{ str($cita->estado)->replace('_', ' ')->title() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($reporte->tipo === 'calificaciones')
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Proveedor</th>
                        <th>Puntuacion</th>
                        <th>Comentario</th>
                        <th>Estado</th>
                        <th>Respuesta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datos['filas'] as $calificacion)
                        <tr>
                            <td>{{ $calificacion->cita?->solicitud?->cliente?->name }}</td>
                            <td>{{ $calificacion->cita?->solicitud?->perfilProveedor?->nombre_publico }}</td>
                            <td>{{ $calificacion->puntuacion }} / 5</td>
                            <td>{{ $calificacion->comentario }}</td>
                            <td>{{ str($calificacion->estado)->title() }}</td>
                            <td>{{ $calificacion->respuesta?->respuesta ?? 'Sin respuesta' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($reporte->tipo === 'documentos')
            <table>
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Tipo</th>
                        <th>Revision</th>
                        <th>Observacion</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datos['filas'] as $documento)
                        <tr>
                            <td>{{ $documento->perfilProveedor?->nombre_publico }}</td>
                            <td>{{ $documento->tipoDocumentoProveedor?->nombre }}</td>
                            <td>{{ str($documento->estado_revision)->title() }}</td>
                            <td>{{ $documento->observacion ?? 'Sin observacion' }}</td>
                            <td>{{ $documento->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($reporte->tipo === 'backups')
            <table>
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Tamano</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datos['filas'] as $backup)
                        <tr>
                            <td>{{ $backup['nombre'] }}</td>
                            <td>{{ number_format($backup['tamano'] / 1024 / 1024, 2) }} MB</td>
                            <td>{{ date('d/m/Y H:i', $backup['fecha']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Sin backups locales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($reporte->tipo === 'activity_logs')
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Modulo</th>
                        <th>Accion</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datos['filas'] as $actividad)
                        <tr>
                            <td>{{ $actividad->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $actividad->log_name }}</td>
                            <td>{{ $actividad->description }}</td>
                            <td>{{ $actividad->causer?->name ?? 'Sistema' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sin actividad registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Proveedor</th>
                        <th>Titulo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datos['filas'] as $solicitud)
                        <tr>
                            <td>{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $solicitud->cliente?->name }}</td>
                            <td>{{ $solicitud->perfilProveedor?->nombre_publico }}</td>
                            <td>{{ $solicitud->titulo }}</td>
                            <td>{{ str($solicitud->estado)->replace('_', ' ')->title() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        {{ $configuracionPdf->texto_pie ?: 'Proyecto Integrador - Reporte generado automaticamente desde el sistema.' }}
    </div>
</body>
</html>
