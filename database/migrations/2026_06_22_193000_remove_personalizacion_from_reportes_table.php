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
        if (! Schema::hasColumn('reportes', 'tamano_hoja')) {
            return;
        }

        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn([
                'tamano_hoja',
                'orientacion',
                'logo_path',
                'color_principal',
                'titulo_encabezado',
                'texto_pie',
                'mostrar_logo',
                'mostrar_fecha',
                'mostrar_generado_por',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->string('tamano_hoja', 20)->default('letter')->after('estado');
            $table->string('orientacion', 20)->default('portrait')->after('tamano_hoja');
            $table->string('logo_path')->nullable()->after('orientacion');
            $table->string('color_principal', 20)->default('#635bff')->after('logo_path');
            $table->string('titulo_encabezado')->nullable()->after('color_principal');
            $table->string('texto_pie')->nullable()->after('titulo_encabezado');
            $table->boolean('mostrar_logo')->default(true)->after('texto_pie');
            $table->boolean('mostrar_fecha')->default(true)->after('mostrar_logo');
            $table->boolean('mostrar_generado_por')->default(true)->after('mostrar_fecha');
        });
    }
};
