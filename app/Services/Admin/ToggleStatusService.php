<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use Illuminate\Support\Str;

class ToggleStatusService
{
    /**
     * Map of type names to model classes
     */
    protected array $modelMap = [
        'vendor_user' => \App\Models\VendorUser::class,
        'vendor_package' => \App\Models\VendorPackage::class,
        'vendor' => \App\Models\Vendor::class,
        'user_basket_schedule' => \App\Models\UserBasketSchedule::class,
        'user' => \App\Models\User::class,
        'system_setting' => \App\Models\SystemSetting::class,
        'store_user' => \App\Models\StoreUser::class,
        'store' => \App\Models\Store::class,
        'shop' => \App\Models\Shop::class,
        'schedule' => \App\Models\Schedule::class,
        'sale_country' => \App\Models\SaleCountry::class,
        'recipe' => \App\Models\Recipe::class,
        'promotion' => \App\Models\Promotion::class,
        'point_rule' => \App\Models\PointRule::class,
        'payment_method' => \App\Models\PaymentMethod::class,
        'package' => \App\Models\Package::class,
        'media' => \App\Models\Media::class,
        'language' => \App\Models\Language::class,
        'icon' => \App\Models\Icon::class,
        'currency' => \App\Models\Currency::class,
        'category' => \App\Models\Category::class,
        'basket_schedule' => \App\Models\BasketSchedule::class,
        'driver' => \App\Models\Driver::class,
        'country' => \App\Models\Country::class,
        'brand' => \App\Models\Brand::class,
        'banner' => \App\Models\Banner::class,
        'faq' => \App\Models\Faq::class,
        'service' => \App\Models\Service::class,
        'area' => \App\Models\Area::class,
        'city' => \App\Models\City::class,
        'governorate' => \App\Models\Governorate::class,
        'badge' => \App\Models\Badge::class,
        'color' => \App\Models\Color::class,
        'coupon' => \App\Models\Coupon::class,
    ];

    /**
     * Toggle is_active status for a model
     *
     * @param string $type
     * @param int $id
     * @param bool $isActive
     * @return array
     * @throws CustomExceptionWithMessage
     */
    public function toggleStatus(string $type, int $id, bool $isActive): bool
    {
        // Get model class from map
        if (!isset($this->modelMap[$type])) {
            throw new CustomExceptionWithMessage(__('custom.invalid_model_type'));
        }

        $modelClass = $this->modelMap[$type];

        // Find the record
        $record = $modelClass::find($id);

        if (!$record) {
            throw new CustomExceptionWithMessage(__('custom.record_not_found'));
        }

        // Update is_active status
        $record->is_active = $isActive;
        $record->save();

        return true;
    }
}
