<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_busqueda_visual', function (Blueprint $table) {
            $table->id();
            $table->string('color_primario', 7)->default('#405189');
            $table->string('color_secundario', 7)->default('#6f5cff');
            $table->string('color_boton', 7)->default('#405189');
            $table->string('color_fondo', 7)->default('#f3f6f9');
            $table->string('color_indicador', 7)->default('#299cdb');
            $table->string('color_fase_completada', 7)->default('#0ab39c');
            $table->string('color_fase_actual', 7)->default('#f7b84b');
            $table->string('color_informativo', 7)->default('#299cdb');
            $table->string('color_error', 7)->default('#f06548');
            $table->string('color_destacado', 7)->default('#6f5cff');
            $table->string('familia_tipografica', 40)->default('system');
            $table->unsignedTinyInteger('tamano_titulos')->default(22);
            $table->unsignedTinyInteger('tamano_mensajes')->default(14);
            $table->unsignedSmallInteger('grosor_texto')->default(600);
            $table->string('animacion', 30)->default('spinner');
            $table->string('presentacion_fases', 30)->default('actual');
            $table->string('diseno_tarjetas', 30)->default('clasica');
            $table->unsignedTinyInteger('radio_bordes')->default(12);
            $table->string('espaciado', 20)->default('normal');
            $table->string('distribucion', 20)->default('estandar');
            $table->string('apariencia_calificacion', 20)->default('estrellas');
            $table->string('apariencia_explicacion', 20)->default('caja');
            $table->json('mensajes_fases');
            $table->timestamps();
        });

        Schema::create('ejecuciones_busqueda_motor', function (Blueprint $table) {
            $table->id();
            $table->string('fastapi_identificador')->unique();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->text('consulta');
            $table->json('filtros')->nullable();
            $table->string('estado')->default('pendiente');
            $table->string('categoria_estado')->default('pendiente');
            $table->string('fase_actual')->nullable();
            $table->text('detalle_fase')->nullable();
            $table->json('historial_fases')->nullable();
            $table->text('error')->nullable();
            $table->json('respuesta_inicio')->nullable();
            $table->json('ultima_respuesta')->nullable();
            $table->timestamp('iniciado_en')->nullable();
            $table->timestamp('finalizado_en')->nullable();
            $table->timestamp('resultados_recuperados_at')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'created_at']);
            $table->index(['usuario_id', 'categoria_estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejecuciones_busqueda_motor');
        Schema::dropIfExists('configuraciones_busqueda_visual');
    }
};
