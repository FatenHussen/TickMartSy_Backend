<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_vendor_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('vendor_service_id')->constrained('vendor_services')->cascadeOnDelete();

            // Extra details about this service in this shop
            $table->json('extra_details')->nullable();  // any additional info

            // Pricing
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_unit')->nullable();   // e.g. "per hour", "per visit"

            // Duration
            $table->unsignedInteger('duration_minutes')->nullable(); // service duration in minutes

            // Schedule: open/closed per day
            // Format: {"monday":{"open":"09:00","close":"18:00","closed":false}, ...}
            $table->json('schedule')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shop_id', 'vendor_service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_vendor_services');
    }
};
