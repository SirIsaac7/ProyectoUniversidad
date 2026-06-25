<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class BusquedaInteligenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'texto_problema' => ['required', 'string', 'min:10', 'max:1000'],
            'usar_ubicacion' => ['nullable', 'boolean'],
            'modo_clasificacion' => ['required', 'in:cnn,gemini'],
            'lat_cliente' => ['nullable', 'numeric', 'between:-90,90'],
            'lon_cliente' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'usar_ubicacion' => filter_var($this->input('usar_ubicacion', false), FILTER_VALIDATE_BOOLEAN),
            'modo_clasificacion' => $this->input('modo_clasificacion', 'cnn'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'imagen' => 'imagen del problema',
            'texto_problema' => 'descripcion del problema',
            'usar_ubicacion' => 'uso de ubicacion',
            'modo_clasificacion' => 'modo de clasificacion',
            'lat_cliente' => 'latitud del cliente',
            'lon_cliente' => 'longitud del cliente',
        ];
    }
}
