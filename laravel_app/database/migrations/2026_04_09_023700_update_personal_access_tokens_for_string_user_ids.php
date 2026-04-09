<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableExists = DB::table('information_schema.tables')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'personal_access_tokens')
            ->exists();

        if (! $tableExists) {
            return;
        }

        $indexName = DB::table('information_schema.statistics')
            ->select('INDEX_NAME')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'personal_access_tokens')
            ->where('INDEX_NAME', '!=', 'PRIMARY')
            ->groupBy('INDEX_NAME')
            ->havingRaw("GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) = 'tokenable_type,tokenable_id'")
            ->value('INDEX_NAME');

        if ($indexName) {
            DB::statement("ALTER TABLE `personal_access_tokens` DROP INDEX `{$indexName}`");
        }

        DB::statement('ALTER TABLE `personal_access_tokens` MODIFY `tokenable_id` VARCHAR(20) NOT NULL');
        DB::statement('CREATE INDEX `personal_access_tokens_tokenable_type_tokenable_id_index` ON `personal_access_tokens` (`tokenable_type`, `tokenable_id`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left as a no-op to avoid unsafe casts from string IDs to integers.
    }
};
