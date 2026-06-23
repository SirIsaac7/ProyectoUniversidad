<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBackupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('configurar backups');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hora_ejecucion' => ['required', 'date_format:H:i'],
            'frecuencia' => ['required', Rule::in(['diario', 'semanal', 'mensual'])],
            'dia_semana' => ['nullable', 'required_if:frecuencia,semanal', 'integer', 'between:1,7'],
            'dia_mes' => ['nullable', 'required_if:frecuencia,mensual', 'integer', 'between:1,28'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
