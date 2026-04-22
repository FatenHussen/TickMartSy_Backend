<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyRows = DB::table('units')->select('id', 'name')->get();

        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique('units_name_unique');
            $table->json('name')->change();
        });

        foreach ($legacyRows as $row) {
            $name = is_string($row->name) ? trim($row->name) : '';

            DB::table('units')
                ->where('id', $row->id)
                ->update([
                    'name' => json_encode([
                        'ar' => $name,
                        'en' => $name,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
        }
    }

    public function down(): void
    {
        $rows = DB::table('units')->select('id', 'name')->get();

        foreach ($rows as $row) {
            $decoded = is_string($row->name) ? json_decode($row->name, true) : null;
            $fallback = is_array($decoded)
                ? ($decoded['en'] ?? $decoded['ar'] ?? null)
                : null;

            DB::table('units')
                ->where('id', $row->id)
                ->update(['name' => $fallback]);
        }

        Schema::table('units', function (Blueprint $table) {
            $table->string('name')->change();
            $table->unique('name');
        });
    }
};
