<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_area', function (Blueprint $table): void {
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->primary(['admin_id', 'area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_area');
    }
};
