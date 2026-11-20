<?php

namespace App\Http\Requests\Correos;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionCorreoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('editar configuracion correos') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre_marca' => ['required', 'string', 'max:100'],
            'color_primario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'file', 'image', 'mimes:png', 'mimetypes:image/png', 'max:5120'],

            'verificacion_asunto' => ['required', 'string', 'max:150'],
            'verificacion_titulo' => ['required', 'string', 'max:150'],
            'verificacion_mensaje' => ['required', 'string', 'max:1500'],
            'verificacion_boton' => ['required', 'string', 'max:60'],

            'recuperacion_asunto' => ['required', 'string', 'max:150'],
            'recuperacion_titulo' => ['required', 'string', 'max:150'],
            'recuperacion_mensaje' => ['required', 'string', 'max:1500'],
            'recuperacion_boton' => ['required', 'string', 'max:60'],

            'backup_destinatarios' => [
                'required',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, Closure $fail) {
                    $correos = preg_split('/[\s,;]+/', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);

                    if (empty($correos)) {
                        $fail('Debes indicar al menos una persona que reciba los avisos de las copias de seguridad.');

                        return;
                    }

                    if (count($correos) > 10) {
                        $fail('Puedes registrar hasta 10 destinatarios.');

                        return;
                    }

                    foreach ($correos as $correo) {
                        if (! filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                            $fail("El destinatario {$correo} no es un correo valido.");

                            return;
                        }
                    }
                },
            ],
            'backup_notificar_exito' => ['nullable', 'boolean'],
            'backup_notificar_error' => ['nullable', 'boolean'],
            'backup_adjuntar_archivo' => ['nullable', 'boolean'],
            'backup_asunto_exito' => ['required', 'string', 'max:150'],
            'backup_mensaje_exito' => ['required', 'string', 'max:1500'],
            'backup_asunto_error' => ['required', 'string', 'max:150'],
            'backup_mensaje_error' => ['required', 'string', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe contener texto.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'boolean' => 'El valor seleccionado para :attribute no es válido.',
            'color_primario.regex' => 'Selecciona un color válido.',
            'logo.file' => 'Debes seleccionar un archivo válido para el logotipo.',
            'logo.image' => 'El logotipo debe ser una imagen válida.',
            'logo.mimes' => 'El logotipo debe estar en formato PNG.',
            'logo.mimetypes' => 'El contenido del logotipo debe corresponder a una imagen PNG.',
            'logo.max' => 'El logotipo no debe superar los 5 MB.',
            'backup_destinatarios.required' => 'Debes indicar al menos una persona que reciba los avisos de las copias de seguridad.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_marca' => 'nombre que verá el destinatario',
            'color_primario' => 'color principal',
            'logo' => 'logotipo',
            'verificacion_asunto' => 'asunto de verificación',
            'verificacion_titulo' => 'título de verificación',
            'verificacion_mensaje' => 'mensaje de verificación',
            'verificacion_boton' => 'texto del botón de verificación',
            'recuperacion_asunto' => 'asunto de recuperación',
            'recuperacion_titulo' => 'título de recuperación',
            'recuperacion_mensaje' => 'mensaje de recuperación',
            'recuperacion_boton' => 'texto del botón de recuperación',
            'backup_destinatarios' => 'personas que recibirán los avisos',
            'backup_notificar_exito' => 'aviso de resultado correcto',
            'backup_notificar_error' => 'aviso de resultado con error',
            'backup_adjuntar_archivo' => 'envío del archivo de la base de datos',
            'backup_asunto_exito' => 'asunto del resultado correcto',
            'backup_mensaje_exito' => 'mensaje del resultado correcto',
            'backup_asunto_error' => 'asunto del resultado con error',
            'backup_mensaje_error' => 'mensaje del resultado con error',
        ];
    }
}
