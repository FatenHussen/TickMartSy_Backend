<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
      Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->json('name');                  // اسم المتجر بالعربية
            $table->string('owner_name');               // اسم المسؤول/البائع
            $table->string('owner_phone');               // اسم المسؤول/البائع
            $table->json('description')->nullable();    // وصف أو تخصص المتجر
            $table->json('address');                    // العنوان التفصيلي
            $table->string('phone')->nullable(); //store
            $table->string('mobile');
            $table->string('email')->unique()->nullable();
            $table->string('commercial_register')->nullable(); // رقم السجل التجاري
            $table->date('contract_date');              // تاريخ العقد
            $table->string('contract_number')->unique(); // رقم العقد
            $table->integer('contract_duration_months'); // مدة العقد بالأشهر
            $table->decimal('commission_rate', 5, 2)->default(5.00); // نسبة العمولة %
            $table->json('working_hours');              // JSON لأيام وساعات العمل (مرن)
            $table->string('logo')->nullable();         // مسار الشعار
            $table->json('cover_images')->nullable();   // مصفوفة صور الغلاف (دعم متعدد + GIF)
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('ratings_count')->default(0); // عدد التقييمات
            $table->unsignedInteger('ratings_sum')->default(0);   // مجموع قيم التقييمات (rating * 1)
            $table->timestamps();
        });

        Schema::create('store_area', function (Blueprint $table) {
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->primary(['store_id', 'area_id']);
        });

        Schema::create('store_service', function (Blueprint $table) {
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['store_id', 'service_id']);
        });

        Schema::create('store_category', function (Blueprint $table) {
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete(); 
            $table->primary(['store_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
