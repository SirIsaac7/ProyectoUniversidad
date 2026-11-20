<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones_busqueda_visual', function (Blueprint $table) {
            $table->boolean('destacar_podio')->default(true)->after('color_destacado');
            $table->string('color_primer_lugar', 7)->default('#D4AF37')->after('destacar_podio');
            $table->string('color_segundo_lugar', 7)->default('#94A3B8')->after('color_primer_lugar');
            $table->string('color_tercer_lugar', 7)->default('#CD7F32')->after('color_segundo_lugar');
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones_busqueda_visual', function (Blueprint $table) {
            $table->dropColumn([
                'destacar_podio',
                'color_primer_lugar',
                'color_segundo_lugar',
                'color_tercer_lugar',
            ]);
        });
    }
};
