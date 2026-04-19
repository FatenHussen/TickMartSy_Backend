<?php

namespace Database\Seeders;

use App\Models\PopupCampaign;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

/**
 * One popup per {@see PromotionSeeder} promotion, with display rules varied so that
 * together they exercise every {@see PopupCampaign} type, audience, and trigger,
 * plus diverse `show_every` / `max_impressions` pairs.
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

        foreach ($promotions as $promotion) {
            $slug = 'popup-announce-' . str_replace('_', '-', $promotion->type);
            $announcement = $this->announcementHeadline($promotion->type);
            $display = $this->displayVariantForPromotionType($promotion->type);

            PopupCampaign::updateOrCreate(
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
                    'show_on_pages' => $display['show_on_pages'],
                    'audience_type' => $display['audience_type'],
                    'trigger_type' => $display['trigger_type'],
                    'trigger_value' => $display['trigger_value'],
                    'show_every' => $display['show_every'],
                    'max_impressions' => $display['max_impressions'],
                ]
            );
        }
    }

    /**
     * Maps each seeded promotion type to a distinct popup configuration.
     *
     * Across the five default promotion types, this covers:
     * - type: {@see PopupCampaign::TYPE_MODAL}, {@see PopupCampaign::TYPE_SLIDE_IN}, {@see PopupCampaign::TYPE_FULLSCREEN}
     * - audience_type: every {@see PopupCampaign::AUDIENCE_TYPES} value
     * - trigger_type: every {@see PopupCampaign::TRIGGER_TYPES} value
     * - show_every / max_impressions: several different non-trivial pairs (incl. 0 show_every)
     */
    private function displayVariantForPromotionType(string $promotionType): array
    {
        return match ($promotionType) {
            'free_shipping' => [
                'type' => PopupCampaign::TYPE_FULLSCREEN,
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_ON_LOAD,
                'trigger_value' => null,
                'show_every' => 0,
                'max_impressions' => 10,
                'media_type' => PopupCampaign::MEDIA_GIF,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => null,
            ],
            'simple_discount' => [
                'type' => PopupCampaign::TYPE_MODAL,
                'audience_type' => PopupCampaign::AUDIENCE_GUESTS,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 5,
                'show_every' => 60,
                'max_impressions' => 5,
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['home', 'products'],
            ],
            'spend_x_discount' => [
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'audience_type' => PopupCampaign::AUDIENCE_LOGGED_IN,
                'trigger_type' => PopupCampaign::TRIGGER_SCROLL,
                'trigger_value' => 40,
                'show_every' => 30,
                'max_impressions' => 3,
                'media_type' => PopupCampaign::MEDIA_VIDEO,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['products', 'product_details'],
            ],
            'spend_x_get_gift' => [
                'type' => PopupCampaign::TYPE_MODAL,
                'audience_type' => PopupCampaign::AUDIENCE_NEW,
                'trigger_type' => PopupCampaign::TRIGGER_EXIT_INTENT,
                'trigger_value' => null,
                'show_every' => 15,
                'max_impressions' => 1,
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => ['home'],
            ],
            'spend_x_get_points' => [
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'audience_type' => PopupCampaign::AUDIENCE_RETURNING,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 8,
                'show_every' => 120,
                'max_impressions' => 7,
                'media_type' => PopupCampaign::MEDIA_GIF,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => null,
            ],
            default => [
                'type' => PopupCampaign::TYPE_MODAL,
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 4,
                'show_every' => 45,
                'max_impressions' => 4,
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => 'images/display/banner.png',
                'show_on_pages' => null,
            ],
        };
    }

    /**
     * One-line announcement per {@see PromotionSeeder} promotion type.
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
            default => [
                'en' => 'A special promotion is available.',
                'ar' => 'يتوفر عرض خاص.',
            ],
        };
    }
}
