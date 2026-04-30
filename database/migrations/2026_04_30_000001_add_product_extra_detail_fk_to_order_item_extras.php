<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_item_extras')) {
            Schema::create('order_item_extras', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
                $table->foreignId('product_extra_detail_id')
                    ->constrained('product_extra_details')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('price');
                $table->timestamps();
            });

            return;
        }

        if (!Schema::hasTable('product_extra_details')) {
            return;
        }

        Schema::table('order_item_extras', function (Blueprint $table) {
            $table->foreign('product_extra_detail_id')
                ->references('id')
                ->on('product_extra_details')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_extras');
    }
};
