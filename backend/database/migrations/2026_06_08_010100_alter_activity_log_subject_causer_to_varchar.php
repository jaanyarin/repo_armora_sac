<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('activitylog.table_name', 'activity_log');

        if (!Schema::hasTable($table)) {
            return;
        }

        $columns = DB::selectOne("
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = ? AND column_name = 'subject_id'
        ", [$table]);

        if ($columns && strtolower($columns->data_type) === 'bigint') {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN subject_id TYPE VARCHAR(26) USING subject_id::varchar");
            DB::statement("ALTER TABLE {$table} ALTER COLUMN causer_id TYPE VARCHAR(26) USING causer_id::varchar");
            DB::statement("DROP INDEX IF EXISTS subject");
            DB::statement("DROP INDEX IF EXISTS causer");
            DB::statement("CREATE INDEX IF NOT EXISTS subject ON {$table} (subject_type, subject_id)");
            DB::statement("CREATE INDEX IF NOT EXISTS causer ON {$table} (causer_type, causer_id)");
        }
    }

    public function down(): void
    {
        $table = config('activitylog.table_name', 'activity_log');
        if (!Schema::hasTable($table)) {
            return;
        }
        DB::statement("ALTER TABLE {$table} ALTER COLUMN subject_id TYPE BIGINT USING NULL");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN causer_id TYPE BIGINT USING NULL");
    }
};
