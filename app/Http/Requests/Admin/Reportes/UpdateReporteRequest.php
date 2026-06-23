<?php

namespace App\Http\Requests\Admin\Reportes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReporteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('editar reportes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(array_keys(config('reportes.tipos', [])))],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'incluir_graficas' => ['nullable', 'boolean'],
            'incluir_imagenes' => ['nullable', 'boolean'],
            'estado' => ['required', 'boolean'],
            'opciones' => ['nullable', 'array'],
            'opciones.*' => ['nullable'],
        ];
    }
}
