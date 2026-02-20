<?php

namespace Database\Seeders;

use App\Models\PointRule;
use Illuminate\Database\Seeder;

class PointRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            [
                'code' => 'participating_stores',
                'title' => 'Place orders from participating stores',
                'type' => 'percentage',
                'value' => 5, // 5% of order value
                'min_order_amount' => 5.00,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
            [
                'code' => 'special_campaigns',
                'title' => 'Join special campaigns (e.g. 2× points today)',
                'type' => 'fixed',
                'value' => 50,
                'min_order_amount' => null,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
            [
                'code' => 'subscription_packages',
                'title' => 'Use eligible subscription packages',
                'type' => 'fixed',
                'value' => 100,
                'min_order_amount' => null,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
            [
                'code' => 'user_registration',
                'title' => 'User Registration Bonus',
                'type' => 'fixed',
                'value' => 100,
                'min_order_amount' => null,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
            [
                'code' => 'first_order',
                'title' => 'First Order Bonus',
                'type' => 'fixed',
                'value' => 50,
                'min_order_amount' => 10.00,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
            [
                'code' => 'product_review',
                'title' => 'Product Review Points',
                'type' => 'fixed',
                'value' => 10,
                'min_order_amount' => null,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            PointRule::create($rule);
        }

        $this->command->info('Created ' . count($rules) . ' point rules.');
    }
}
