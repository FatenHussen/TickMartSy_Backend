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
        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recipe_id')
                ->constrained('recipes')
                ->cascadeOnDelete();

            $table->integer('step_number');

            $table->json('instruction');
            $table->json('time_minutes')->nullable();
            $table->json('heat_level')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_steps');
    }
};
