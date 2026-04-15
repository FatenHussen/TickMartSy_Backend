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
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_restaurant')->default(false)->after('is_service_provider');
            $table->json('payment_methods')->nullable()->after('is_restaurant');
            $table->enum('pricing_tier', ['cheap', 'medium', 'expensive'])->nullable()->after('payment_methods');
            $table->boolean('is_recommended')->default(false)->after('pricing_tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'is_restaurant',
                'payment_methods',
                'pricing_tier',
                'is_recommended',
            ]);
        });
    }
};
