<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_campaign_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_campaign_id')->constrained('popup_campaigns')->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['popup_campaign_id', 'promotion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_campaign_promotion');
    }
};
