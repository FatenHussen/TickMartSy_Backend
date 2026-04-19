<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('admin_area');

        Schema::create('admin_city', function (Blueprint $table): void {
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->primary(['admin_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_city');
    }
};
