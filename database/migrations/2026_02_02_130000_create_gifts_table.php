<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('points_required');
            $table->integer('stock_quantity')->nullable(); // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->foreignId('category_id')->nullable()->constrained(); // electronics, books, vouchers, etc.
            $table->json('terms_conditions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
