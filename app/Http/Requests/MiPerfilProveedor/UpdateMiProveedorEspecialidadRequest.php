<?php

namespace App\Http\Requests\MiPerfilProveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMiProveedorEspecialidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $perfilProveedorId = auth()->user()?->perfilProveedor?->id;
        $proveedorEspecialidad = $this->route('proveedorEspecialidad');

        return [
            'especialidad_id' => [
                'required',
                'integer',
                Rule::exists('especialidades', 'id')->where('estado', true),
                Rule::unique('proveedor_especialidad', 'especialidad_id')
                    ->where('perfil_proveedor_id', $perfilProveedorId)
                    ->where('estado', true)
                    ->ignore($proveedorEspecialidad?->id),
            ],
            'es_principal' => ['required', 'boolean'],
        ];
    }
}
