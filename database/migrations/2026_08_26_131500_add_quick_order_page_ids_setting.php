<?php

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Quick Order placement: which CMS pages show the section (content stays in settings).
     * Default = home page only (backward compatible).
     */
    public function up(): void
    {
        if (Setting::query()->where('key', 'quick_order_page_ids')->exists()) {
            return;
        }

        $homeId = Page::query()->where('slug', 'home')->value('id');
        $pageIds = $homeId ? [(int) $homeId] : [];

        Setting::create([
            'key' => 'quick_order_page_ids',
            'value' => $pageIds,
            'type' => 'json',
        ]);
    }

    public function down(): void
    {
        Setting::query()->where('key', 'quick_order_page_ids')->delete();
    }
};
