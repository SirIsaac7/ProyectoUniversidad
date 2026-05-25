<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_proveedor', function (Blueprint $table) {
            $table->boolean('estado')->default(true)->after('disponible');
        });

        Schema::table('documentos_proveedor', function (Blueprint $table) {
            $table->boolean('estado')->default(true)->after('fecha_revision');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_proveedor', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        Schema::table('horarios_proveedor', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
