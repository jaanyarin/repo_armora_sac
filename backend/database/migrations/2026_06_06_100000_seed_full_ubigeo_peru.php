<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $provincias = database_path('data/ubigeo_provincias_inei_2024.sql');
        $distritos = database_path('data/ubigeo_distritos_inei_2024.sql');

        if (file_exists($provincias)) {
            DB::unprepared(file_get_contents($provincias));
        }

        if (file_exists($distritos)) {
            DB::unprepared(file_get_contents($distritos));
        }
    }

    public function down(): void
    {
        $this->command?->warn('No rollback: ubigeo data is authoritative catálogos SUNAT.');
    }
};
