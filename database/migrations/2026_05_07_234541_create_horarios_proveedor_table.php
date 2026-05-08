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
        Schema::create('horarios_proveedor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perfil_proveedor_id')
                ->constrained('perfiles_proveedores')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('dia_semana'); // 1 lunes, 7 domingo
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();

            $table->string('tipo_atencion')->default('mixto'); // domicilio, local, remoto, mixto
            $table->boolean('disponible')->default(true);

            $table->timestamps();

            $table->unique([
                'perfil_proveedor_id',
                'dia_semana',
                'hora_inicio',
                'hora_fin',
            ], 'horarios_proveedor_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_proveedor');
    }
};
