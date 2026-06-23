<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion_backups', function (Blueprint $table) {
            $table->id();
            $table->time('hora_ejecucion')->default('02:00:00');
            $table->string('frecuencia')->default('diario'); // diario, semanal, mensual
            $table->unsignedTinyInteger('dia_semana')->nullable(); // 1 lunes - 7 domingo
            $table->unsignedTinyInteger('dia_mes')->nullable(); // 1 - 28
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_backup_at')->nullable();
            $table->string('ultimo_estado')->nullable();
            $table->text('ultimo_mensaje')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_backups');
    }
};
