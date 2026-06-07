<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE customers ALTER COLUMN tipo_documento TYPE VARCHAR(20)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE customers ALTER COLUMN tipo_documento TYPE VARCHAR(2)');
    }
};
