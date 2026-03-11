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
            $table->text('fcm_token');
            $table->string('device_name')->nullable();
            $table->string('device_type')->default('web'); // web, mobile, tablet
            $table->timestamps();

            // منع التكرار
            $table->unique(['vendor_user_id', 'fcm_token']);
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
