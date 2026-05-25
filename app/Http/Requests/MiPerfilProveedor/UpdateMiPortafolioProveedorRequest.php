<?php

namespace App\Http\Requests\MiPerfilProveedor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMiPortafolioProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'fecha_trabajo' => ['nullable', 'date', 'before_or_equal:today'],
            'imagenes' => ['nullable', 'array', 'max:4'],
            'imagenes.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagenes_titulo' => ['nullable', 'array', 'max:4'],
            'imagenes_titulo.*' => ['nullable', 'string', 'max:255'],
            'imagenes_descripcion' => ['nullable', 'array', 'max:4'],
            'imagenes_descripcion.*' => ['nullable', 'string', 'max:1000'],
            'imagenes_existentes' => ['nullable', 'array'],
            'imagenes_existentes.*.titulo' => ['nullable', 'string', 'max:255'],
            'imagenes_existentes.*.descripcion' => ['nullable', 'string', 'max:1000'],
            'imagenes_existentes.*.estado' => ['nullable', 'boolean'],
        ];
    }
}
