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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->json('name');                  
            $table->json('description')->nullable();    
            $table->json('address');                   
            $table->string('phone')->nullable(); //store
            $table->string('mobile');
            $table->string('email');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->foreignId('area_id')->constrained('areas');
            $table->json('working_hours');             
            $table->string('logo')->nullable();         
            $table->json('cover_images')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('ratings_count')->default(0); 
            $table->unsignedInteger('ratings_sum')->default(0);
            
            $table->foreignId('vendor_id')
                ->constrained('vendors')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
