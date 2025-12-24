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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            /** Polymorphic relation */
            $table->morphs('mediable'); // mediable_id + mediable_type

            /** Media data */
            $table->string('collection')->default('default'); // cover, logo, slider
            // $table->string('file_name'); 
            $table->string('file_path'); 
            $table->integer('order')->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
