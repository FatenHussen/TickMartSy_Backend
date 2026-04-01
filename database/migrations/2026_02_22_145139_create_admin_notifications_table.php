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
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('body');
            $table->string('type')->default('all');
            $table->boolean('is_fixed')->default(false);
            $table->string('target_page')->nullable();
            $table->string('emoji')->nullable();
            $table->string('media_type')->nullable();
            $table->text('media_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
