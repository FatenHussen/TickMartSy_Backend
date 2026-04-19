<?php

namespace App\Http\Requests\Concerns;

use App\Authorization\CityAccess;
use App\Models\Admin;
use App\Models\Shop;
use Illuminate\Validation\Validator;

trait ValidatesDriverCityScope
{
    public function withValidatorForDriverCityScope(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $admin = auth('admin')->user();
            if (! $admin instanceof Admin) {
                return;
            }

            $access = CityAccess::for($admin);
            if ($access->hasFullAccess()) {
                return;
            }

            if ($this->has('city_ids') && ! $access->allCityIdsAreAssignable($this->normalizedDriverCityIds())) {
                $validator->errors()->add(
                    'city_ids',
                    'The selected cities are not within your allowed scope.'
                );
            }

            if ($this->has('shop_ids')) {
                foreach ($this->normalizedDriverShopIds() as $shopId) {
                    $shop = Shop::query()->find($shopId);
                    if (! $shop || ! $access->canAccessShop($shop)) {
                        $validator->errors()->add(
                            'shop_ids',
                            'One or more shops are outside your allowed cities.'
                        );

                        return;
                    }
                }
            }
        });
    }

    /**
     * @return list<int>
     */
    protected function normalizedDriverCityIds(): array
    {
        $raw = $this->input('city_ids', []);
        $ids = [];
        foreach ($raw as $item) {
            if (is_array($item) && isset($item['id'])) {
                $ids[] = (int) $item['id'];
            } elseif (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }

        return array_values(array_filter($ids));
    }

    /**
     * @return list<int>
     */
    protected function normalizedDriverShopIds(): array
    {
        $raw = $this->input('shop_ids', []);
        $ids = [];
        foreach ($raw as $item) {
            if (is_array($item) && isset($item['id'])) {
                $ids[] = (int) $item['id'];
            } elseif (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }

        return array_values(array_filter($ids));
    }
}
