<?php

namespace App\Http\Requests\Notificaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificacionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $usuario = $this->user();

        if (! $usuario?->can('gestionar mis notificaciones')) {
            return false;
        }

        return $this->input('accion') !== 'eliminar'
            || $usuario->can('eliminar mis notificaciones');
    }

    public function rules(): array
    {
        return [
            'notificaciones' => ['required', 'array', 'min:1', 'max:50'],
            'notificaciones.*' => ['required', 'uuid', 'distinct'],
            'accion' => ['required', Rule::in(['leer', 'no_leer', 'eliminar'])],
        ];
    }

    public function messages(): array
    {
        return [
            'notificaciones.required' => 'Selecciona al menos una notificacion.',
            'notificaciones.min' => 'Selecciona al menos una notificacion.',
            'notificaciones.max' => 'Puedes procesar hasta 50 notificaciones a la vez.',
        ];
    }
}
