<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_popup_campaign', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('popup_campaign_id')->constrained('popup_campaigns')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['page_id', 'popup_campaign_id']);
        });

        if (Schema::hasColumn('popup_campaigns', 'show_on_pages')) {
            $this->migrateShowOnPagesJsonToPivot();

            Schema::table('popup_campaigns', function (Blueprint $table) {
                $table->dropColumn('show_on_pages');
            });
        }
    }

    protected function migrateShowOnPagesJsonToPivot(): void
    {
        $campaigns = DB::table('popup_campaigns')->select('id', 'show_on_pages')->get();

        foreach ($campaigns as $row) {
            if ($row->show_on_pages === null || $row->show_on_pages === '') {
                continue;
            }

            $decoded = json_decode($row->show_on_pages, true);
            if (! is_array($decoded)) {
                continue;
            }

            $slugs = $this->slugsFromLegacyShowOnPages($decoded);
            foreach (array_unique($slugs) as $slug) {
                $pageId = DB::table('pages')->where('slug', $slug)->value('id');
                if ($pageId === null) {
                    continue;
                }

                DB::table('page_popup_campaign')->insertOrIgnore([
                    'page_id' => $pageId,
                    'popup_campaign_id' => $row->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * @param  array<mixed>  $decoded
     * @return list<string>
     */
    protected function slugsFromLegacyShowOnPages(array $decoded): array
    {
        $slugs = [];

        foreach ($decoded as $key => $value) {
            if (is_string($key) && (Str::contains($key, 'exclude') || $key === 'custom_url')) {
                continue;
            }

            if (is_string($value)) {
                $slugs[] = Str::of($value)->trim()->lower()->replace(' ', '_')->toString();

                continue;
            }

            if (is_array($value) && isset($value['name']) && $value['name'] !== 'custom_url') {
                $slugs[] = Str::of($value['name'])->trim()->lower()->replace(' ', '_')->toString();
            }
        }

        return $slugs;
    }

    public function down(): void
    {
        Schema::dropIfExists('page_popup_campaign');

        if (Schema::hasTable('popup_campaigns') && ! Schema::hasColumn('popup_campaigns', 'show_on_pages')) {
            Schema::table('popup_campaigns', function (Blueprint $table) {
                $table->json('show_on_pages')->nullable();
            });
        }
    }
};
