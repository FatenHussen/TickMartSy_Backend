<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();

            $table->string('owner_name');
            $table->string('owner_email')->unique();
            $table->string('owner_phone', 20)->unique();
            $table->string('password');

            $table->string('store_name');
            $table->text('description')->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('cover', 500)->nullable();
            $table->string('store_phone', 20)->nullable();
            $table->string('store_email')->nullable();
            $table->text('store_address')->nullable();
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();

            $table->enum('status', ['pending', 'active', 'suspended', 'rejected'])->default('pending');
            $table->decimal('rating', 3, 2)->default(0.00);

            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            $table->json('time_work')->nullable();
            $table->date('date_contract')->nullable();
            $table->time('duration_contract')->nullable();
            $table->string('contract_number')->nullable();
            $table->string('Commercial_registration_number')->nullable();
            $table->integer('agreed_percentage')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
