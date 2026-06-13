<?php

namespace Database\Seeders;

use App\Models\Basket;
use App\Models\Page;
use App\Models\PopupCampaign;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Recipe;
use App\Models\Shop;
use Illuminate\Database\Seeder;

/**
 * One popup per {@see PromotionSeeder} promotion, with display rules varied so that
 * together they exercise every {@see PopupCampaign} type, audience, and trigger.
 *
 * Frequency fields match {@see PopupCampaign::DEFAULT_SHOW_EVERY} and
 * {@see PopupCampaign::DEFAULT_MAX_IMPRESSIONS} (same as the admin API).
 *
 * Sample attachables (product / shop / recipe / basket) are linked when those rows exist.
 *
 * Run after {@see PromotionSeeder}.
 */
class PopupCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = Promotion::query()->orderBy('type')->get();

        if ($promotions->isEmpty()) {
            $this->command?->warn('PopupCampaignSeeder: no promotions found. Run PromotionSeeder first.');

            return;
        }

        $priority = 50;
        $productId = Product::query()->value('id');
        $shopId = Shop::query()->value('id');
        $recipeId = Recipe::query()->value('id');
        $basketId = Basket::query()->value('id');

        foreach ($promotions as $promotion) {
            $slug = 'popup-announce-' . str_replace('_', '-', $promotion->type);
            $announcement = $this->announcementHeadline($promotion->type);
            $display = $this->displayVariantForPromotionType($promotion->type);

            $campaign = PopupCampaign::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $announcement,
                    'headline' => $announcement,
                    'subheadline' => $promotion->getTranslations('name'),
                    'description' => $promotion->getTranslations('description'),
                    'type' => $display['type'],
                    'status' => PopupCampaign::STATUS_ACTIVE,
                    'priority' => $priority--,
                    'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                    'button_url' => '/checkout?promotion_id=' . $promotion->id,
                    'secondary_button_text' => null,
                    'media_type' => $display['media_type'],
                    'media_path' => $display['media_path'],
                    'form_enabled' => false,
                    'form_fields' => null,
                    'audience_type' => $display['audience_type'],
                    'trigger_type' => $display['trigger_type'],
                    'trigger_value' => $display['trigger_value'],
                    'show_every' => PopupCampaign::DEFAULT_SHOW_EVERY,
                    'max_impressions' => PopupCampaign::DEFAULT_MAX_IMPRESSIONS,
                ]
            );

            $this->syncAttachablesForPromotionType(
                $campaign,
                $promotion->type,
                $productId,
                $shopId,
                $recipeId,
                $basketId
            );

            $campaign->promotions()->sync([$promotion->id]);

            $this->syncPagesForCampaign($campaign, $display['show_on_pages'] ?? null);
        }

        $this->createGuaranteedHomeCampaign($productId, $shopId, $recipeId, $basketId);
    }

    /**
     * Ensures user API returns at least one home popup with entity payloads.
     */
    private function createGuaranteedHomeCampaign(
        ?int $productId,
        ?int $shopId,
        ?int $recipeId,
        ?int $basketId
    ): void
    {
        $campaign = PopupCampaign::updateOrCreate(
            ['slug' => 'popup-home-default'],
            [
                'title' => [
                    'en' => 'Welcome offer is available now.',
                    'ar' => 'عرض ترحيبي متاح الآن.',
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
                    'en' => 'This popup is seeded as a guaranteed home campaign for API testing.',
                    'ar' => 'تمت إضافة هذه النافذة كحملة مضمونة للصفحة الرئيسية لاختبار الـ API.',
                ],
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
            ]
        );

        $campaign->products()->sync($productId !== null ? [$productId] : []);
        $campaign->shops()->sync($shopId !== null ? [$shopId] : []);
        $campaign->recipes()->sync($recipeId !== null ? [$recipeId] : []);
        $campaign->baskets()->sync($basketId !== null ? [$basketId] : []);
        $this->syncPagesForCampaign($campaign, ['home']);
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

    /**
     * Maps each seeded promotion type to a distinct popup configuration.
     *
     * Covers {@see PopupCampaign::TYPES}, {@see PopupCampaign::AUDIENCE_TYPES},
     * and {@see PopupCampaign::TRIGGER_TYPES} across default promotion types.
     */
    private function displayVariantForPromotionType(string $promotionType): array
    {
        return match ($promotionType) {
            'free_shipping' => [
                'type' => PopupCampaign::TYPE_FULLSCREEN,
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_ON_LOAD,
                'trigger_value' => null,
                'media_type' => PopupCampaign::MEDIA_GIF,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['home', 'products'],
            ],
            'simple_discount' => [
                'type' => PopupCampaign::TYPE_MODAL,
                'audience_type' => PopupCampaign::AUDIENCE_GUESTS,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 5,
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['home', 'products'],
            ],
            'spend_x_discount' => [
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'audience_type' => PopupCampaign::AUDIENCE_LOGGED_IN,
                'trigger_type' => PopupCampaign::TRIGGER_SCROLL,
                'trigger_value' => 40,
                'media_type' => PopupCampaign::MEDIA_VIDEO,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['products', 'product_details'],
            ],
            'spend_x_get_gift' => [
                'type' => PopupCampaign::TYPE_MODAL,
                'audience_type' => PopupCampaign::AUDIENCE_NEW,
                'trigger_type' => PopupCampaign::TRIGGER_EXIT_INTENT,
                'trigger_value' => null,
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['home'],
            ],
            'spend_x_get_points' => [
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'audience_type' => PopupCampaign::AUDIENCE_RETURNING,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 8,
                'media_type' => PopupCampaign::MEDIA_GIF,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => null,
            ],
            'spend_x_get_free_shipping' => [
                'type' => PopupCampaign::TYPE_MODAL,
                'audience_type' => PopupCampaign::AUDIENCE_LOGGED_IN,
                'trigger_type' => PopupCampaign::TRIGGER_SCROLL,
                'trigger_value' => 25,
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['shops', 'shop_details'],
            ],
            default => [
                'type' => PopupCampaign::TYPE_MODAL,
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 4,
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => null,
            ],
        };
    }

    private function syncAttachablesForPromotionType(
        PopupCampaign $campaign,
        string $promotionType,
        ?int $productId,
        ?int $shopId,
        ?int $recipeId,
        ?int $basketId
    ): void {
        $payload = match ($promotionType) {
            'free_shipping' => [
                'products' => $productId !== null ? [$productId] : [],
                'shops' => $shopId !== null ? [$shopId] : [],
                'recipes' => [],
                'baskets' => [],
            ],
            'simple_discount' => [
                'products' => $productId !== null ? [$productId] : [],
                'shops' => $shopId !== null ? [$shopId] : [],
                'recipes' => [],
                'baskets' => [],
            ],
            'spend_x_discount' => [
                'products' => $productId !== null ? [$productId] : [],
                'shops' => [],
                'recipes' => [],
                'baskets' => [],
            ],
            'spend_x_get_gift' => [
                'products' => [],
                'shops' => [],
                'recipes' => $recipeId !== null ? [$recipeId] : [],
                'baskets' => [],
            ],
            'spend_x_get_points' => [
                'products' => [],
                'shops' => [],
                'recipes' => [],
                'baskets' => $basketId !== null ? [$basketId] : [],
            ],
            'spend_x_get_free_shipping' => [
                'products' => [],
                'shops' => $shopId !== null ? [$shopId] : [],
                'recipes' => [],
                'baskets' => [],
            ],
            default => [
                'products' => $productId !== null ? [$productId] : [],
                'shops' => [],
                'recipes' => [],
                'baskets' => [],
            ],
        };

        $campaign->products()->sync($payload['products']);
        $campaign->shops()->sync($payload['shops']);
        $campaign->recipes()->sync($payload['recipes']);
        $campaign->baskets()->sync($payload['baskets']);
    }

    /**
     * One-line announcement per {@see PromotionSeeder} promotion type.
     *
     * @return array<string, string>
     */
    private function announcementHeadline(string $type): array
    {
        return match ($type) {
            'simple_discount' => [
                'en' => 'A sitewide discount is available now.',
                'ar' => 'يتوفر الآن خصم على كامل المتجر.',
            ],
            'spend_x_discount' => [
                'en' => 'Spend more, save more — this offer is active.',
                'ar' => 'أنفق أكثر ووفر أكثر — هذا العرض مفعّل.',
            ],
            'spend_x_get_gift' => [
                'en' => 'You can get a gift with qualifying orders.',
                'ar' => 'يمكنك الحصول على هدية مع الطلبات المؤهلة.',
            ],
            'spend_x_get_points' => [
                'en' => 'Bonus loyalty points are available on qualifying spend.',
                'ar' => 'تتوفر نقاط ولاء إضافية مع إنفاق مؤهل.',
            ],
            'free_shipping' => [
                'en' => 'Free shipping is available.',
                'ar' => 'يتوفر توصيل مجاني.',
            ],
            'spend_x_get_free_shipping' => [
                'en' => 'Free shipping unlocks on qualifying spend.',
                'ar' => 'يتم تفعيل التوصيل المجاني عند إنفاق مؤهل.',
            ],
            default => [
                'en' => 'A special promotion is available.',
                'ar' => 'يتوفر عرض خاص.',
            ],
        };
    }
}
