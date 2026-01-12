<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BasketItemCompany;

class BasketItemCompanySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'basket_item_id' => 1,
                'brand_id' => 1,
                'is_default' => true,
                'company_specific_price' => null,
            ],
            [
                'basket_item_id' => 1,
                'brand_id' => 1,

                'is_default' => false,
                'company_specific_price' => 12.50,
            ],
            [
                'basket_item_id' => 2,
                'brand_id' => 1,

                'is_default' => true,
                'company_specific_price' => 9.99,
            ],
            [
                'basket_item_id' => 3,
                'brand_id' => 1,

                'is_default' => false,
                'company_specific_price' => null,
            ],
        ];

        foreach ($data as $item) {
            BasketItemCompany::create($item);
        }
    }
}
