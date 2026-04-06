<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'key' => 'payment_default',
            'value' => '1',
            'type' => 'integer',
        ]);
        // جهات الاتصال
        Setting::create([
            'key' => 'whts',
            'value' => '0999999999',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'phone',
            'value' => '+963940404018',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'email',
            'value' => 'tikmol@tikmol.com',
            'type' => 'string',
        ]);

        // واجهة اللوجين
        Setting::create([
            'key' => 'login_image',
            'value' => 'settings/logo.png',
            'type' => 'file',
        ]);

        Setting::create([
            'key' => 'login_link',
            'value' => 'https://example.com/login',
            'type' => 'string',
        ]);

        // واجهة الترحيب
        Setting::create([
            'key' => 'welcome_image',
            'value' => 'settings/welcome.png',
            'type' => 'file',
        ]);

        Setting::create([
            'key' => 'welcome_text',
            'value' => [
                'en' => 'Hello',
                'ar' => 'مرحبا',
            ],
            'type' => 'json',
        ]);

        // ألوان الواجهة
        Setting::create([
            'key' => 'main_color',
            'value' => '#FFA000',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'text_color',
            'value' => '#1F2937',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'second_color',
            'value' => '#F3F4F6',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'dark_main_color',
            'value' => '#F1F1F1',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'dark_text_color',
            'value' => '#FFE8A3',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'dark_second_color',
            'value' => '#FFF4CC',
            'type' => 'string',
        ]);
    }
}
