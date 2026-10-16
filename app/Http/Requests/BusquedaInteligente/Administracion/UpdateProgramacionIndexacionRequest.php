<?php

namespace App\Http\Requests\BusquedaInteligente\Administracion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramacionIndexacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->can('configurar programacion indexaciones motor') ?? false;
    }

    public function rules(): array
    {
        return [
            'activo' => ['nullable', 'boolean'],
            'hora_ejecucion' => ['required', 'date_format:H:i'],
            'frecuencia' => ['required', Rule::in(['diario', 'semanal'])],
            'dia_semana' => ['nullable', 'required_if:frecuencia,semanal', 'integer', 'between:1,7'],
        ];
    }

    public function attributes(): array
    {
        return [
            'activo' => 'indexacion automatica',
            'hora_ejecucion' => 'hora de ejecucion',
            'frecuencia' => 'frecuencia',
            'dia_semana' => 'dia de la semana',
        ];
    }
}
