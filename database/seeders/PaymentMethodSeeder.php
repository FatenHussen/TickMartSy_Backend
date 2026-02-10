<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name'       => 'Cash',
                'code'       => 'cash',
                'icon'       => 'cash.png',
                'is_active'  => true,
                'sort_order' => 1,
                'config'     => null,
            ],
            [
                'name'       => 'Credit Card',
                'code'       => 'card',
                'icon'       => 'payments/image1.png',
                'is_active'  => true,
                'sort_order' => 2,
                'config'     => [
                    'provider' => 'stripe',
                    'public_key' => env('STRIPE_KEY')  ?? null,
                ],
            ],
            [
                'name'       => 'PayPal',
                'code'       => 'paypal',
                'icon'       => 'payments/image2.png',
                'is_active'  => true,
                'sort_order' => 3,
                'config'     => [
                    'client_id' => env('PAYPAL_CLIENT_ID') ?? null,
                ],
            ],
            [
                'name'       => 'Wallet',
                'code'       => 'wallet',
                'icon'       => 'payments/image3.png',
                'is_active'  => true,
                'sort_order' => 4,
                'config'     => null,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
