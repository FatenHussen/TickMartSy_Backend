<?php

use App\Models\PageSection;
use App\Support\DisplayTypeCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_active');
        });

        PageSection::query()
            ->whereHas('page', fn ($query) => $query->whereNotNull('category_id'))
            ->whereHas('section', fn ($query) => $query->where('api_method', 'categories'))
            ->where('order', 1)
            ->update(['is_default' => true]);

        PageSection::query()
            ->whereHas('page', fn ($query) => $query->whereNotNull('category_id'))
            ->whereHas('section', fn ($query) => $query->where('api_method', 'products'))
            ->where('order', 2)
            ->update(['is_default' => true]);

        PageSection::query()
            ->whereNull('display_type_id')
            ->with(['section', 'page'])
            ->chunkById(200, function ($pageSections) {
                foreach ($pageSections as $pageSection) {
                    $displayTypeId = DisplayTypeCatalog::idForSection(
                        $pageSection->section,
                        $pageSection->page?->slug,
                    );

                    if ($displayTypeId !== null) {
                        $pageSection->update(['display_type_id' => $displayTypeId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
