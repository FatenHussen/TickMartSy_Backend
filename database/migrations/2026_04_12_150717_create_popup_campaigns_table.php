<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('popup_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['modal', 'slide_in', 'fullscreen'])->default('modal');
            $table->enum('status', ['draft', 'active', 'paused', 'archived'])->default('draft');
            $table->integer('priority')->default(0);

            $table->string('headline');
            $table->string('subheadline')->nullable();
            $table->text('description')->nullable();

            $table->enum('button_text', ['Claim Offer', 'Shop Now', 'Subscribe', 'Reveal My Deal'])->default('Claim Offer');
            $table->string('secondary_button_text')->nullable();

            $table->enum('cta_type', ['url', 'product', 'category', 'form', 'coupon'])->default('url');
            $table->string('cta_value');

            $table->enum('media_type', ['image', 'video', 'gif'])->default('image');
            $table->string('media_path');

            $table->boolean('form_enabled')->default(false);
            $table->json('form_fields')->nullable();

            $table->json('show_on_pages')->nullable();
            $table->enum('audience_type', ['all_visitors', 'guests_only', 'logged_in_only', 'new_visitors', 'returning_visitors'])->default('all_visitors');

            $table->enum('trigger_type', ['on_load', 'delay', 'scroll', 'exit_intent'])->default('on_load');
            $table->integer('trigger_value')->nullable();

            $table->integer('show_every')->default(0);
            $table->integer('max_impressions')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popup_campaigns');
    }
};
