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
        Schema::create('documentos_proveedor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perfil_proveedor_id')
                ->constrained('perfiles_proveedores')
                ->cascadeOnDelete();

            $table->foreignId('tipo_documento_proveedor_id')
                ->constrained('tipos_documento_proveedor')
                ->restrictOnDelete();

            $table->string('archivo');
            $table->string('estado_revision')->default('pendiente');
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_revision')->nullable();

            $table->timestamps();

            $table->unique([
                'perfil_proveedor_id',
                'tipo_documento_proveedor_id',
            ], 'documentos_proveedor_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_proveedor');
    }
};
