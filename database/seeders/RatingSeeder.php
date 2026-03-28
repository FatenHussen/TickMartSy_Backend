<?php

namespace Database\Seeders;

use App\Enums\RateableType;
use App\Models\{
    Rating,
    User,
    Product,
    Brand,
    Shop,
    Recipe,
    Basket,
    BasketSchedule,
    Driver
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->toArray();

        $rateables = [
            RateableType::PRODUCT->value => Product::pluck('id')->toArray(),
            RateableType::BRAND->value => Brand::pluck('id')->toArray(),
            RateableType::SHOP->value => Shop::pluck('id')->toArray(),
            RateableType::DELIVERY->value => Driver::pluck('id')->toArray(),
            RateableType::RECIPE->value => Recipe::pluck('id')->toArray(),
            RateableType::BASKET->value => Basket::pluck('id')->toArray(),
            RateableType::SCHEDULED_BASKET->value => BasketSchedule::pluck('id')->toArray(),
        ];

        $comments = [
            'ممتاز جداً',
            'جيد',
            'تجربة رائعة',
            'مقبول',
            'غير مرضي',
            'أنصح به',
            'سيء',
            'خدمة ممتازة',
            'سعر مناسب',
            'جودة عالية',
            'توصيل سريع',
            'تعامل راقي',
        ];

        foreach (range(1, 300) as $i) {

            $type = Arr::random(array_keys($rateables));

            if (empty($rateables[$type])) {
                continue;
            }

            $rateableId = Arr::random($rateables[$type]);

            // 70% chance to have a comment for all types
            $hasComment = rand(1, 100) <= 70;

            Rating::create([
                'user_id' => Arr::random($users),
                'order_id' => null,
                'rateable_type' => $this->resolveRateableClass($type),
                'rateable_id' => $rateableId,
                'rating' => rand(1, 5),
                'comment' => $hasComment ? Arr::random($comments) : null,
                'image' => ($type === RateableType::PRODUCT->value && rand(0, 1)) ? 'ratings/sample.jpg' : null,
                'is_verified' => rand(0, 1),
                'created_at' => now()->subDays(rand(0, 180)),
                'updated_at' => now(),
            ]);
        }
    }

    private function resolveRateableClass(string $type): string
    {
        return match ($type) {
            RateableType::PRODUCT->value => Product::class,
            RateableType::BRAND->value => Brand::class,
            RateableType::SHOP->value => Shop::class,
            RateableType::DELIVERY->value => Driver::class,
            RateableType::RECIPE->value => Recipe::class,
            RateableType::BASKET->value => Basket::class,
            RateableType::SCHEDULED_BASKET->value => BasketSchedule::class,
        };
    }
}
