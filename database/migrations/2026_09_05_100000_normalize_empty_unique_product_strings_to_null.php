<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->nullifyEmptyStrings('products', ['sku', 'model', 'barcode', 'product_number']);
        $this->nullifyEmptyStrings('product_variants', ['sku', 'model', 'barcode']);
    }

    public function down(): void
    {
        // Empty strings were invalid unique placeholders; do not restore them.
    }

    /**
     * @param  list<string>  $columns
     */
    private function nullifyEmptyStrings(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)->where($column, '')->update([$column => null]);
        }
    }
};
