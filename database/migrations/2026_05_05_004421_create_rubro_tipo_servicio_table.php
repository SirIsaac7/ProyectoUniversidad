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
        Schema::create('rubro_tipo_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubro_id')->constrained('rubros')->cascadeOnDelete();
            $table->foreignId('tipo_servicio_id')->constrained('tipos_servicio')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->unique(['rubro_id', 'tipo_servicio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubro_tipo_servicio');
    }
};
