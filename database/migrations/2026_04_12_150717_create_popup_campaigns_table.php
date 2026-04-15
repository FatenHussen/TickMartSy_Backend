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
            $table->json('title');
            $table->string('slug')->unique();
            $table->enum('type', ['modal', 'slide_in', 'fullscreen'])->default('modal');
            $table->enum('status', ['draft', 'active', 'paused', 'archived'])->default('draft');
            $table->integer('priority')->default(0);

            $table->json('headline');
            $table->json('subheadline')->nullable();
            $table->json('description')->nullable();

            $table->string('button_text');
            $table->string('button_url')->nullable();
            $table->string('secondary_button_text')->nullable();

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
