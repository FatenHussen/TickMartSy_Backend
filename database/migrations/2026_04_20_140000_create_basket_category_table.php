<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('basket_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_id')->constrained('baskets')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['basket_id', 'category_id']);
        });

        $existing = DB::table('baskets')
            ->select('id as basket_id', 'category_id')
            ->whereNotNull('category_id')
            ->get();

        foreach ($existing as $row) {
            DB::table('basket_category')->insert([
                'basket_id' => $row->basket_id,
                'category_id' => $row->category_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basket_category');
    }
};
