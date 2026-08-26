<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Quick Order (طلب سريع) home section — visibility + styling for Flutter/Web.
     */
    public function up(): void
    {
        $defaults = [
            [
                'key' => 'quick_order_enabled',
                'value' => true,
                'type' => 'boolean',
            ],
            [
                'key' => 'quick_order_background_image',
                'value' => null,
                'type' => 'file',
            ],
            [
                'key' => 'quick_order_background_color',
                'value' => '#FFE8D6',
                'type' => 'string',
            ],
            [
                'key' => 'quick_order_card_background_color',
                'value' => '#FFFFFF',
                'type' => 'string',
            ],
            [
                'key' => 'quick_order_card_variant',
                'value' => 'horizontal',
                'type' => 'string',
            ],
            [
                'key' => 'quick_order_badge',
                'value' => [
                    'ar' => 'طلب عاجل',
                    'en' => 'Urgent order',
                ],
                'type' => 'json',
            ],
            [
                'key' => 'quick_order_title',
                'value' => [
                    'ar' => 'تحتاجه الآن؟',
                    'en' => 'Need it now?',
                ],
                'type' => 'json',
            ],
            [
                'key' => 'quick_order_subtitle',
                'value' => [
                    'ar' => 'اكتب ما تريده مثل قائمة السوق. نسعّره، توافق، ونوصّله للباب.',
                    'en' => 'Write what you want like a market list. We price it, you approve, we bring it to the door.',
                ],
                'type' => 'json',
            ],
            [
                'key' => 'quick_order_cta',
                'value' => [
                    'ar' => 'اطلب الآن',
                    'en' => 'Order now',
                ],
                'type' => 'json',
            ],
            [
                'key' => 'quick_order_steps',
                'value' => [
                    [
                        'number' => 1,
                        'title' => ['ar' => 'اكتبه', 'en' => 'Write it'],
                        'description' => ['ar' => 'قائمتك، بكلماتك', 'en' => 'Your list, in your words'],
                        'icon' => 'edit',
                    ],
                    [
                        'number' => 2,
                        'title' => ['ar' => 'نسعّره', 'en' => 'We price it'],
                        'description' => ['ar' => 'أسعار واضحة قبل الدفع', 'en' => 'Clear prices before you pay'],
                        'icon' => 'price',
                    ],
                    [
                        'number' => 3,
                        'title' => ['ar' => 'نوصّل', 'en' => 'We deliver'],
                        'description' => ['ar' => 'للباب بسرعة', 'en' => 'To your door, fast'],
                        'icon' => 'delivery',
                    ],
                ],
                'type' => 'json',
            ],
        ];

        foreach ($defaults as $row) {
            Setting::updateOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'type' => $row['type'],
                ]
            );
        }
    }

    public function down(): void
    {
        Setting::query()
            ->whereIn('key', [
                'quick_order_enabled',
                'quick_order_background_image',
                'quick_order_background_color',
                'quick_order_card_background_color',
                'quick_order_card_variant',
                'quick_order_badge',
                'quick_order_title',
                'quick_order_subtitle',
                'quick_order_cta',
                'quick_order_steps',
            ])
            ->delete();
    }
};
