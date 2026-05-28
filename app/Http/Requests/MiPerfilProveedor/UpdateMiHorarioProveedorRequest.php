<?php

namespace App\Http\Requests\MiPerfilProveedor;

use App\Models\HorarioProveedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMiHorarioProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $perfilProveedorId = auth()->user()?->perfilProveedor?->id;
        $horarioProveedor = $this->route('horarioProveedor');

        return [
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => [
                'required',
                'date_format:H:i',
                Rule::unique('horarios_proveedor', 'hora_inicio')
                    ->where('perfil_proveedor_id', $perfilProveedorId)
                    ->where('dia_semana', $this->input('dia_semana'))
                    ->where('hora_fin', $this->input('hora_fin'))
                    ->ignore($horarioProveedor?->id),
            ],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_atencion' => ['required', Rule::in(['domicilio', 'local', 'remoto', 'mixto'])],
            'disponible' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $perfilProveedorId = $this->input('perfil_proveedor_id') ?? auth()->user()?->perfilProveedor?->id;

            if (
                ! $perfilProveedorId ||
                ! $this->filled('dia_semana') ||
                ! $this->filled('hora_inicio') ||
                ! $this->filled('hora_fin') ||
                $this->input('disponible') == '0'
            ) {
                return;
            }

            $query = HorarioProveedor::where('perfil_proveedor_id', $perfilProveedorId)
                ->where('dia_semana', $this->input('dia_semana'))
                ->where('estado', true)
                ->where('disponible', true)
                ->where('hora_inicio', '<', $this->input('hora_fin'))
                ->where('hora_fin', '>', $this->input('hora_inicio'));

            if ($horarioProveedor = $this->route('horarioProveedor') ?? $this->route('horarios_proveedor')) {
                $query->where('id', '!=', is_object($horarioProveedor) ? $horarioProveedor->id : $horarioProveedor);
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    'hora_inicio',
                    'El horario se cruza con otro horario registrado para este dia.'
                );
            }
        });
    }
}
