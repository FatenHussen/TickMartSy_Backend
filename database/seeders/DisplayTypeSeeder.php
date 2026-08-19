<?php

namespace Database\Seeders;

use App\Models\DisplayType;
use Illuminate\Database\Seeder;

/**
 * Seeds display_types with fixed IDs so every environment exposes the same
 * display_type_id values to mobile/web clients.
 */
class DisplayTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('display_types.seed', []) as $id => $attributes) {
            DisplayType::query()->updateOrCreate(
                ['id' => $id],
                $attributes,
            );
        }
    }
}
