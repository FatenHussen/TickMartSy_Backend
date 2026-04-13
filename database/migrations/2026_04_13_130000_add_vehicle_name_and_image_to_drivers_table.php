<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table): void {
            $table->string('vehicle_name')->nullable()->after('vehicle_type');
            $table->string('vehicle_image')->nullable()->after('vehicle_number');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table): void {
            $table->dropColumn(['vehicle_name', 'vehicle_image']);
        });
    }
};
