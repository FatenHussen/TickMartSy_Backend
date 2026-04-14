<?php

namespace Database\Seeders;

use App\Models\FlashSale;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FlashSaleSeeder extends Seeder
{
    public function run(): void
    {
        FlashSale::create([
            'name' => 'Weekend Flash Sale',
            'end_date' => Carbon::now()->addDays(2),
            'is_active' => true,
        ]);
    }
}
