<?php

namespace Database\Seeders;

use App\Models\ContactMethod;
use Illuminate\Database\Seeder;

class DriverContactMethodSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'key' => 'support_driver_phone',
                'type' => 'number',
                'value' => '+962790000000',
            ],
            [
                'key' => 'support_driver_whatsapp',
                'type' => 'whts',
                'value' => '+962790000000',
            ],
            [
                'key' => 'support_driver_telegram',
                'type' => 'url',
                'value' => 'https://t.me/tikmool_driver_support',
            ],
            [
                'key' => 'support_driver_email',
                'type' => 'email',
                'value' => 'driver.support@tikmool.com',
            ],
            [
                'key' => 'share_driver_app',
                'type' => 'url',
                'value' => 'https://tikmool.app/driver',
            ],
        ];

        foreach ($items as $item) {
            ContactMethod::updateOrCreate(
                ['key' => $item['key']],
                $item
            );
        }
    }
}
