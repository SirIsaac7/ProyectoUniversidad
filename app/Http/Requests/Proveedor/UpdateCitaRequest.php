<?php

namespace App\Http\Requests\Proveedor;

use App\Models\Cita;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
                Rule::in(['programada', 'reprogramada', 'en_camino', 'en_atencion', 'completada', 'cancelada', 'no_asistio']),
            ],
            'observaciones' => ['nullable', 'required_if:estado,cancelada,no_asistio', 'string', 'max:1000'],
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

            $this->validarCruceHorario($validator, $cita, $perfilProveedor->id);
        });
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
            ->whereNotIn('estado', ['cancelada', 'completada', 'no_asistio'])
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where('hora_inicio', '<', $horaFin)
                    ->where('hora_fin', '>', $horaInicio);
            })
            ->exists();

        if ($existeCruce) {
            $validator->errors()->add('hora_inicio', 'Ya existe una cita programada en ese rango de horario.');
        }
    }
}
