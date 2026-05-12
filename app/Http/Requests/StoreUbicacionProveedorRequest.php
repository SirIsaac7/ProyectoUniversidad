<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUbicacionProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perfil_proveedor_id' => [
                'required',
                'integer',
                Rule::exists('perfiles_proveedores', 'id')->where('estado', true),
                Rule::unique('ubicaciones_proveedor', 'perfil_proveedor_id'),
            ],
            'zona' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'radio_cobertura_km' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }
}
