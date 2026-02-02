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
                'code' => 'order_completion',
                'title' => 'Order Completion Points',
                'type' => 'percentage',
                'value' => 5, // 5% of order value
                'min_order_amount' => 5.00,
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
            [
                'code' => 'daily_login',
                'title' => 'Daily Login Bonus',
                'type' => 'fixed',
                'value' => 5,
                'min_order_amount' => null,
                'expires_after_days' => 30,
                'is_active' => false, // Disabled by default
            ],
            [
                'code' => 'large_order_bonus',
                'title' => 'Large Order Bonus',
                'type' => 'fixed',
                'value' => 25,
                'min_order_amount' => 100.00,
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