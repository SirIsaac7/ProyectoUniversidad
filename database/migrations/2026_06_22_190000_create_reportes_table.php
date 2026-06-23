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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('tipo');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('incluir_graficas')->default(true);
            $table->boolean('incluir_imagenes')->default(true);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
