<?php

namespace App\Http\Requests\MiPerfilProveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMiDocumentoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $perfilProveedorId = auth()->user()?->perfilProveedor?->id;
        $documentoProveedor = $this->route('documentoProveedor');

        return [
            'tipo_documento_proveedor_id' => [
                'required',
                'integer',
                Rule::exists('tipos_documento_proveedor', 'id')->where('estado', true),
                Rule::unique('documentos_proveedor', 'tipo_documento_proveedor_id')
                    ->where('perfil_proveedor_id', $perfilProveedorId)
                    ->where('estado', true)
                    ->ignore($documentoProveedor?->id),
            ],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
