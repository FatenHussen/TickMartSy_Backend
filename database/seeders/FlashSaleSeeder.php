<?php

namespace Database\Seeders;

use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FlashSaleSeeder extends Seeder
{
    public function run(): void
    {
        $flashSale = FlashSale::updateOrCreate([
            'name' => 'Weekend Flash Sale',
        ], [
            'end_date' => Carbon::now()->addDays(2),
            'is_active' => true,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        // Attach a sample set of products to the active flash sale.
        $productIds = Product::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->limit(12)
            ->pluck('id');

        if ($productIds->isEmpty()) {
            return;
        }

        Product::where('flash_sale_id', $flashSale->id)
            ->whereNotIn('id', $productIds)
            ->update(['flash_sale_id' => null]);

        Product::whereIn('id', $productIds)
            ->update(['flash_sale_id' => $flashSale->id]);
    }
}
