<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => [
                    'en' => 'How can I place an order?',
                    'ar' => 'كيف يمكنني تقديم طلب؟',
                ],
                'answer' => [
                    'en' => 'You can place an order through our website or by contacting our branches directly.',
                    'ar' => 'يمكنك تقديم الطلب عبر موقعنا الإلكتروني أو بالتواصل مع فروعنا مباشرة.',
                ],
            ],
            [
                'question' => [
                    'en' => 'What are the payment methods?',
                    'ar' => 'ما هي طرق الدفع؟',
                ],
                'answer' => [
                    'en' => 'We accept cash, credit/debit cards, and online payments.',
                    'ar' => 'نقبل الدفع نقداً، وبطاقات الائتمان/الخصم، والدفع الإلكتروني.',
                ],
            ],
            [
                'question' => [
                    'en' => 'Do you offer delivery services?',
                    'ar' => 'هل تقدمون خدمات التوصيل؟',
                ],
                'answer' => [
                    'en' => 'Yes, we provide delivery to all our branch locations and some surrounding areas.',
                    'ar' => 'نعم، نقدم التوصيل لجميع فروعنا وبعض المناطق المحيطة.',
                ],
            ],
            [
                'question' => [
                    'en' => 'What is the warranty on your products?',
                    'ar' => 'ما هي الضمانات على منتجاتكم؟',
                ],
                'answer' => [
                    'en' => 'All products come with a standard manufacturer warranty. Specific warranty details are available per product.',
                    'ar' => 'جميع المنتجات تأتي مع ضمان الشركة المصنعة. تتوفر تفاصيل الضمان لكل منتج على حدة.',
                ],
            ],
            [
                'question' => [
                    'en' => 'How can I contact customer support?',
                    'ar' => 'كيف يمكنني التواصل مع خدمة العملاء؟',
                ],
                'answer' => [
                    'en' => 'You can contact us via phone, email, or through our website contact form.',
                    'ar' => 'يمكنك التواصل معنا عبر الهاتف أو البريد الإلكتروني أو نموذج الاتصال على موقعنا.',
                ],
            ],
        ];


        $types = ['orders', 'delivery', 'payments', 'account', 'stores & drivers', 'other'];

        foreach ($types as $type) {
            foreach ($faqs as $faq) {
                Faq::create([
                    'question' => $faq['question'],
                    'answer'   => $faq['answer'],
                    'type'     => $type,
                ]);
            }
        }
    }
}
