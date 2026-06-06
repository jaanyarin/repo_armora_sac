<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'documento_identidad_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('documento_identidad_id')->nullable()->after('documento_tipo_id')
                    ->constrained('dim_documento_identidad');
            });
        }

        if (Schema::hasColumn('users', 'documento_tipo_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['documento_tipo_id']);
                $table->dropColumn('documento_tipo_id');
            });
        }
        if (Schema::hasColumn('users', 'dni')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['dni']);
                $table->dropColumn('dni');
            });
        }
        if (Schema::hasColumn('users', 'ruc')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['ruc']);
                $table->dropColumn('ruc');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'documento_identidad_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['documento_identidad_id']);
                $table->dropColumn('documento_identidad_id');
            });
        }

        if (!Schema::hasColumn('users', 'documento_tipo_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('documento_tipo_id')->nullable()->constrained('dim_documento_tipo');
            });
        }
        if (!Schema::hasColumn('users', 'dni')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('dni', 8)->nullable()->unique();
            });
        }
        if (!Schema::hasColumn('users', 'ruc')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('ruc', 11)->nullable()->unique();
            });
        }
    }
};
