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
        Schema::create('configuracion_reportes', function (Blueprint $table) {
            $table->id();
            $table->string('tamano_hoja', 20)->default('letter');
            $table->string('orientacion', 20)->default('portrait');
            $table->string('logo_path')->nullable();
            $table->string('color_principal', 20)->default('#635bff');
            $table->string('titulo_encabezado')->nullable();
            $table->string('texto_pie')->nullable();
            $table->boolean('mostrar_logo')->default(true);
            $table->boolean('mostrar_fecha')->default(true);
            $table->boolean('mostrar_generado_por')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_reportes');
    }
};
