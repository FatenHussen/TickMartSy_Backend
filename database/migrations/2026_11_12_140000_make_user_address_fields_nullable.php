<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Street name and contact phone are no longer required when saving an address.
     *
     * Most users here do not know (or their location does not have) a formal
     * street name, and the contact phone already exists on the user account —
     * asking for it again on every address is redundant. Both stay available
     * for the cases that do need them (e.g. gift deliveries to someone else),
     * they are simply optional now.
     */
    public function up(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('user_addresses', 'street_name')) {
                $table->string('street_name')->nullable()->change();
            }

            if (Schema::hasColumn('user_addresses', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('user_addresses', 'street_name')) {
                $table->string('street_name')->nullable(false)->change();
            }

            if (Schema::hasColumn('user_addresses', 'contact_phone')) {
                $table->string('contact_phone')->nullable(false)->change();
            }
        });
    }
};
