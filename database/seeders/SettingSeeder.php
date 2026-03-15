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
            'value' => '#E4F0FB',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'text_color',
            'value' => '#2A2A2A',
            'type' => 'string',
        ]);

        Setting::create([
            'key' => 'second_color',
            'value' => '#e27676',
            'type' => 'string',
        ]);
    }
}
