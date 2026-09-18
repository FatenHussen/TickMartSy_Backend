<?php

use Database\Seeders\PlatformVendorSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform vendor is required for sale_channel=platform products.
     * Runs with migrate on production — not a forgotten seeder step.
     */
    public function up(): void
    {
        if (!Schema::hasTable('vendors') || !Schema::hasTable('shops')) {
            return;
        }

        (new PlatformVendorSeeder())->run();
    }

    public function down(): void
    {
        // Keep the platform vendor; products may already reference it.
    }
};
