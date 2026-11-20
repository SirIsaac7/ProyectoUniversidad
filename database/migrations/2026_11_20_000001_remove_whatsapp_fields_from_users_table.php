<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('users', 'celular_verificado_at') ? 'celular_verificado_at' : null,
            Schema::hasColumn('users', 'recibe_notificaciones_whatsapp') ? 'recibe_notificaciones_whatsapp' : null,
        ]));

        if ($columns !== []) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'celular_verificado_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('celular_verificado_at')->nullable()->after('celular');
            });
        }

        if (! Schema::hasColumn('users', 'recibe_notificaciones_whatsapp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('recibe_notificaciones_whatsapp')->default(false)->after('celular_verificado_at');
            });
        }
    }
};
