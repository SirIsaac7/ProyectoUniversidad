<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentoProveedor = $this->route('documentos_proveedor');

        return [
            'perfil_proveedor_id' => [
                'required',
                'integer',
                Rule::exists('perfiles_proveedores', 'id'),
            ],
            'tipo_documento_proveedor_id' => [
                'required',
                'integer',
                Rule::exists('tipos_documento_proveedor', 'id'),
                Rule::unique('documentos_proveedor', 'tipo_documento_proveedor_id')
                    ->where('perfil_proveedor_id', $this->input('perfil_proveedor_id'))
                    ->where('estado', true)
                    ->ignore($documentoProveedor?->id),
            ],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'estado_revision' => ['required', Rule::in(['pendiente', 'aprobado', 'rechazado'])],
            'observacion' => ['nullable', 'required_if:estado_revision,rechazado', 'string', 'max:1000'],
            'estado' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento_proveedor_id.unique' => 'Este proveedor ya tiene registrado ese tipo de documento.',
            'observacion.required_if' => 'La observacion es obligatoria cuando el documento esta rechazado.',
        ];
    }
}
