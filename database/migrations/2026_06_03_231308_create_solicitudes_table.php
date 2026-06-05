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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('perfil_proveedor_id')
                ->constrained('perfiles_proveedores')
                ->restrictOnDelete();

            $table->foreignId('especialidad_id')
                ->constrained('especialidades')
                ->restrictOnDelete();

            $table->string('titulo');
            $table->text('descripcion');
            $table->string('tipo_atencion')->default('mixto'); // domicilio, local, remoto, mixto

            $table->string('direccion')->nullable();
            $table->string('zona')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            $table->date('fecha_solicitada')->nullable();
            $table->time('hora_solicitada')->nullable();

            $table->string('estado')->default('pendiente'); // pendiente, aceptada, rechazada, cancelada, en_proceso, finalizada
            $table->text('motivo_cancelacion')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
