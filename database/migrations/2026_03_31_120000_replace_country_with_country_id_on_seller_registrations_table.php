<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->foreignId('country_id')
                ->nullable()
                ->after('commercial_register_date')
                ->constrained('countries')
                ->nullOnDelete();
        });

        if (Schema::hasColumn('seller_registrations', 'country')) {
            DB::table('seller_registrations')
                ->select('id', 'country')
                ->whereNotNull('country')
                ->orderBy('id')
                ->each(function ($registration) {
                    $countryId = DB::table('countries')
                        ->where('name->en', $registration->country)
                        ->orWhere('name->ar', $registration->country)
                        ->value('id');

                    if ($countryId) {
                        DB::table('seller_registrations')
                            ->where('id', $registration->id)
                            ->update(['country_id' => $countryId]);
                    }
                });

            Schema::table('seller_registrations', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }

    public function down(): void
    {
        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->string('country')->nullable()->after('commercial_register_date');
        });

        DB::table('seller_registrations')
            ->select('id', 'country_id')
            ->whereNotNull('country_id')
            ->orderBy('id')
            ->each(function ($registration) {
                $countryName = DB::table('countries')
                    ->where('id', $registration->country_id)
                    ->value('name');

                $decodedName = is_string($countryName)
                    ? json_decode($countryName, true)
                    : null;

                DB::table('seller_registrations')
                    ->where('id', $registration->id)
                    ->update([
                        'country' => $decodedName['en'] ?? $decodedName['ar'] ?? null,
                    ]);
            });

        Schema::table('seller_registrations', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
