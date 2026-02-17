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
            'key' => 'whts',
            'value' => '0999999999',
            'type' => 'string'
        ]);
        Setting::create([
            'key' => 'phone',
            'value' => '0999999999',
            'type' => 'string'
        ]);
        Setting::create([
            'key' => 'email',
            'value' => 'tikmol@tikmol.com',
            'type' => 'string'
        ]);
    }
}
