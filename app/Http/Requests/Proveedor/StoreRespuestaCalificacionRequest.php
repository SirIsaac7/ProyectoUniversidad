<?php

namespace App\Http\Requests\Proveedor;

use App\Models\Calificacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRespuestaCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'calificacion_id' => ['required', 'integer', 'exists:calificaciones,id'],
            'respuesta' => ['required', 'string', 'max:1500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('calificacion_id')) {
                return;
            }

            $perfilProveedor = $this->user()?->perfilProveedor;
            $calificacion = Calificacion::query()
                ->with('cita.solicitud', 'respuesta')
                ->find($this->input('calificacion_id'));

            if (! $perfilProveedor || ! $calificacion) {
                return;
            }

            if ((int) $calificacion->cita?->solicitud?->perfil_proveedor_id !== (int) $perfilProveedor->id) {
                $validator->errors()->add('calificacion_id', 'La calificación no pertenece a tu perfil de proveedor.');
            }

            if ($calificacion->respuesta) {
                $validator->errors()->add('calificacion_id', 'Esta calificación ya tiene una respuesta registrada.');
            }
        });
    }
}
