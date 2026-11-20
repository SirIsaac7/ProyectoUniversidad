<?php

namespace App\Http\Requests\BusquedaInteligente\Administracion;

use Illuminate\Foundation\Http\FormRequest;

class FiltrarHistorialIndexacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ver historial indexaciones motor') ?? false;
    }

    public function rules(): array
    {
        return [
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'tipo' => ['nullable', 'in:manual,automatica'],
            'estado' => ['nullable', 'in:pendiente,ejecucion,completado,error,desconocido'],
        ];
    }
}
