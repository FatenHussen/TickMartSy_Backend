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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->json('name');

            // Code
            $table->string('code')->unique();

            // Discount
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 10, 2);

            // Validity
            $table->dateTime('start_at');
            $table->dateTime('end_at');

            // Usage
            $table->unsignedInteger('max_uses');
            $table->unsignedInteger('used_count')->default(0);

            // Location (optional)
            $table->foreignId('city_id')->nullable()->constrained();

            // المسوق (optional)
            $table->foreignId('user_id')->nullable()->constrained();

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
