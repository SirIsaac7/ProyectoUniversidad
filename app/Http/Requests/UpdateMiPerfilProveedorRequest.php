<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMiPerfilProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_publico' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'foto_portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'anios_experiencia' => ['nullable', 'integer', 'min:0', 'max:80'],
        ];
    }
}
