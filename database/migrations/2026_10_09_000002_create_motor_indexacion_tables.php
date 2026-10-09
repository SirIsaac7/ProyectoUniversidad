<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_indexacion_motor', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(false);
            $table->string('frecuencia')->default('diario');
            $table->time('hora_ejecucion')->default('02:00:00');
            $table->unsignedTinyInteger('dia_semana')->nullable();
            $table->string('zona_horaria')->default('America/La_Paz');
            $table->timestamp('ultima_ejecucion_programada_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ejecuciones_indexacion_motor', function (Blueprint $table) {
            $table->id();
            $table->string('fastapi_identificador')->nullable()->unique();
            $table->string('tipo', 20);
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado')->default('solicitando');
            $table->string('categoria_estado')->default('pendiente');
            $table->string('fase_actual')->nullable();
            $table->text('descripcion_fase')->nullable();
            $table->timestamp('iniciado_en')->nullable();
            $table->timestamp('finalizado_en')->nullable();
            $table->unsignedInteger('total_proveedores')->nullable();
            $table->unsignedInteger('proveedores_procesados')->nullable();
            $table->unsignedInteger('proveedores_nuevos')->nullable();
            $table->unsignedInteger('proveedores_reindexados')->nullable();
            $table->unsignedInteger('metadatos_actualizados')->nullable();
            $table->unsignedInteger('proveedores_sin_modificaciones')->nullable();
            $table->unsignedInteger('proveedores_eliminados')->nullable();
            $table->json('errores')->nullable();
            $table->json('fases')->nullable();
            $table->json('respuesta_inicio')->nullable();
            $table->json('ultima_respuesta')->nullable();
            $table->text('ultimo_error_seguimiento')->nullable();
            $table->unsignedInteger('intentos_seguimiento')->default(0);
            $table->timestamp('ultimo_seguimiento_at')->nullable();
            $table->timestamps();

            $table->index(['categoria_estado', 'created_at']);
            $table->index(['tipo', 'created_at']);
            $table->index('iniciado_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejecuciones_indexacion_motor');
        Schema::dropIfExists('configuraciones_indexacion_motor');
    }
};
