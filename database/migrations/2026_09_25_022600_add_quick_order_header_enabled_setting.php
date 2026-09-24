<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Separate Quick Order header button visibility from the home section.
     * Section stays on quick_order_enabled; header uses quick_order_header_enabled.
     */
    public function up(): void
    {
        if (Setting::query()->where('key', 'quick_order_header_enabled')->exists()) {
            return;
        }

        Setting::create([
            'key' => 'quick_order_header_enabled',
            'value' => true,
            'type' => 'boolean',
        ]);
    }

    public function down(): void
    {
        Setting::query()->where('key', 'quick_order_header_enabled')->delete();
    }
};
