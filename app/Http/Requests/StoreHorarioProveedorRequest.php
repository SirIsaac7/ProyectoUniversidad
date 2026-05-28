<?php

namespace App\Http\Requests;

use App\Models\HorarioProveedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHorarioProveedorRequest extends FormRequest
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
            ],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => [
                'required',
                'date_format:H:i',
                Rule::unique('horarios_proveedor', 'hora_inicio')
                    ->where('perfil_proveedor_id', $this->input('perfil_proveedor_id'))
                    ->where('dia_semana', $this->input('dia_semana'))
                    ->where('hora_fin', $this->input('hora_fin')),
            ],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'tipo_atencion' => ['required', 'string', Rule::in(['domicilio', 'local', 'remoto', 'mixto'])],
            'disponible' => ['required', 'boolean'],
            'estado' => ['required', 'boolean'],
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
