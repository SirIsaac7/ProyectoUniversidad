<?php

namespace App\Http\Requests\Proveedor;

use App\Models\Cita;
use App\Models\HorarioProveedor;
use App\Models\Solicitud;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Carbon;

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

            if (! in_array($solicitud->estado, ['pendiente', 'aceptada'], true)) {
                $validator->errors()->add('solicitud_id', 'Solo puedes agendar solicitudes pendientes o aceptadas.');
            }

            if ($solicitud->cita) {
                $validator->errors()->add('solicitud_id', 'Esta solicitud ya tiene una cita programada.');
            }

            $this->validarCruceHorario($validator, $perfilProveedor->id);
            $this->validarDisponibilidadProveedor($validator, $solicitud, $perfilProveedor->id);
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
            ->whereNotIn('estado', ['cancelada', 'completada', 'no_asistio', 'vencida'])
            ->where(function ($query) {
                $query->where('hora_inicio', '<', $this->input('hora_fin'))
                    ->where('hora_fin', '>', $this->input('hora_inicio'));
            })
            ->exists();

        if ($existeCruce) {
            $validator->errors()->add('hora_inicio', 'Ya existe una cita programada en ese rango de horario.');
        }
    }

    protected function validarDisponibilidadProveedor(Validator $validator, Solicitud $solicitud, int $perfilProveedorId): void
    {
        if (! $this->filled(['fecha_cita', 'hora_inicio', 'hora_fin'])) {
            return;
        }

        $diaSemana = Carbon::parse($this->input('fecha_cita'))->dayOfWeekIso;
        $tipoAtencion = $solicitud->tipo_atencion;

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
            ->where('hora_inicio', '<=', $this->input('hora_inicio'))
            ->where('hora_fin', '>=', $this->input('hora_fin'))
            ->exists();

        if (! $hayHorario) {
            $validator->errors()->add('fecha_cita', 'El horario seleccionado no coincide con tu disponibilidad configurada.');
        }
    }
}
