<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PointSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PointRuleSeeder::class);
        $this->call(PointWalletSeeder::class);
        $this->call(PointEventSeeder::class);
        $this->call(PointTransactionSeeder::class);
    }
}