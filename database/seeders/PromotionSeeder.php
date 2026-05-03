<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Promotion;
use App\Services\User\PromotionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates one sample promotion per order-promotion type (see PromotionService & Admin\Promotion\StoreRequest).
     */
    public function run(): void
    {
        $startsAt = Carbon::now()->subDay();
        $endsAt = Carbon::now()->addDays(30);

        $window = [
            'is_active' => true,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];

        $definitions = [
            'simple_discount' => [
                ...$window,
                'name' => [
                    'en' => '10% Off Everything',
                    'ar' => 'خصم 10% على كل شيء',
                ],
                'description' => [
                    'en' => 'Get 10% off on all products.',
                    'ar' => 'احصل على خصم 10% على جميع المنتجات.',
                ],
                'min_spend' => null,
                'discount_value' => 10,
                'discount_type' => 'percentage',
                'gift_description' => null,
                'reward_points' => null,
            ],
            'spend_x_discount' => [
                ...$window,
                'name' => [
                    'en' => 'Spend 100 Get 20 Off',
                    'ar' => 'أنفق 100 واحصل على خصم 20',
                ],
                'description' => [
                    'en' => 'Get $20 off when you spend $100 or more.',
                    'ar' => 'احصل على خصم 20 عند الشراء بمبلغ 100 أو أكثر.',
                ],
                'min_spend' => 100,
                'discount_value' => 20,
                'discount_type' => 'fixed',
                'gift_description' => null,
                'reward_points' => null,
            ],
            'spend_x_get_gift' => [
                ...$window,
                'name' => [
                    'en' => 'Spend 200 Get a Gift',
                    'ar' => 'أنفق 200 و احصل على هدية',
                ],
                'description' => [
                    'en' => 'Receive a curated gift when you spend 200 or more.',
                    'ar' => 'احصل على هدية مختارة عند الإنفاق 200 أو أكثر.',
                ],
                'min_spend' => 200,
                'discount_value' => null,
                'discount_type' => null,
                'gift_description' => [
                    'en' => 'A surprise gift from the store ',
                    'ar' => 'هدية مفاجئة من المتجر.',
                ],
                'reward_points' => null,
            ],
            'spend_x_get_points' => [
                ...$window,
                'name' => [
                    'en' => 'Spend 150 to earn 50 points',
                    'ar' => 'أنفق 150 لتحصل على 50 نقطة',
                ],
                'description' => [
                    'en' => 'Earn 50 loyalty points when you spend 150 or more.',
                    'ar' => 'احصل على 50 نقطة ولاء عند الإنفاق 150 أو أكثر.',
                ],
                'min_spend' => 150,
                'discount_value' => null,
                'discount_type' => null,
                'gift_description' => null,
                'reward_points' => 50,
            ],
            'free_shipping' => [
                ...$window,
                'name' => [
                    'en' => 'Free Shipping',
                    'ar' => 'توصيل مجاني',
                ],
                'description' => [
                    'en' => 'Free delivery on your order — no minimum spend.',
                    'ar' => 'توصيل مجاني على طلبك دون حد أدنى للشراء.',
                ],
                'min_spend' => null,
                'discount_value' => null,
                'discount_type' => null,
                'gift_description' => null,
                'reward_points' => null,
            ],
            'spend_x_get_free_shipping' => [
                ...$window,
                'name' => [
                    'en' => 'Spend 250 Get Free Shipping',
                    'ar' => 'أنفق 250 واحصل على توصيل مجاني',
                ],
                'description' => [
                    'en' => 'Free delivery when you spend 250 or more.',
                    'ar' => 'توصيل مجاني عند الإنفاق 250 أو أكثر.',
                ],
                'min_spend' => 250,
                'discount_value' => null,
                'discount_type' => null,
                'gift_description' => null,
                'reward_points' => null,
            ],
        ];

        $expectedTypes = array_values(array_unique(array_merge(
            PromotionService::USER_SELECTABLE_TYPES,
            PromotionService::AUTOMATIC_TYPES,
        )));

        $slugs = ['cart'];

        foreach (array_values($expectedTypes) as $index => $type) {
            if (! isset($definitions[$type])) {
                throw new \RuntimeException("PromotionSeeder missing definition for type: {$type}");
            }

            $promotion = Promotion::updateOrCreate(
                ['type' => $type],
                [...$definitions[$type], 'position' => $index % 2 === 0 ? 'top' : 'bottom']
            );

            $ids = Page::query()->whereIn('slug', $slugs)->pluck('id')->all();
            $promotion->pages()->sync($ids);
        }
    }
}
