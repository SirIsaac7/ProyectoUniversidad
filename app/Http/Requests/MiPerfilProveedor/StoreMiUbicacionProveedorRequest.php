<?php

namespace App\Http\Requests\MiPerfilProveedor;

use Illuminate\Foundation\Http\FormRequest;

class StoreMiUbicacionProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zona' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'radio_cobertura_km' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}
