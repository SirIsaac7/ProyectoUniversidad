<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePerfilProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('estado', true),
                Rule::unique('perfiles_proveedores', 'user_id'),
            ],
            'nombre_publico' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'foto_portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'anios_experiencia' => ['nullable', 'integer', 'min:0', 'max:80'],
            'estado_verificacion' => ['required', 'string', Rule::in(['pendiente', 'aprobado', 'rechazado'])],
            'motivo_rechazo' => ['nullable', 'required_if:estado_verificacion,rechazado', 'string', 'max:1000'],
            'estado' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $usuarioEsProveedor = User::whereKey($this->input('user_id'))
                ->whereHas('roles', fn ($query) => $query->where('name', 'PROVEEDOR'))
                ->exists();

            if (! $usuarioEsProveedor) {
                $validator->errors()->add('user_id', 'El usuario seleccionado debe tener el rol proveedor.');
            }
        });
    }
}
