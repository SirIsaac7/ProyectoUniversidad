<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoDocumentoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoDocumentoProveedor = $this->route('tipos_documento_proveedor');

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipos_documento_proveedor', 'nombre')->ignore($tipoDocumentoProveedor?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'obligatorio' => ['required', 'boolean'],
            'estado' => ['required', 'boolean'],
        ];
    }
}
