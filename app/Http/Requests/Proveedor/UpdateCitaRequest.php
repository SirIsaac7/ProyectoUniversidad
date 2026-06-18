<?php

namespace App\Http\Requests\Proveedor;

use App\Models\Cita;
use App\Models\HorarioProveedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Carbon;

class UpdateCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $estadoRequerido = $this->routeIs('proveedor.citas.estado') ? 'required' : 'nullable';

        return [
            'fecha_cita' => ['nullable', 'date', 'after_or_equal:today', 'required_with:hora_inicio,hora_fin'],
            'hora_inicio' => ['nullable', 'date_format:H:i', 'required_with:fecha_cita,hora_fin'],
            'hora_fin' => ['nullable', 'date_format:H:i', 'after:hora_inicio', 'required_with:fecha_cita,hora_inicio'],
            'estado' => [
                $estadoRequerido,
                Rule::in(['programada', 'en_atencion', 'completada', 'cancelada', 'no_asistio', 'vencida']),
            ],
            'observaciones' => ['nullable', 'required_if:estado,cancelada,no_asistio,vencida', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cita = $this->route('cita');
            $perfilProveedor = $this->user()?->perfilProveedor;

            if (! $cita instanceof Cita || ! $perfilProveedor) {
                return;
            }

            if ((int) $cita->solicitud?->perfil_proveedor_id !== (int) $perfilProveedor->id) {
                $validator->errors()->add('cita', 'La cita no pertenece a tu perfil de proveedor.');
                return;
            }

            $this->validarTransicionEstado($validator, $cita);

            if ($this->filled('fecha_cita') || $this->filled('hora_inicio') || $this->filled('hora_fin')) {
                $this->validarCruceHorario($validator, $cita, $perfilProveedor->id);
                $this->validarDisponibilidadProveedor($validator, $cita, $perfilProveedor->id);
            }
        });
    }

    protected function validarTransicionEstado(Validator $validator, Cita $cita): void
    {
        if (! $this->filled('estado')) {
            return;
        }

        $nuevoEstado = $this->input('estado');

        if (in_array($cita->estado, ['completada', 'cancelada', 'no_asistio', 'vencida'], true)) {
            $validator->errors()->add('estado', 'No puedes modificar una cita cerrada.');
            return;
        }

        if ($nuevoEstado === 'en_atencion') {
            if ($cita->estado !== 'programada') {
                $validator->errors()->add('estado', 'Solo puedes iniciar citas programadas.');
                return;
            }

            if (! $cita->fecha_cita || ! $cita->hora_inicio) {
                $validator->errors()->add('estado', 'La cita no tiene fecha y hora de inicio configuradas.');
                return;
            }

            $inicio = Carbon::parse($cita->fecha_cita->format('Y-m-d') . ' ' . $cita->hora_inicio->format('H:i'));

            if (! now()->isSameDay($inicio) || now()->lessThan($inicio->copy()->subMinutes(15))) {
                $validator->errors()->add('estado', 'Solo puedes iniciar la atencion el dia de la cita y hasta 15 minutos antes.');
            }
        }

        if ($nuevoEstado === 'completada' && $cita->estado !== 'en_atencion') {
            $validator->errors()->add('estado', 'Solo puedes completar una cita que esta en atencion.');
        }
    }

    protected function validarCruceHorario(Validator $validator, Cita $cita, int $perfilProveedorId): void
    {
        $fecha = $this->input('fecha_cita', optional($cita->fecha_cita)->format('Y-m-d'));
        $horaInicio = $this->input('hora_inicio', $cita->hora_inicio?->format('H:i'));
        $horaFin = $this->input('hora_fin', $cita->hora_fin?->format('H:i'));

        if (! $fecha || ! $horaInicio || ! $horaFin) {
            return;
        }

        $existeCruce = Cita::query()
            ->whereKeyNot($cita->id)
            ->where('fecha_cita', $fecha)
            ->whereHas('solicitud', fn ($query) => $query->where('perfil_proveedor_id', $perfilProveedorId))
            ->whereNotIn('estado', ['cancelada', 'completada', 'no_asistio', 'vencida'])
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio);
            })
            ->exists();

        if ($existeCruce) {
            $validator->errors()->add('hora_inicio', 'Ya existe una cita programada en ese rango de horario.');
        }
    }

    protected function validarDisponibilidadProveedor(Validator $validator, Cita $cita, int $perfilProveedorId): void
    {
        $fecha = $this->input('fecha_cita', optional($cita->fecha_cita)->format('Y-m-d'));
        $horaInicio = $this->input('hora_inicio', $cita->hora_inicio?->format('H:i'));
        $horaFin = $this->input('hora_fin', $cita->hora_fin?->format('H:i'));

        if (! $fecha || ! $horaInicio || ! $horaFin) {
            return;
        }

        $diaSemana = Carbon::parse($fecha)->dayOfWeekIso;
        $tipoAtencion = $cita->solicitud?->tipo_atencion ?? 'mixto';

        $hayHorario = HorarioProveedor::query()
            ->where('perfil_proveedor_id', $perfilProveedorId)
            ->where('dia_semana', $diaSemana)
            ->where('estado', true)
            ->where('disponible', true)
            ->when($tipoAtencion !== 'mixto', function ($query) use ($tipoAtencion) {
                $query->where(function ($subquery) use ($tipoAtencion) {
                    $subquery->where('tipo_atencion', 'mixto')
                        ->orWhere('tipo_atencion', $tipoAtencion);
                });
            })
            ->where('hora_inicio', '<=', $horaInicio)
            ->where('hora_fin', '>=', $horaFin)
            ->exists();

        if (! $hayHorario) {
            $validator->errors()->add('fecha_cita', 'El horario seleccionado no coincide con tu disponibilidad configurada.');
        }
    }
}
