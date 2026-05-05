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
       Schema::rename('categoria_servicios', 'rubros');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('rubros', 'categoria_servicios');
    }
};
