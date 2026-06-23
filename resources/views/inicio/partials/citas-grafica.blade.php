@php
    $titulo = $titulo ?? 'Resumen de citas';
    $descripcion = $descripcion ?? 'Movimiento general de tus citas durante ' . ($resumenCitas['anio'] ?? now()->year) . '.';
    $chartData = [
        'labels' => $resumenCitas['labels'] ?? [],
        'series' => $resumenCitas['series'] ?? [
            'totales' => [],
            'completadas' => [],
            'incidencias' => [],
        ],
    ];
@endphp

<div class="card inicio-citas-overview-card mt-3">
    <div class="card-header align-items-center d-flex flex-wrap gap-2">
        <div class="flex-grow-1">
            <h5 class="card-title mb-1">{{ $titulo }}</h5>
            <p class="text-muted mb-0">{{ $descripcion }}</p>
        </div>
        <span class="badge bg-primary-subtle text-primary">
            Año actual
        </span>
    </div>

    <div class="card-body p-0">
        <div class="row g-0 inicio-citas-metricas">
            <div class="col-6 col-xl-3">
                <div class="inicio-citas-metrica">
                    <span class="inicio-citas-metrica-icon bg-primary-subtle text-primary">
                        <i class="ri-calendar-check-line"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">{{ $resumenCitas['totalAnio'] }}</h3>
                        <p class="text-muted mb-0">Citas del año</p>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="inicio-citas-metrica">
                    <span class="inicio-citas-metrica-icon bg-info-subtle text-info">
                        <i class="ri-time-line"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">{{ $resumenCitas['programadas'] }}</h3>
                        <p class="text-muted mb-0">Programadas</p>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="inicio-citas-metrica">
                    <span class="inicio-citas-metrica-icon bg-success-subtle text-success">
                        <i class="ri-checkbox-circle-line"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">{{ $resumenCitas['completadas'] }}</h3>
                        <p class="text-muted mb-0">Completadas</p>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="inicio-citas-metrica">
                    <span class="inicio-citas-metrica-icon bg-warning-subtle text-warning">
                        <i class="ri-alert-line"></i>
                    </span>
                    <div>
                        <h3 class="mb-1">{{ $resumenCitas['incidencias'] }}</h3>
                        <p class="text-muted mb-0">Canceladas o vencidas</p>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="inicio-citas-chart"
            data-inicio-citas-chart
            data-chart='@json($chartData)'
        ></div>
    </div>
</div>
