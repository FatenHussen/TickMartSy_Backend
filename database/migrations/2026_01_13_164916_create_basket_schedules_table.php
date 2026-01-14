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
        Schema::create('basket_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['3_days', 'weekly', 'biweekly', 'monthly']);
            $table->json('title');

            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('discount_value', 8, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basket_scedules');
    }
};
