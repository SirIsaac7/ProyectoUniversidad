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
        Schema::create('ubicaciones_proveedor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perfil_proveedor_id')
                ->unique()
                ->constrained('perfiles_proveedores')
                ->cascadeOnDelete();

            $table->string('zona')->nullable();
            $table->string('direccion')->nullable();

            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->unsignedSmallInteger('radio_cobertura_km')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_proveedor');
    }
};
