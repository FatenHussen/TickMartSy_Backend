<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_menu_items', function (Blueprint $table) {
            $table->id();
            $table->json('title'); // { "ar": "...", "en": "..." }
            // type: page | category | brand | url | route
            $table->string('type')->default('route');

            // destination references (only the one matching `type` is used)
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('url')->nullable();        // when type = url
            $table->string('route_key')->nullable();  // when type = route (e.g. categories, baskets, points)

            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('open_in_new_tab')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_menu_items');
    }
};
