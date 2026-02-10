<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BasketItem;
use App\Models\BasketItemCompany;
use App\Models\Brand;

class BasketItemSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::all(); 

        $itemsData = [
            1 => [
                ['product_id' => 1,  'variant_id' => 1, 'shop_product_variant_id' => 1 ,'quantity' => 2, 'price' => 18.90, 'is_required' => true],
                ['product_id' => 2,  'variant_id' => 3, 'shop_product_variant_id' => 2, 'quantity' => 1, 'price' => 42.50, 'is_required' => false],
                ['product_id' => 3, 'variant_id' => 5, 'shop_product_variant_id' => 3, 'quantity' => 4, 'price' => 3.75,  'is_required' => true],
                ['product_id' => 4, 'variant_id' => 7, 'shop_product_variant_id' => 1, 'quantity' => 3, 'price' => 5.90,  'is_required' => true],
                ['product_id' => 5, 'variant_id' => 10, 'shop_product_variant_id' => 2, 'quantity' => 1, 'price' => 28.00, 'is_required' => false],
            ],

            2 => [
                ['product_id' => 1, 'variant_id' => 2, 'shop_product_variant_id' => 1,'quantity' => 2, 'price' => 4.20,  'is_required' => true],
                ['product_id' => 2, 'variant_id' => 4, 'shop_product_variant_id' => 2, 'quantity' => 1, 'price' => 7.80,  'is_required' => true],
                ['product_id' => 3, 'variant_id' => 5, 'shop_product_variant_id' => 3, 'quantity' => 3, 'price' => 6.50,  'is_required' => false],
                ['product_id' => 4, 'variant_id' => 8, 'shop_product_variant_id' => 1, 'quantity' => 4, 'price' => 3.10,  'is_required' => true],
            ],

            3 => [
                ['product_id' => 1,  'variant_id' => 1,  'shop_product_variant_id' => 1,'quantity' => 2, 'price' => 24.00, 'is_required' => true],
                ['product_id' => 2, 'variant_id' => 3, 'shop_product_variant_id' => 2,'quantity' => 1, 'price' => 55.00, 'is_required' => true],
                ['product_id' => 3, 'variant_id' => 6, 'shop_product_variant_id' => 3, 'quantity' => 3, 'price' => 9.50,  'is_required' => true],
                ['product_id' => 4, 'variant_id' => 8, 'shop_product_variant_id' => 1, 'quantity' => 2, 'price' => 14.75, 'is_required' => false],
                ['product_id' => 5, 'variant_id' => 10, 'shop_product_variant_id' => 1, 'quantity' => 4, 'price' => 5.60,  'is_required' => true],
            ],
        ];

        foreach ($itemsData as $basketId => $items) {
            foreach ($items as $itemData) {

                $basketItem = BasketItem::create(array_merge(
                    $itemData,
                    [
                        'basket_id'     => $basketId,
                        'min_quantity'  => 1,
                        'max_quantity'  => 10,
                        'is_extra'      => $itemData['is_required'] ? 0 : 1,
                    ]
                ));

                foreach ($brands as $index => $brand) {
                    BasketItemCompany::create([
                        'basket_item_id' => $basketItem->id,
                        'brand_id'       => $brand->id,
                        'is_default'     => $index === 0, 
                        'company_specific_price' => null,
                    ]);
                }
            }
        }
    }
}
