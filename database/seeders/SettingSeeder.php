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
        // جهات الاتصال
        Setting::create([
            'key' => 'whts',
            'value' => '0999999999',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'phone',
            'value' => '0999999999',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'email',
            'value' => 'tikmol@tikmol.com',
            'type' => 'string',
        ]);

        // واجهة اللوغين
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
    }
}
