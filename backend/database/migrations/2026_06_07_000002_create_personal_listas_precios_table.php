<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_listas_precios', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('lista_precio_id');
            $table->timestamps();

            $table->primary(['user_id', 'lista_precio_id']);
            $table->foreign('lista_precio_id')->references('id')->on('dim_lista_precios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_listas_precios');
    }
};
