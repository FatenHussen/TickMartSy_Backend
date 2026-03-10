<?php

namespace App\Services\Vendor;

use App\Models\Product;
use App\Models\PromotionRequest;
use App\Models\VendorSubscription;
use App\Models\VendorUser;
use Illuminate\Support\Facades\Schema;

class VendorSubscriptionQuotaService
{
    public function getActiveSubscription(?VendorUser $user): ?VendorSubscription
    {
        if (!$user) {
            return null;
        }

        $subscription = null;

        if (Schema::hasColumn('vendor_subscriptions', 'vendor_id')) {
            $subscription = VendorSubscription::query()
                ->where('vendor_id', $user->vendor_id)
                ->where('status', 'active')
                ->where('ends_at', '>=', now()->toDateString())
                ->orderByDesc('ends_at')
                ->first();
        }

        if ($subscription) {
            return $subscription;
        }

        // Fallback for legacy records without vendor_id
        $shop = $user->shops()->first();

        return $shop?->activeSubscription();
    }

    public function getUsageSnapshot(?VendorUser $user): array
    {
        if (!$user) {
            return $this->emptySnapshot('no_user');
        }

        $subscription = $this->getActiveSubscription($user);
        if (!$subscription || !$subscription->isActive()) {
            return $this->emptySnapshot('inactive_subscription');
        }

        $package = $subscription->package;
        if (!$package) {
            return $this->emptySnapshot('no_package');
        }

        $productsUsed = Product::where('vendor_id', $user->vendor_id)->count();
        $campaignsUsed = PromotionRequest::where('vendor_id', $user->vendor_id)->count();

        $maxProducts = $package->max_products;
        $maxCampaigns = $package->max_campaigns;

        $remainingProducts = $this->remaining($maxProducts, $productsUsed);
        $remainingCampaigns = $this->remaining($maxCampaigns, $campaignsUsed);

        $canCreateProduct = $this->canCreate($maxProducts, $remainingProducts);
        $canCreateCampaign = $this->canCreate($maxCampaigns, $remainingCampaigns);
        $canCreateBanner = $canCreateCampaign && (bool) $package->has_banner_ad;

        $daysLeft = $subscription->ends_at
            ? max(0, now()->diffInDays($subscription->ends_at, false))
            : null;

        return [
            'has_active' => true,
            'reason' => null,
            'shop' => null,
            'subscription' => $subscription,
            'package' => $package,
            'products_used' => $productsUsed,
            'campaigns_used' => $campaignsUsed,
            'max_products' => $maxProducts,
            'max_campaigns' => $maxCampaigns,
            'remaining_products' => $remainingProducts,
            'remaining_campaigns' => $remainingCampaigns,
            'can_create_product' => $canCreateProduct,
            'can_create_campaign' => $canCreateCampaign,
            'can_create_banner' => $canCreateBanner,
            'days_left' => $daysLeft,
        ];
    }

    private function remaining(?int $max, int $used): ?int
    {
        if ($max === null) {
            return null;
        }

        return max($max - $used, 0);
    }

    private function canCreate(?int $max, ?int $remaining): bool
    {
        return $max === null || ($remaining !== null && $remaining > 0);
    }

    private function emptySnapshot(string $reason): array
    {
        return [
            'has_active' => false,
            'reason' => $reason,
            'shop' => null,
            'subscription' => null,
            'package' => null,
            'products_used' => 0,
            'campaigns_used' => 0,
            'max_products' => null,
            'max_campaigns' => null,
            'remaining_products' => null,
            'remaining_campaigns' => null,
            'can_create_product' => false,
            'can_create_campaign' => false,
            'can_create_banner' => false,
            'days_left' => null,
        ];
    }
}
