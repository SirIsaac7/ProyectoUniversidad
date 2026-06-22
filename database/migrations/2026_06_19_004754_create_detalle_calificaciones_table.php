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
        Schema::create('detalle_calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calificacion_id')
                ->constrained('calificaciones')
                ->cascadeOnDelete();

            $table->foreignId('aspecto_calificacion_id')
                ->constrained('aspectos_calificacion')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('puntuacion');
            $table->timestamps();
            $table->unique(
                ['calificacion_id', 'aspecto_calificacion_id'],
                'detalle_calificaciones_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_calificaciones');
    }
};
