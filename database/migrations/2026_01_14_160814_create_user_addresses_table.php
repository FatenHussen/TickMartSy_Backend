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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // المستخدم
            $table->string('label')->default('Home'); // Address Label *

            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();

            $table->string('street_name'); // Street Name *
            $table->string('nearest_landmark')->nullable(); // Nearest Landmark
            $table->string('building_number')->nullable(); // Building/Number
            $table->string('floor_apartment')->nullable(); // Floor/Apartment
            $table->string('contact_phone'); // Contact Phone *

            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            $table->boolean('is_default')->default(false); // إذا العنوان الافتراضي للمستخدم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_users');
    }
};
