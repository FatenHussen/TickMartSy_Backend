<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class ExchangeSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // إعدادات عامة للاستبدال
            [
                'key' => 'min_exchange_points',
                'value' => '100',
                'type' => 'integer',
                'group' => 'points_exchange',
                'title' => 'الحد الأدنى لاستبدال النقاط',
                'description' => 'أقل عدد نقاط يمكن استبدالها',
            ],
            [
                'key' => 'max_exchange_points',
                'value' => '10000',
                'type' => 'integer',
                'group' => 'points_exchange',
                'title' => 'الحد الأقصى لاستبدال النقاط',
                'description' => 'أكبر عدد نقاط يمكن استبدالها في المرة الواحدة',
            ],
            [
                'key' => 'points_expiry_months',
                'value' => '12',
                'type' => 'integer',
                'group' => 'points_exchange',
                'title' => 'مدة انتهاء صلاحية النقاط (بالأشهر)',
                'description' => 'عدد الأشهر قبل انتهاء صلاحية النقاط',
            ],
            [
                'key' => 'points_exchange_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'points_exchange',
                'title' => 'تفعيل نظام استبدال النقاط',
                'description' => 'تفعيل أو تعطيل نظام استبدال النقاط',
            ],

            // إعدادات الكوبونات
            [
                'key' => 'exchange_coupon_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'points_exchange',
                'title' => 'تفعيل استبدال النقاط بكوبونات',
                'description' => 'السماح للمستخدمين باستبدال النقاط بكوبونات خصم',
            ],
            [
                'key' => 'coupon_discount_rate',
                'value' => '0.01',
                'type' => 'number',
                'group' => 'points_exchange',
                'title' => 'معدل تحويل النقاط لخصم',
                'description' => 'كم دولار خصم مقابل كل نقطة (مثال: 0.01 = 1 نقطة = 1 سنت)',
            ],

            // إعدادات التوصيل المجاني
            [
                'key' => 'exchange_free_delivery_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'points_exchange',
                'title' => 'تفعيل استبدال النقاط بتوصيل مجاني',
                'description' => 'السماح للمستخدمين باستبدال النقاط بتوصيل مجاني',
            ],
            [
                'key' => 'free_delivery_points_cost',
                'value' => '200',
                'type' => 'integer',
                'group' => 'points_exchange',
                'title' => 'تكلفة التوصيل المجاني بالنقاط',
                'description' => 'عدد النقاط المطلوبة للحصول على توصيل مجاني',
            ],

            // إعدادات الهدايا
            [
                'key' => 'exchange_gifts_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'points_exchange',
                'title' => 'تفعيل استبدال النقاط بهدايا',
                'description' => 'السماح للمستخدمين باستبدال النقاط بهدايا فعلية',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Created ' . count($settings) . ' exchange settings.');
    }
}