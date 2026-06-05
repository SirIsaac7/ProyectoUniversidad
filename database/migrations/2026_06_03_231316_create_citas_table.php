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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                ->unique()
                ->constrained('solicitudes')
                ->cascadeOnDelete();

            $table->date('fecha_cita');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->string('estado')->default('programada'); // programada, reprogramada, en_camino, en_atencion, completada, cancelada, no_asistio
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
