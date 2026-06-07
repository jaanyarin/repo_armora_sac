<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_subclases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('clase_id');
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('slug', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('clase_id')
                ->references('id')
                ->on('products_clases')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('clase_id');
            $table->index('activo');
            $table->index('orden');
            $table->unique(['clase_id', 'nombre'], 'products_subclases_clase_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_subclases');
    }
};
