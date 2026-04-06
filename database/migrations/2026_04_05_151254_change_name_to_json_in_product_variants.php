<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing string values to JSON
        DB::table('product_variants')->whereNotNull('name')->orderBy('id')->each(function ($row) {
            $name = $row->name;
            // If already JSON, skip
            if (!is_string($name) || str_starts_with(trim($name), '{')) return;
            DB::table('product_variants')->where('id', $row->id)->update([
                'name' => json_encode(['ar' => $name, 'en' => $name]),
            ]);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }
};
