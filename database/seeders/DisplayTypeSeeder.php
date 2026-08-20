<?php

namespace Database\Seeders;

use App\Support\DisplayTypeCatalog;
use Illuminate\Database\Seeder;

/**
 * Seeds display_types with fixed IDs so every environment exposes the same
 * display_type_id values to mobile/web clients.
 */
class DisplayTypeSeeder extends Seeder
{
    public function run(): void
    {
        DisplayTypeCatalog::ensureSeeded();

        $maxId = (int) \App\Models\DisplayType::query()->max('id');
        if ($maxId > 0) {
            // Safe here only — not inside an HTTP request DB::transaction.
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE display_types AUTO_INCREMENT = ' . ($maxId + 1)
            );
        }

        $count = \App\Models\DisplayType::query()->count();
        $productOk = \App\Models\DisplayType::query()->whereKey(2)->exists();
        $this->command?->info(
            "display_types ready: {$count} rows (product id=2 " . ($productOk ? 'ok' : 'MISSING') . ').'
        );
    }
}
