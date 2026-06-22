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
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')
                ->unique()
                ->constrained('citas')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('puntuacion');
            $table->text('comentario')->nullable();
            $table->string('estado')->default('visible'); // visible, oculta, pendiente_revision
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
