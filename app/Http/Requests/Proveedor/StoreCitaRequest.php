<?php

namespace App\Http\Requests\Proveedor;

use App\Models\Cita;
use App\Models\Solicitud;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitud_id' => [
                'required',
                'integer',
                Rule::exists('solicitudes', 'id'),
            ],
            'fecha_cita' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('solicitud_id')) {
                return;
            }

            $solicitud = Solicitud::with('cita')->find($this->input('solicitud_id'));
            $perfilProveedor = $this->user()?->perfilProveedor;

            if (! $solicitud || ! $perfilProveedor) {
                return;
            }

            if ((int) $solicitud->perfil_proveedor_id !== (int) $perfilProveedor->id) {
                $validator->errors()->add('solicitud_id', 'La solicitud no pertenece a tu perfil de proveedor.');
            }

            if ($solicitud->estado !== 'aceptada') {
                $validator->errors()->add('solicitud_id', 'Solo puedes programar citas para solicitudes aceptadas.');
            }

            if ($solicitud->cita) {
                $validator->errors()->add('solicitud_id', 'Esta solicitud ya tiene una cita programada.');
            }

            $this->validarCruceHorario($validator, $perfilProveedor->id);
        });
    }

    protected function validarCruceHorario(Validator $validator, int $perfilProveedorId): void
    {
        if (! $this->filled(['fecha_cita', 'hora_inicio', 'hora_fin'])) {
            return;
        }

        $existeCruce = Cita::query()
            ->where('fecha_cita', $this->input('fecha_cita'))
            ->whereHas('solicitud', fn ($query) => $query->where('perfil_proveedor_id', $perfilProveedorId))
            ->whereNotIn('estado', ['cancelada', 'completada', 'no_asistio'])
            ->where(function ($query) {
                $query->where('hora_inicio', '<', $this->input('hora_fin'))
                    ->where('hora_fin', '>', $this->input('hora_inicio'));
            })
            ->exists();

        if ($existeCruce) {
            $validator->errors()->add('hora_inicio', 'Ya existe una cita programada en ese rango de horario.');
        }
    }
}
