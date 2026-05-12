<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portafolio_proveedor_imagenes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('portafolio_proveedor_id')
                ->constrained('portafolio_proveedor')
                ->cascadeOnDelete();

            $table->string('imagen');
            $table->string('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);

            $table->timestamps();
        });

        if (Schema::hasColumn('portafolio_proveedor', 'imagen')) {
            DB::table('portafolio_proveedor')
                ->whereNotNull('imagen')
                ->where('imagen', '<>', '')
                ->orderBy('id')
                ->get(['id', 'imagen', 'created_at', 'updated_at'])
                ->each(function ($portafolio) {
                    DB::table('portafolio_proveedor_imagenes')->insert([
                        'portafolio_proveedor_id' => $portafolio->id,
                        'imagen' => $portafolio->imagen,
                        'titulo' => 'Imagen principal',
                        'descripcion' => null,
                        'estado' => true,
                        'created_at' => $portafolio->created_at,
                        'updated_at' => $portafolio->updated_at,
                    ]);
                });

            Schema::table('portafolio_proveedor', function (Blueprint $table) {
                $table->dropColumn('imagen');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('portafolio_proveedor', 'imagen')) {
            Schema::table('portafolio_proveedor', function (Blueprint $table) {
                $table->string('imagen')->nullable()->after('descripcion');
            });
        }

        if (Schema::hasTable('portafolio_proveedor_imagenes')) {
            DB::table('portafolio_proveedor_imagenes')
                ->where('estado', true)
                ->orderBy('id')
                ->get(['portafolio_proveedor_id', 'imagen'])
                ->unique('portafolio_proveedor_id')
                ->each(function ($imagen) {
                    DB::table('portafolio_proveedor')
                        ->where('id', $imagen->portafolio_proveedor_id)
                        ->update(['imagen' => $imagen->imagen]);
                });
        }

        Schema::dropIfExists('portafolio_proveedor_imagenes');
    }
};
