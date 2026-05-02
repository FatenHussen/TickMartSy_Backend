<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_campaign_attachables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_campaign_id')->constrained('popup_campaigns')->cascadeOnDelete();
            $table->morphs('attachable');
            $table->timestamps();

            $table->unique(['popup_campaign_id', 'attachable_type', 'attachable_id'], 'popup_campaign_attachable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_campaign_attachables');
    }
};
