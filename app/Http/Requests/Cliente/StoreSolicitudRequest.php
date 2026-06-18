<?php

namespace App\Http\Requests\Cliente;

use App\Models\PerfilProveedor;
use App\Models\ProveedorEspecialidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSolicitudRequest extends FormRequest
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
            'especialidad_id' => [
                'required',
                'integer',
                Rule::exists('especialidades', 'id')->where('estado', true),
            ],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'tipo_atencion' => ['required', Rule::in(['mixto', 'domicilio', 'local', 'remoto'])],
            'direccion' => ['nullable', 'string', 'max:255'],
            'zona' => ['required_if:tipo_atencion,domicilio', 'nullable', 'string', 'max:255'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'fecha_solicitada' => ['nullable', 'date', 'after_or_equal:today'],
            'hora_solicitada' => ['nullable', 'date_format:H:i'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('perfil_proveedor_id') || ! $this->filled('especialidad_id')) {
                return;
            }

            $perfilProveedor = PerfilProveedor::find($this->input('perfil_proveedor_id'));

            if ($perfilProveedor && (int) $perfilProveedor->user_id === (int) $this->user()->id) {
                $validator->errors()->add('perfil_proveedor_id', 'No puedes solicitarte un servicio a ti mismo.');
            }

            $especialidadAsignada = ProveedorEspecialidad::where('perfil_proveedor_id', $this->input('perfil_proveedor_id'))
                ->where('especialidad_id', $this->input('especialidad_id'))
                ->where('estado', true)
                ->exists();
            if (! $especialidadAsignada) {

                $validator->errors()->add('especialidad_id', 'La especialidad seleccionada no pertenece al proveedor.');
            }
        });
    }
}
