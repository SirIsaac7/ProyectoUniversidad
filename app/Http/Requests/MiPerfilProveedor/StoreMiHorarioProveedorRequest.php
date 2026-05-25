<?php

namespace App\Http\Requests\MiPerfilProveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMiHorarioProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $perfilProveedorId = auth()->user()?->perfilProveedor?->id;

        return [
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => [
                'required',
                'date_format:H:i',
                Rule::unique('horarios_proveedor', 'hora_inicio')
                    ->where('perfil_proveedor_id', $perfilProveedorId)
                    ->where('dia_semana', $this->input('dia_semana'))
                    ->where('hora_fin', $this->input('hora_fin')),
            ],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_atencion' => ['required', Rule::in(['domicilio', 'local', 'remoto', 'mixto'])],
            'disponible' => ['required', 'boolean'],
        ];
    }
}
