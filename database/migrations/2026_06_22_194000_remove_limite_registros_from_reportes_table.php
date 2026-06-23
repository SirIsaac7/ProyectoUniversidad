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
        if (! Schema::hasColumn('reportes', 'limite_registros')) {
            return;
        }

        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn('limite_registros');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->unsignedSmallInteger('limite_registros')->default(18)->after('estado');
        });
    }
};
