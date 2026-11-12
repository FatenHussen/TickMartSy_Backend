<?php

namespace App\Services\User;

use App\Models\SystemSetting;

/**
 * Order tax, shown to the customer as its own line.
 *
 * The rate is a percentage held in system settings (`tikmart_tax_rate`), so
 * the dashboard can change it without a deploy. The amount is always money,
 * never a percentage: the frontend renders what it is given and does not add
 * tax into the total itself.
 */
class TaxService
{
    /** Percentage applied to the taxable base, e.g. 10 for 10%. */
    public function rate(): float
    {
        return max(0.0, (float) SystemSetting::get('tikmart_tax_rate', 0));
    }

    /**
     * Tax owed on an order.
     *
     * Charged on goods after discounts, not on delivery: the customer is taxed
     * on what they actually pay for the products.
     */
    public function calculate(float $subtotalAfterDiscounts): float
    {
        $rate = $this->rate();
        if ($rate <= 0) {
            return 0.0;
        }

        return round(max(0.0, $subtotalAfterDiscounts) * $rate / 100, 2);
    }
}
