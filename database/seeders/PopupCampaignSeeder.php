<?php

namespace Database\Seeders;

use App\Models\PopupCampaign;
use Illuminate\Database\Seeder;

class PopupCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            [
                'title' => 'Spring Flash Sale',
                'slug' => 'spring-flash-modal',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 90,
                'headline' => 'Get 20% Off Today',
                'subheadline' => 'Only until midnight',
                'description' => 'Fresh baskets and new ingredients arrive with 20% off everything.',
                'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                'secondary_button_text' => 'See offers',
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/collections/spring-sale',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/spring.jpg',
                'form_enabled' => true,
                'form_fields' => ['name', 'email', 'phone'],
                'show_on_pages' => ['home', 'category'],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 5,
                'show_every' => 60,
                'max_impressions' => 3,
            ],
            [
                'title' => 'Weekly Inspiration',
                'slug' => 'weekly-inspiration-slide',
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 75,
                'headline' => 'Discover New Weekly Picks',
                'subheadline' => 'Handpicked by chefs',
                'description' => 'Scroll through fresh bundles tailored for your taste.',
                'button_text' => PopupCampaign::BUTTON_CLAIM_OFFER,
                'secondary_button_text' => 'Browse Collections',
                'cta_type' => PopupCampaign::CTA_CATEGORY,
                'cta_value' => '12',
                'media_type' => PopupCampaign::MEDIA_VIDEO,
                'media_path' => '/storage/popups/weekly.mp4',
                'form_enabled' => false,
                'show_on_pages' => ['home', 'product'],
                'audience_type' => PopupCampaign::AUDIENCE_GUESTS,
                'trigger_type' => PopupCampaign::TRIGGER_SCROLL,
                'trigger_value' => 30,
                'show_every' => 10,
                'max_impressions' => 5,
            ],
            [
                'title' => 'Exclusive Checkout Offer',
                'slug' => 'checkout-exit-intent',
                'type' => PopupCampaign::TYPE_FULLSCREEN,
                'status' => PopupCampaign::STATUS_PAUSED,
                'priority' => 70,
                'headline' => 'Wait! Take an extra 10% off',
                'subheadline' => 'Only for logged-in customers',
                'description' => 'Use code EXTRA10 before you finish the checkout.',
                'button_text' => PopupCampaign::BUTTON_REVEAL_MY_DEAL,
                'cta_type' => PopupCampaign::CTA_COUPON,
                'cta_value' => 'EXTRA10',
                'media_type' => PopupCampaign::MEDIA_GIF,
                'media_path' => '/storage/popups/checkout.gif',
                'form_enabled' => false,
                'show_on_pages' => ['checkout'],
                'audience_type' => PopupCampaign::AUDIENCE_LOGGED_IN,
                'trigger_type' => PopupCampaign::TRIGGER_EXIT_INTENT,
                'show_every' => 5,
                'max_impressions' => 1,
            ],
            [
                'title' => 'Guest Welcome Modal',
                'slug' => 'guest-welcome-modal',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 65,
                'headline' => 'Welcome, Guest!',
                'subheadline' => 'Sign up for early alerts',
                'description' => 'Subscribe and stay ahead of limited drops.',
                'button_text' => PopupCampaign::BUTTON_SUBSCRIBE,
                'cta_type' => PopupCampaign::CTA_FORM,
                'cta_value' => '/newsletter',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/guest.jpg',
                'form_enabled' => true,
                'form_fields' => ['email'],
                'show_on_pages' => ['home'],
                'audience_type' => PopupCampaign::AUDIENCE_GUESTS,
                'trigger_type' => PopupCampaign::TRIGGER_ON_LOAD,
                'show_every' => 60,
                'max_impressions' => 4,
            ],
            [
                'title' => 'New Visitors Slide-In',
                'slug' => 'new-visitors-slide',
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 82,
                'headline' => 'First-time bundle!',
                'subheadline' => 'Special price for new friends',
                'description' => 'Save 15% on your first curated basket.',
                'button_text' => PopupCampaign::BUTTON_CLAIM_OFFER,
                'cta_type' => PopupCampaign::CTA_PRODUCT,
                'cta_value' => '501',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/new.png',
                'form_enabled' => false,
                'show_on_pages' => ['home', 'custom_url'],
                'audience_type' => PopupCampaign::AUDIENCE_NEW,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 10,
                'show_every' => 30,
                'max_impressions' => 3,
            ],
            [
                'title' => 'Returning Visitors Modal',
                'slug' => 'returning-modal',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 68,
                'headline' => 'We missed you!',
                'description' => 'Unlock VIP perks just for coming back.',
                'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/collections/vip',
                'media_type' => PopupCampaign::MEDIA_VIDEO,
                'media_path' => '/storage/popups/returning.mp4',
                'form_enabled' => false,
                'show_on_pages' => ['home', 'product'],
                'audience_type' => PopupCampaign::AUDIENCE_RETURNING,
                'trigger_type' => PopupCampaign::TRIGGER_SCROLL,
                'trigger_value' => 50,
                'show_every' => 15,
                'max_impressions' => 3,
            ],
            [
                'title' => 'Category Spotlight',
                'slug' => 'category-spotlight',
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 58,
                'headline' => 'New Bakery Drops',
                'description' => 'Explore artisan breads with 12% off.',
                'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                'cta_type' => PopupCampaign::CTA_CATEGORY,
                'cta_value' => '22',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/bakery.jpg',
                'form_enabled' => false,
                'show_on_pages' => ['category'],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_ON_LOAD,
                'show_every' => 10,
                'max_impressions' => 5,
            ],
            [
                'title' => 'Cart Reminder Fullscreen',
                'slug' => 'cart-reminder-fullscreen',
                'type' => PopupCampaign::TYPE_FULLSCREEN,
                'status' => PopupCampaign::STATUS_PAUSED,
                'priority' => 50,
                'headline' => 'Your cart is waiting',
                'subheadline' => 'Complete checkout and unlock delivery',
                'description' => 'Add more items and get free delivery.',
                'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/cart',
                'media_type' => PopupCampaign::MEDIA_GIF,
                'media_path' => '/storage/popups/cart.gif',
                'form_enabled' => false,
                'show_on_pages' => ['cart'],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 5,
                'show_every' => 20,
                'max_impressions' => 2,
            ],
            [
                'title' => 'Product Lock-In',
                'slug' => 'product-lock-in',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 45,
                'headline' => 'Add-ons just for this product',
                'description' => 'Bundle teas with your selected item for 18% off.',
                'button_text' => PopupCampaign::BUTTON_CLAIM_OFFER,
                'cta_type' => PopupCampaign::CTA_PRODUCT,
                'cta_value' => '612',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/product.jpg',
                'form_enabled' => false,
                'show_on_pages' => ['product'],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_EXIT_INTENT,
                'show_every' => 5,
                'max_impressions' => 1,
            ],
            [
                'title' => 'VIP Signup Form',
                'slug' => 'vip-form',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_DRAFT,
                'priority' => 35,
                'headline' => 'Become a VIP Shopper',
                'description' => 'Share your details to unlock previews.',
                'button_text' => PopupCampaign::BUTTON_SUBSCRIBE,
                'cta_type' => PopupCampaign::CTA_FORM,
                'cta_value' => '/vip',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/vip.jpg',
                'form_enabled' => true,
                'form_fields' => ['name', 'email', 'custom_text'],
                'show_on_pages' => ['home'],
                'audience_type' => PopupCampaign::AUDIENCE_LOGGED_IN,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 8,
                'show_every' => 10,
                'max_impressions' => 3,
            ],
            [
                'title' => 'Sale Explorer',
                'slug' => 'sale-explorer',
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 40,
                'headline' => 'Flash Sale Alert',
                'description' => 'Check /sale or /black-friday for secret prices.',
                'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/sale',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/sale.jpg',
                'form_enabled' => false,
                'show_on_pages' => [
                    ['name' => 'custom_url', 'value' => ['/sale', '/black-friday']],
                ],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 5,
                'show_every' => 10,
                'max_impressions' => 5,
            ],
            [
                'title' => 'Exclude Checkout',
                'slug' => 'exclude-checkout',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 30,
                'headline' => 'Installment Offer',
                'description' => 'Check how to split payments everywhere except checkout.',
                'button_text' => PopupCampaign::BUTTON_SUBSCRIBE,
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/installments',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/installment.jpg',
                'form_enabled' => false,
                'show_on_pages' => [
                    ['name' => 'exclude_pages', 'value' => ['/checkout']],
                ],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_ON_LOAD,
                'show_every' => 5,
                'max_impressions' => 5,
            ],
            [
                'title' => 'Minimal Target',
                'slug' => 'minimal-target',
                'type' => PopupCampaign::TYPE_SLIDE_IN,
                'status' => PopupCampaign::STATUS_ARCHIVED,
                'priority' => 25,
                'headline' => 'Archive Mode',
                'description' => 'This campaign is archived and should never show.',
                'button_text' => PopupCampaign::BUTTON_CLAIM_OFFER,
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/archive',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/archive.jpg',
                'form_enabled' => false,
                'show_on_pages' => [],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 3,
                'show_every' => 15,
                'max_impressions' => 2,
            ],
            [
                'title' => 'Custom URL Only',
                'slug' => 'custom-url-only',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 20,
                'headline' => 'Special landing spot',
                'description' => 'Visible only over /promo-pages.',
                'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/promo-pages',
                'media_type' => PopupCampaign::MEDIA_GIF,
                'media_path' => '/storage/popups/promo.gif',
                'form_enabled' => false,
                'show_on_pages' => [
                    ['name' => 'custom_url', 'value' => ['/promo-pages']],
                ],
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_ON_LOAD,
                'show_every' => 5,
                'max_impressions' => 3,
            ],
            [
                'title' => 'Empty Scope Campaign',
                'slug' => 'empty-scope',
                'type' => PopupCampaign::TYPE_MODAL,
                'status' => PopupCampaign::STATUS_ACTIVE,
                'priority' => 10,
                'headline' => 'Site-wide Reminder',
                'description' => 'No page constraints apply — will show everywhere.',
                'button_text' => PopupCampaign::BUTTON_SHOP_NOW,
                'cta_type' => PopupCampaign::CTA_URL,
                'cta_value' => '/',
                'media_type' => PopupCampaign::MEDIA_IMAGE,
                'media_path' => '/storage/popups/global.jpg',
                'form_enabled' => false,
                'show_on_pages' => null,
                'audience_type' => PopupCampaign::AUDIENCE_ALL,
                'trigger_type' => PopupCampaign::TRIGGER_DELAY,
                'trigger_value' => 4,
                'show_every' => 5,
                'max_impressions' => 5,
            ],
        ];

        foreach ($campaigns as $campaign) {
            $normalizedCampaign = $this->normalizeCampaign($campaign);
            PopupCampaign::updateOrCreate(['slug' => $normalizedCampaign['slug']], $normalizedCampaign);
        }
    }

    private function normalizeCampaign(array $campaign): array
    {
        foreach (['title', 'headline', 'subheadline', 'description'] as $field) {
            if (array_key_exists($field, $campaign)) {
                $campaign[$field] = $this->toTranslationPayload($campaign[$field]);
            }
        }

        if (! isset($campaign['button_url']) && isset($campaign['cta_value'])) {
            $campaign['button_url'] = $this->resolveButtonUrl(
                $campaign['cta_type'] ?? null,
                $campaign['cta_value']
            );
        }

        unset($campaign['cta_type'], $campaign['cta_value']);

        return $campaign;
    }

    private function toTranslationPayload(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        return [
            'en' => $text,
            'ar' => $text,
        ];
    }

    private function resolveButtonUrl(?string $ctaType, mixed $ctaValue): ?string
    {
        if ($ctaValue === null) {
            return null;
        }

        $value = trim((string) $ctaValue);
        if ($value === '') {
            return null;
        }

        return match ($ctaType) {
            PopupCampaign::CTA_PRODUCT => '/products/' . $value,
            PopupCampaign::CTA_CATEGORY => '/categories/' . $value,
            PopupCampaign::CTA_COUPON => '/coupon/' . $value,
            default => $value,
        };
    }
}
