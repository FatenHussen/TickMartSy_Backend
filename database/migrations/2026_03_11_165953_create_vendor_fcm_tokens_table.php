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
        Schema::create('vendor_fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_user_id')->constrained('vendor_users')->onDelete('cascade');
            $table->string('fcm_token', 500); // استخدم string بدل text
            $table->string('device_name')->nullable();
            $table->string('device_type')->default('web'); // web, mobile, tablet
            $table->timestamps();

            // منع التكرار
            $table->unique(['vendor_user_id', 'fcm_token']);
            $table->index('vendor_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_fcm_tokens');
    }
};
