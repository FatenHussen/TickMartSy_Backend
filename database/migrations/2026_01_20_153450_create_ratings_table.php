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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('rateable');
            $table->integer('rating')->unsigned();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('comment')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->unique(
                ['user_id', 'rateable_id', 'rateable_type', 'order_id'],
                'unique_user_rating_per_order'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
