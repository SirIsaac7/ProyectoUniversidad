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
        Schema::table('users', function (Blueprint $table) {
            $table->string('celular', 20)->nullable()->after('email');
            $table->timestamp('celular_verificado_at')->nullable()->after('celular');
            $table->boolean('recibe_notificaciones_whatsapp')->default(false)->after('celular_verificado_at');
            $table->date('fecha_nacimiento')->nullable()->after('recibe_notificaciones_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'celular',
                'celular_verificado_at',
                'recibe_notificaciones_whatsapp',
                'fecha_nacimiento',
            ]);
        });
    }
};
