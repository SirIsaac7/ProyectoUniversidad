<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_correo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_marca')->default('Proyecto Integrador');
            $table->string('color_primario', 7)->default('#405189');
            $table->string('texto_pie')->default('Este es un correo automatico. Por favor, no respondas a este mensaje.');

            $table->string('verificacion_asunto')->default('Verifica tu correo electronico');
            $table->string('verificacion_titulo')->default('Verifica tu correo electronico');
            $table->text('verificacion_mensaje');
            $table->string('verificacion_boton')->default('Verificar correo');

            $table->string('recuperacion_asunto')->default('Restablece tu contrasena');
            $table->string('recuperacion_titulo')->default('Restablecimiento de contrasena');
            $table->text('recuperacion_mensaje');
            $table->string('recuperacion_boton')->default('Restablecer contrasena');

            $table->json('backup_destinatarios')->nullable();
            $table->boolean('backup_notificar_exito')->default(true);
            $table->boolean('backup_notificar_error')->default(true);
            $table->string('backup_asunto_exito')->default('Backup completado correctamente');
            $table->text('backup_mensaje_exito');
            $table->string('backup_asunto_error')->default('Error al ejecutar el backup');
            $table->text('backup_mensaje_error');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_correo');
    }
};
