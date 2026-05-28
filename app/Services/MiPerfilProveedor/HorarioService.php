<?php

namespace App\Services\MiPerfilProveedor;

use App\Models\HorarioProveedor;
use App\Models\PerfilProveedor;
use App\Services\HorarioProveedorService;
use Carbon\Carbon;

class HorarioService
{
    public function __construct(
        protected HorarioProveedorService $horarioProveedorService
    ) {
    }

    public function obtenerDatosVista(): array
    {
        $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'];
        $diasCortos = [1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'];
        $tiposAtencion = ['mixto' => 'Mixto', 'domicilio' => 'Domicilio', 'local' => 'Local', 'remoto' => 'Remoto'];

        $perfilProveedor = $this->getPerfilActual();

        $horarios = $perfilProveedor->horarios->sortBy([
            ['dia_semana', 'asc'],
            ['hora_inicio', 'asc'],
        ]);

        $horariosDisponibles = $horarios->where('disponible', true);
        $diasDisponibles = $horariosDisponibles->pluck('dia_semana')->unique()->count();
        $minutosSemana = $this->calcularMinutosSemana($horariosDisponibles);
        $horasSemana = floor($minutosSemana / 60);
        $minutosRestantes = $minutosSemana % 60;
        $proximoDescanso = $this->obtenerProximoDescanso($horarios);
        $horariosCalendario = $this->prepararHorariosCalendario($horarios, $diasSemana);

        return compact(
            'perfilProveedor',
            'diasSemana',
            'diasCortos',
            'tiposAtencion',
            'horarios',
            'horariosDisponibles',
            'diasDisponibles',
            'horasSemana',
            'minutosRestantes',
            'proximoDescanso',
            'horariosCalendario'
        );
    }

    public function getPerfilActual(): PerfilProveedor
    {
        return PerfilProveedor::with([
            'horarios' => fn ($query) => $query->where('estado', true),
        ])
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function crearActual(array $data): HorarioProveedor
    {
        return $this->horarioProveedorService->createForPerfil(
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function actualizarActual(HorarioProveedor $horarioProveedor, array $data): HorarioProveedor
    {
        return $this->horarioProveedorService->updateForPerfil(
            $horarioProveedor,
            $this->getPerfilActual()->id,
            $data
        );
    }

    public function eliminarActual(HorarioProveedor $horarioProveedor): void
    {
        $perfilProveedor = $this->getPerfilActual();

        abort_unless((int) $horarioProveedor->perfil_proveedor_id === $perfilProveedor->id, 403);

        $this->horarioProveedorService->bajaLogica($horarioProveedor);
    }

    private function calcularMinutosSemana($horariosDisponibles): int
    {
        return $horariosDisponibles->sum(function ($horario) {
            if (! $horario->hora_inicio || ! $horario->hora_fin) {
                return 0;
            }

            return Carbon::parse($horario->hora_inicio)->diffInMinutes(Carbon::parse($horario->hora_fin));
        });
    }

    private function obtenerProximoDescanso($horarios)
    {
        $diaActual = now()->isoWeekday();

        return $horarios
            ->where('disponible', false)
            ->sortBy(fn ($horario) => ($horario->dia_semana - $diaActual + 7) % 7)
            ->first();
    }

    private function prepararHorariosCalendario($horarios, array $diasSemana)
    {
        return $horarios->map(function ($horario) use ($diasSemana) {
            return [
                'id' => $horario->id,
                'dia_semana' => $horario->dia_semana,
                'dia' => $diasSemana[$horario->dia_semana] ?? 'Sin dia',
                'hora_inicio' => optional($horario->hora_inicio)->format('H:i'),
                'hora_fin' => optional($horario->hora_fin)->format('H:i'),
                'tipo_atencion' => $horario->tipo_atencion,
                'disponible' => $horario->disponible,
            ];
        })->values();
    }
}
