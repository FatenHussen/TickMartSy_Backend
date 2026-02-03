<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_affiliate')
                ->default(false)
                ->after('image');

            $table->boolean('affiliate_approved')
                ->default(false)
                ->after('is_affiliate');

            $table->string('affiliate_id')
                ->nullable()
                ->unique()
                ->after('affiliate_approved');

            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained()
                ->after('affiliate_id');

            $table->decimal('affiliate_rate', 5, 2)
                ->nullable()
                ->after('coupon_id');

            // إذا عندك جدول coupons
            // $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_affiliate',
                'affiliate_approved',
                'affiliate_id',
                'coupon_id',
                'affiliate_rate',
            ]);
        });
    }
};
