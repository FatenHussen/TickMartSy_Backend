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
        Schema::create('bascket_item_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_item_id')->constrained('basket_items')->onDelete('cascade');
            $table->json('company');
            $table->boolean('is_default');
            $table->decimal('company_specific_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bascket_item_companies');
    }
};
