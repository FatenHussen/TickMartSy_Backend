<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_shop_vendor_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('shop_vendor_service_id')->constrained('shop_vendor_services')->cascadeOnDelete();
            $table->unique(['promotion_id', 'shop_vendor_service_id'], 'promotion_shop_vendor_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_shop_vendor_services');
    }
};
