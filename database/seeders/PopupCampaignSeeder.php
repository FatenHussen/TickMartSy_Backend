<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\Category;
use App\Models\Page;
use App\Models\PopupCampaign;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Recipe;
use App\Models\Shop;
use App\Models\ShopVendorService;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeds one popup linked to one promotion that carries every promotion targeting relation,
 * plus popup-only attachables (recipes, baskets) for API testing.
 *
 * Run after PromotionSeeder, RestaurantAndServiceProviderSeeder, and VendorServiceCatalogSeeder.
 */
class PopupCampaignSeeder extends Seeder
{
    private const SAMPLE_LIMIT = 3;

    public function run(): void
    {
        PopupCampaign::query()->delete();

        $productIds = $this->sampleIds(Product::class);
        $categoryIds = $this->sampleIds(Category::class);
        $storeIds = $this->sampleIds(Shop::class, fn ($q) => $q
            ->where('is_restaurant', false)
            ->where('is_service_provider', false));
        $restaurantIds = $this->sampleIds(Shop::class, fn ($q) => $q->where('is_restaurant', true));
        $serviceProviderIds = $this->sampleIds(Shop::class, fn ($q) => $q->where('is_service_provider', true));
        $vendorIds = $this->sampleIds(Vendor::class);
        $recipeIds = $this->sampleIds(Recipe::class);
        $basketIds = $this->sampleIds(Basket::class);
        $shopVendorServiceIds = $this->sampleIds(ShopVendorService::class);

        $shopIds = array_values(array_unique(array_merge($storeIds, $restaurantIds, $serviceProviderIds)));

        $promotion = $this->seedPromotionWithAllRelations(
            $productIds,
            $categoryIds,
            $shopIds,
            $vendorIds,
            $shopVendorServiceIds,
        );

        $campaign = PopupCampaign::create([
            'title' => [
                'en' => 'Test popup with all attachable types.',
                'ar' => 'نافذة تجريبية مع جميع أنواع الربط.',
            ],
            'headline' => [
                'en' => 'Welcome offer is available now.',
                'ar' => 'عرض ترحيبي متاح الآن.',
            ],
            'subheadline' => [
                'en' => 'Check today\'s highlighted deals.',
                'ar' => 'تعرّف على العروض المميزة اليوم.',
            ],
            'description' => [
                'en' => 'Single seeded popup linked to one promotion with full targeting.',
                'ar' => 'نافذة واحدة مربوطة بعرض يحتوي كل العلاقات الممكنة.',
            ],
            'slug' => 'popup-test-all-attachables',
            'type' => PopupCampaign::TYPE_MODAL,
            'status' => PopupCampaign::STATUS_ACTIVE,
            'priority' => 1000,
            'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
            'button_url' => '/home',
            'secondary_button_text' => null,
            'media_type' => PopupCampaign::MEDIA_IMAGE,
            'media_path' => 'images/display/banner.png',
            'form_enabled' => false,
            'form_fields' => null,
            'audience_type' => PopupCampaign::AUDIENCE_ALL,
            'trigger_type' => PopupCampaign::TRIGGER_ON_LOAD,
            'trigger_value' => null,
            'show_every' => PopupCampaign::DEFAULT_SHOW_EVERY,
            'max_impressions' => PopupCampaign::DEFAULT_MAX_IMPRESSIONS,
        ]);

        $campaign->products()->sync($productIds);
        $campaign->shops()->sync($shopIds);
        $campaign->recipes()->sync($recipeIds);
        $campaign->baskets()->sync($basketIds);
        $campaign->shopVendorServices()->sync($shopVendorServiceIds);
        $campaign->promotions()->sync([$promotion->id]);

        $this->syncPagesForCampaign($campaign, ['home']);

        $this->command?->info(sprintf(
            'PopupCampaignSeeder: created 1 popup linked to promotion #%d (products: %d, categories: %d, stores: %d, restaurants: %d, service providers: %d, vendors: %d, recipes: %d, baskets: %d, services: %d).',
            $promotion->id,
            count($productIds),
            count($categoryIds),
            count($storeIds),
            count($restaurantIds),
            count($serviceProviderIds),
            count($vendorIds),
            count($recipeIds),
            count($basketIds),
            count($shopVendorServiceIds),
        ));
    }

    /**
     * @param  list<int>  $productIds
     * @param  list<int>  $categoryIds
     * @param  list<int>  $shopIds
     * @param  list<int>  $vendorIds
     * @param  list<int>  $shopVendorServiceIds
     */
    private function seedPromotionWithAllRelations(
        array $productIds,
        array $categoryIds,
        array $shopIds,
        array $vendorIds,
        array $shopVendorServiceIds,
    ): Promotion {
        $promotion = Promotion::updateOrCreate(
            ['type' => 'simple_discount'],
            [
                'name' => [
                    'en' => 'Full targeting test promotion',
                    'ar' => 'عرض تجريبي بكل العلاقات',
                ],
                'description' => [
                    'en' => 'Promotion seeded with products, categories, shops, vendors, and services.',
                    'ar' => 'عرض مرتبط بمنتجات وفئات ومتاجر وبائعين وخدمات للاختبار.',
                ],
                'is_active' => true,
                'position' => 'top',
                'starts_at' => Carbon::now()->subDay(),
                'ends_at' => Carbon::now()->addDays(30),
                'min_spend' => null,
                'discount_value' => 10,
                'discount_type' => 'percentage',
                'gift_description' => null,
                'reward_points' => null,
            ]
        );

        $promotion->products()->sync($productIds);
        $promotion->categories()->sync($categoryIds);
        $promotion->shops()->sync($shopIds);
        $promotion->vendors()->sync($vendorIds);
        $promotion->shopVendorServices()->sync($shopVendorServiceIds);

        $pageIds = Page::query()->whereIn('slug', ['home', 'cart'])->pluck('id')->all();
        $promotion->pages()->sync($pageIds);

        return $promotion;
    }

    /**
     * @param  class-string  $model
     * @return list<int>
     */
    private function sampleIds(string $model, ?callable $scope = null): array
    {
        $query = $model::query();

        if ($scope !== null) {
            $scope($query);
        }

        return $query->orderBy('id')->limit(self::SAMPLE_LIMIT)->pluck('id')->all();
    }

    private function syncPagesForCampaign(PopupCampaign $campaign, ?array $slugs): void
    {
        if ($slugs === null || $slugs === []) {
            $campaign->pages()->detach();

            return;
        }

        $ids = Page::query()->whereIn('slug', $slugs)->pluck('id')->all();
        $campaign->pages()->sync($ids);
    }
}
