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
        Schema::create('basket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('variant_id')->constrained('product_variants');

            $table->foreignId('shop_product_variant_id')->nullable()->constrained('shop_product_variants');

            $table->integer('quantity')->unsigned()->default(1);
           $table->boolean('is_required')->default(false);
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->default(10);

            $table->decimal('price', 10, 2);  

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bascket_items');
    }
};
