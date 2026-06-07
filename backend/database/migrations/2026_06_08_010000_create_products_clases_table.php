<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_clases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->string('slug', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('licor')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('activo');
            $table->index('orden');
            $table->index('licor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_clases');
    }
};
