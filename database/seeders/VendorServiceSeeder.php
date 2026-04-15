<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VendorServiceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(VendorServiceCatalogSeeder::class);
    }
}
