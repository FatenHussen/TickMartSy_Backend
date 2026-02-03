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
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('points_required');
            $table->integer('stock_quantity')->nullable(); // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->string('category_id')->nullable()->constrained(); // electronics, books, vouchers, etc.
            $table->text('terms_conditions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};