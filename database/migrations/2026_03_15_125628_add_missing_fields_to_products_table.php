<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('thumbnail')->nullable()->after('seo_image');
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            $table->string('unit')->nullable()->after('quantity'); // kg, piece, liter, box, etc.
            $table->boolean('is_visible')->default(true)->after('approval_status');
            $table->enum('discount_type', ['none', 'percentage', 'fixed'])->default('none')->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['thumbnail', 'cost_price', 'unit', 'is_visible', 'discount_type']);
        });
    }
};
