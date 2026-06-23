<?php

namespace App\Http\Requests\Admin\Reportes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfiguracionReporteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('configurar reportes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tamano_hoja' => ['required', Rule::in(array_keys(config('reportes.tamano_hoja', [])))],
            'orientacion' => ['required', Rule::in(array_keys(config('reportes.orientaciones', [])))],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quitar_logo' => ['nullable', 'boolean'],
            'color_principal' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'titulo_encabezado' => ['nullable', 'string', 'max:255'],
            'texto_pie' => ['nullable', 'string', 'max:255'],
            'mostrar_logo' => ['nullable', 'boolean'],
            'mostrar_fecha' => ['nullable', 'boolean'],
            'mostrar_generado_por' => ['nullable', 'boolean'],
        ];
    }
}
