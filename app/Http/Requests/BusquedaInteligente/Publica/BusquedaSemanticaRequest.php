<?php

namespace App\Http\Requests\BusquedaInteligente\Publica;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusquedaSemanticaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'consulta' => ['required', 'string', 'max:1000'],
            'disponible' => ['nullable', 'boolean'],
            'tipo_atencion' => ['nullable', Rule::in(['local', 'domicilio'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'consulta' => trim((string) $this->input('consulta', '')),
            'disponible' => $this->boolean('disponible') ? true : null,
            'tipo_atencion' => $this->filled('tipo_atencion') ? $this->input('tipo_atencion') : null,
        ]);
    }

    public function attributes(): array
    {
        return [
            'consulta' => 'descripcion del problema',
            'disponible' => 'disponibilidad',
            'tipo_atencion' => 'tipo de atencion',
        ];
    }
}
