<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('affiliate_commission_type', [
                'percentage_order',
                'fixed_per_order',
                'percentage_selected_products',
            ])->default('percentage_order')->after('affiliate_rate');

            $table->decimal('affiliate_fixed_commission', 10, 2)
                ->nullable()
                ->after('affiliate_commission_type');
        });

        Schema::create('affiliate_user_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_user_products');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_commission_type',
                'affiliate_fixed_commission',
            ]);
        });
    }
};

