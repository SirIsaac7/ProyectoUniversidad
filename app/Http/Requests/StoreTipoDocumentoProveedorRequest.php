<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTipoDocumentoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:tipos_documento_proveedor,nombre'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'obligatorio' => ['required', 'boolean'],
            'estado' => ['required', 'boolean'],
        ];
    }
}
