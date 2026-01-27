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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('name')->nullable()->after('phone');
            $table->string('vehicle_type')->nullable()->after('address'); // car, motorcycle, bicycle
            $table->string('vehicle_number')->nullable()->after('vehicle_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['name', 'vehicle_type', 'vehicle_number']);
        });
    }
};