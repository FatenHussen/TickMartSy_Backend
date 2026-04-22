<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('units', 'name_json')) {
            Schema::table('units', function (Blueprint $table) {
                $table->json('name_json')->nullable()->after('name');
            });
        }

        $legacyRows = DB::table('units')->select('id', 'name')->get();

        foreach ($legacyRows as $row) {
            $rawName = $row->name;
            $decoded = is_string($rawName) ? json_decode($rawName, true) : null;

            if (is_array($decoded)) {
                $nameAr = isset($decoded['ar']) ? (string) $decoded['ar'] : '';
                $nameEn = isset($decoded['en']) ? (string) $decoded['en'] : '';
            } else {
                $name = is_string($rawName) ? trim($rawName) : '';
                $nameAr = $name;
                $nameEn = $name;
            }

            DB::table('units')
                ->where('id', $row->id)
                ->update([
                    'name_json' => json_encode([
                        'ar' => $nameAr,
                        'en' => $nameEn,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
        }

        try {
            DB::statement('ALTER TABLE units DROP INDEX units_name_unique');
        } catch (Throwable $e) {
            // Ignore when index does not exist.
        }

        if (Schema::hasColumn('units', 'name')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('name_json', 'name');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('units', 'name_legacy')) {
            Schema::table('units', function (Blueprint $table) {
                $table->string('name_legacy')->nullable()->after('name');
            });
        }

        $rows = DB::table('units')->select('id', 'name')->get();

        foreach ($rows as $row) {
            $decoded = is_string($row->name) ? json_decode($row->name, true) : null;
            $fallback = is_array($decoded)
                ? ($decoded['en'] ?? $decoded['ar'] ?? null)
                : (is_string($row->name) ? $row->name : null);

            DB::table('units')
                ->where('id', $row->id)
                ->update(['name_legacy' => $fallback]);
        }

        if (Schema::hasColumn('units', 'name')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        Schema::table('units', function (Blueprint $table) {
            $table->renameColumn('name_legacy', 'name');
        });

        try {
            DB::statement('ALTER TABLE units ADD UNIQUE units_name_unique (name)');
        } catch (Throwable $e) {
            // Ignore when duplicate legacy names exist.
        }
    }
};
