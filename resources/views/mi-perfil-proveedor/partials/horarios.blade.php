<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0">Mis horarios</h5>
    <span class="badge bg-primary-subtle text-primary">{{ $perfilProveedor->horarios->count() }} registrados</span>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle mb-0">
        <thead>
            <tr>
                <th>Dia</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Atencion</th>
                <th>Disponible</th>
            </tr>
        </thead>
        <tbody>
            @php
                $diasSemana = [
                    1 => 'Lunes',
                    2 => 'Martes',
                    3 => 'Miercoles',
                    4 => 'Jueves',
                    5 => 'Viernes',
                    6 => 'Sabado',
                    7 => 'Domingo',
                ];
            @endphp

            @forelse ($perfilProveedor->horarios as $horario)
                <tr>
                    <td>{{ $diasSemana[$horario->dia_semana] ?? 'Sin dia' }}</td>
                    <td>{{ optional($horario->hora_inicio)->format('H:i') ?: 'Sin hora' }}</td>
                    <td>{{ optional($horario->hora_fin)->format('H:i') ?: 'Sin hora' }}</td>
                    <td>{{ ucfirst($horario->tipo_atencion) }}</td>
                    <td>
                        @if ($horario->disponible)
                            <span class="badge bg-success-subtle text-success">Si</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">No</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Aun no tienes horarios registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
