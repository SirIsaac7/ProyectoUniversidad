<?php

namespace App\Services\Correos;

use App\Models\ConfiguracionCorreo;
use App\Notifications\Correos\ResultadoBackupNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class ConfiguracionCorreoService
{
    public function configuracion(): ConfiguracionCorreo
    {
        return ConfiguracionCorreo::firstOrCreate(
            ['id' => 1],
            $this->valoresPredeterminados()
        );
    }

    public function actualizar(array $data): ConfiguracionCorreo
    {
        $configuracion = $this->configuracion();
        $destinatarios = preg_split('/[\s,;]+/', trim($data['backup_destinatarios']), -1, PREG_SPLIT_NO_EMPTY);
        $logoAnterior = $configuracion->logo_path;
        $logoPath = $this->guardarLogo($data['logo'] ?? null);

        $configuracion->update([
            'nombre_marca' => $data['nombre_marca'],
            'color_primario' => strtoupper($data['color_primario']),
            'logo_path' => $logoPath ?? $logoAnterior,
            'verificacion_asunto' => $data['verificacion_asunto'],
            'verificacion_titulo' => $data['verificacion_titulo'],
            'verificacion_mensaje' => $data['verificacion_mensaje'],
            'verificacion_boton' => $data['verificacion_boton'],
            'recuperacion_asunto' => $data['recuperacion_asunto'],
            'recuperacion_titulo' => $data['recuperacion_titulo'],
            'recuperacion_mensaje' => $data['recuperacion_mensaje'],
            'recuperacion_boton' => $data['recuperacion_boton'],
            'backup_destinatarios' => array_values(array_unique(array_map('mb_strtolower', $destinatarios))),
            'backup_notificar_exito' => (bool) ($data['backup_notificar_exito'] ?? false),
            'backup_notificar_error' => (bool) ($data['backup_notificar_error'] ?? false),
            'backup_adjuntar_archivo' => (bool) ($data['backup_adjuntar_archivo'] ?? false),
            'backup_asunto_exito' => $data['backup_asunto_exito'],
            'backup_mensaje_exito' => $data['backup_mensaje_exito'],
            'backup_asunto_error' => $data['backup_asunto_error'],
            'backup_mensaje_error' => $data['backup_mensaje_error'],
        ]);

        if ($logoPath && $logoAnterior && $logoAnterior !== $logoPath) {
            $this->eliminarArchivoPublico($logoAnterior);
        }

        return $configuracion->refresh();
    }

    public function correoVerificacion(object $usuario, string $url): MailMessage
    {
        $configuracion = $this->configuracion();

        return $this->crearMensaje(
            configuracion: $configuracion,
            asunto: $this->reemplazar($configuracion->verificacion_asunto, $usuario),
            titulo: $this->reemplazar($configuracion->verificacion_titulo, $usuario),
            mensaje: $this->reemplazar($configuracion->verificacion_mensaje, $usuario),
            textoBoton: $configuracion->verificacion_boton,
            urlBoton: $url
        );
    }

    public function correoRecuperacion(object $usuario, string $url): MailMessage
    {
        $configuracion = $this->configuracion();
        $extra = [
            '{minutos}' => (string) config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
        ];

        return $this->crearMensaje(
            configuracion: $configuracion,
            asunto: $this->reemplazar($configuracion->recuperacion_asunto, $usuario, $extra),
            titulo: $this->reemplazar($configuracion->recuperacion_titulo, $usuario, $extra),
            mensaje: $this->reemplazar($configuracion->recuperacion_mensaje, $usuario, $extra),
            textoBoton: $configuracion->recuperacion_boton,
            urlBoton: $url
        );
    }

    public function correoResultadoBackup(
        bool $exitoso,
        string $detalle,
        string $fecha,
        ?string $rutaArchivo = null
    ): MailMessage {
        $configuracion = $this->configuracion();
        $extra = [
            '{fecha}' => $fecha,
            '{detalle}' => $detalle,
            '{estado}' => $exitoso ? 'correcto' : 'error',
        ];
        $asunto = $exitoso ? $configuracion->backup_asunto_exito : $configuracion->backup_asunto_error;
        $mensaje = $exitoso ? $configuracion->backup_mensaje_exito : $configuracion->backup_mensaje_error;

        $correo = $this->crearMensaje(
            configuracion: $configuracion,
            asunto: $this->reemplazar($asunto, null, $extra),
            titulo: $this->reemplazar($asunto, null, $extra),
            mensaje: $this->reemplazar($mensaje, null, $extra)
        );

        if ($exitoso && $rutaArchivo && is_file($rutaArchivo)) {
            $correo->attach($rutaArchivo, [
                'as' => basename($rutaArchivo),
                'mime' => 'application/zip',
            ]);
        }

        return $correo;
    }

    public function notificarResultadoBackup(
        bool $exitoso,
        string $detalle,
        ?string $rutaArchivo = null
    ): void {
        try {
            $detalle = Str::limit($detalle, 2000);
            $configuracion = $this->configuracion();
            $habilitado = $exitoso
                ? $configuracion->backup_notificar_exito
                : $configuracion->backup_notificar_error;
            $destinatarios = array_values(array_filter($configuracion->backup_destinatarios ?? []));

            if (! $habilitado || empty($destinatarios)) {
                return;
            }

            Notification::route('mail', $destinatarios)->notify(
                new ResultadoBackupNotification(
                    $exitoso,
                    $detalle,
                    now()->format('d/m/Y H:i'),
                    $exitoso && $configuracion->backup_adjuntar_archivo ? $rutaArchivo : null
                )
            );
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar el correo del resultado del backup.', [
                'error' => $e->getMessage(),
                'backup_exitoso' => $exitoso,
            ]);
        }
    }

    private function crearMensaje(
        ConfiguracionCorreo $configuracion,
        string $asunto,
        string $titulo,
        string $mensaje,
        ?string $textoBoton = null,
        ?string $urlBoton = null
    ): MailMessage {
        return (new MailMessage)
            ->subject($asunto)
            ->view('correos.emails.sistema', [
                'nombreMarca' => $configuracion->nombre_marca,
                'colorPrimario' => $configuracion->color_primario,
                'logoUrl' => $configuracion->logo_path ? asset($configuracion->logo_path) : null,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'textoBoton' => $textoBoton,
                'urlBoton' => $urlBoton,
            ]);
    }

    private function reemplazar(string $texto, ?object $usuario = null, array $extra = []): string
    {
        return strtr($texto, [
            '{nombre}' => (string) ($usuario->name ?? 'usuario'),
            '{aplicacion}' => (string) config('app.name'),
            ...$extra,
        ]);
    }

    private function valoresPredeterminados(): array
    {
        return [
            'nombre_marca' => config('app.name', 'Proyecto Integrador'),
            'color_primario' => '#405189',
            'logo_path' => null,
            'verificacion_asunto' => 'Verifica tu correo electronico',
            'verificacion_titulo' => 'Verifica tu correo electronico',
            'verificacion_mensaje' => 'Hola {nombre}, confirma tu direccion de correo para completar el acceso a {aplicacion}.',
            'verificacion_boton' => 'Verificar correo',
            'recuperacion_asunto' => 'Restablece tu contrasena',
            'recuperacion_titulo' => 'Restablecimiento de contrasena',
            'recuperacion_mensaje' => 'Hola {nombre}, recibimos una solicitud para restablecer la contrasena de tu cuenta. Este enlace vencera en {minutos} minutos.',
            'recuperacion_boton' => 'Restablecer contrasena',
            'backup_destinatarios' => array_values(array_filter([config('mail.from.address')])),
            'backup_notificar_exito' => true,
            'backup_notificar_error' => true,
            'backup_adjuntar_archivo' => false,
            'backup_asunto_exito' => 'Backup completado correctamente',
            'backup_mensaje_exito' => 'El backup de {aplicacion} finalizo correctamente el {fecha}.',
            'backup_asunto_error' => 'Error al ejecutar el backup',
            'backup_mensaje_error' => 'El backup de {aplicacion} fallo el {fecha}. Detalle: {detalle}',
        ];
    }

    private function guardarLogo(mixed $archivo): ?string
    {
        if (! $archivo) {
            return null;
        }

        $directorio = public_path('uploads/correos/logos');

        if (! File::exists($directorio)) {
            File::makeDirectory($directorio, 0755, true);
        }

        $nombre = Str::uuid().'.png';
        $archivo->move($directorio, $nombre);

        return 'uploads/correos/logos/'.$nombre;
    }

    private function eliminarArchivoPublico(?string $ruta): void
    {
        if (! $ruta) {
            return;
        }

        $rutaAbsoluta = public_path(ltrim($ruta, '/'));

        if (File::exists($rutaAbsoluta)) {
            File::delete($rutaAbsoluta);
        }
    }
}
