<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StorePortafolioProveedorRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'fecha_trabajo' => ['nullable', 'date', 'before_or_equal:today'],
            'estado' => ['required', 'boolean'],
            'imagenes' => ['required', 'array', 'max:4'],
            'imagenes.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagenes_titulo' => ['nullable', 'array', 'max:4'],
            'imagenes_titulo.*' => ['nullable', 'string', 'max:255'],
            'imagenes_descripcion' => ['nullable', 'array', 'max:4'],
            'imagenes_descripcion.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $imagenes = $this->file('imagenes', []);

            $tieneImagen = collect($imagenes)->contains(function ($imagen) {
                return $imagen instanceof UploadedFile && $imagen->isValid();
            });

            if (! $tieneImagen) {
                $validator->errors()->add('imagenes', 'Debes seleccionar al menos una imagen del trabajo.');
            }
        });
    }
}
