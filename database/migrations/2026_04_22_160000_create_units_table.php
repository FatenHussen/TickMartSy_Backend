<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('units')->insert([
            ['name' => 'piece', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'kg', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'gram', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'liter', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'box', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
