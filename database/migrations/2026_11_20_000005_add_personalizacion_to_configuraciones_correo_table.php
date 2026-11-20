<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones_correo', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('color_primario');
            $table->boolean('backup_adjuntar_archivo')
                ->default(false)
                ->after('backup_notificar_error');
            $table->dropColumn('texto_pie');
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones_correo', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'backup_adjuntar_archivo']);
            $table->string('texto_pie')
                ->default('Este es un correo automatico. Por favor, no respondas a este mensaje.')
                ->after('color_primario');
        });
    }
};
