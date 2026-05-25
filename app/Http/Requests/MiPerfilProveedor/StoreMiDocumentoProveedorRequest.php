<?php

namespace App\Http\Requests\MiPerfilProveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMiDocumentoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $perfilProveedorId = auth()->user()?->perfilProveedor?->id;

        return [
            'tipo_documento_proveedor_id' => [
                'required',
                'integer',
                Rule::exists('tipos_documento_proveedor', 'id')->where('estado', true),
                Rule::unique('documentos_proveedor', 'tipo_documento_proveedor_id')
                    ->where('perfil_proveedor_id', $perfilProveedorId)
                    ->where('estado', true),
            ],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento_proveedor_id.unique' => 'Ya subiste un documento de este tipo.',
        ];
    }
}
