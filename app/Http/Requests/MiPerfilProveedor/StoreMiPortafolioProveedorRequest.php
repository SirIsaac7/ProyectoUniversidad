<?php

namespace App\Http\Requests\MiPerfilProveedor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreMiPortafolioProveedorRequest extends FormRequest
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

            $tieneImagen = collect($imagenes)->contains(fn ($imagen) => $imagen instanceof UploadedFile && $imagen->isValid());

            if (! $tieneImagen) {
                $validator->errors()->add('imagenes', 'Debes seleccionar al menos una imagen del trabajo.');
            }
        });
    }
}
