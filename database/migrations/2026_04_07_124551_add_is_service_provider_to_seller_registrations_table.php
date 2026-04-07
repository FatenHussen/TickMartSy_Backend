<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->boolean('is_service_provider')->default(false)->after('logo');
            $table->json('service_type_ids')->nullable()->after('is_service_provider');
        });
    }

    public function down(): void
    {
        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->dropColumn(['is_service_provider', 'service_type_ids']);
        });
    }
};
