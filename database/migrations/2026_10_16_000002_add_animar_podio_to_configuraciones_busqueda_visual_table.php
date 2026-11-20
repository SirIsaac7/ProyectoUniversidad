<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones_busqueda_visual', function (Blueprint $table) {
            $table->boolean('animar_podio')->default(true)->after('destacar_podio');
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones_busqueda_visual', function (Blueprint $table) {
            $table->dropColumn('animar_podio');
        });
    }
};
