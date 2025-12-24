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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->json('name');                 
            $table->string('owner_name');           
            $table->string('owner_phone');             
            $table->string('commercial_register')->nullable(); 
            $table->date('contract_date');           
            $table->string('contract_number')->unique();
            $table->integer('contract_duration_months'); 
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->string('logo')->nullable();         
            $table->json('cover_images')->nullable();   
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('ratings_count')->default(0); 
            $table->unsignedInteger('ratings_sum')->default(0);  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
