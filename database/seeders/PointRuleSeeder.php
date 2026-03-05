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
                'min_order_amount' => null,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
            [
                'code' => 'order_completion',
                'title' => 'Order Completion Points',
                'type' => 'fixed',
                'value' => 100,
                'min_order_amount' => null,
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
                'code' => 'purchase_amount_threshold',
                'title' => 'Purchase Amount Threshold Bonus',
                'type' => 'fixed',
                'value' => 200,
                'min_order_amount' => 100.00,
                'expires_after_days' => 365,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            PointRule::updateOrCreate(
                ['code' => $rule['code']],
                $rule
            );
        }

        $this->command->info('Created/Updated ' . count($rules) . ' point rules.');
    }
}
