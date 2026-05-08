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
        Schema::create('proveedor_especialidad', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perfil_proveedor_id')
                ->constrained('perfiles_proveedores')
                ->cascadeOnDelete();

            $table->foreignId('especialidad_id')
                ->constrained('especialidades')
                ->cascadeOnDelete();

            $table->boolean('es_principal')->default(false);
            $table->boolean('estado')->default(true);

            $table->timestamps();

            $table->unique([
                'perfil_proveedor_id',
                'especialidad_id',
            ], 'proveedor_especialidad_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedor_especialidad');
    }
};
