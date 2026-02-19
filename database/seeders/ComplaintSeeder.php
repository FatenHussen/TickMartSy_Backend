<?php

namespace Database\Seeders;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        // Get some users and orders
        $users = User::limit(5)->get();
        $orders = Order::limit(10)->get();

        if ($users->isEmpty() || $orders->isEmpty()) {
            $this->command->warn('No users or orders found. Skipping complaint seeding.');
            return;
        }

        $complaints = [
            // New Complaints
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'المنتج وصل متأخر جداً وكان التوصيل سيء',
                'type' => ComplaintType::ORDER->value,
                'status' => ComplaintStatus::NEW->value,
                'admin_response' => null,
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'المنتج المستلم مختلف عن المطلوب في الطلب',
                'type' => ComplaintType::PRODUCT->value,
                'status' => ComplaintStatus::NEW->value,
                'admin_response' => null,
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'السائق كان غير محترم وتعامل بطريقة سيئة',
                'type' => ComplaintType::DRIVER->value,
                'status' => ComplaintStatus::NEW->value,
                'admin_response' => null,
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'المتجر لم يلتزم بالمواصفات المذكورة للمنتج',
                'type' => ComplaintType::MERCHANT->value,
                'status' => ComplaintStatus::NEW->value,
                'admin_response' => null,
                'images' => null,
            ],

            // Resolved Complaints
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'الطلب وصل ناقص بعض المنتجات',
                'type' => ComplaintType::ORDER->value,
                'status' => ComplaintStatus::RESOLVED->value,
                'admin_response' => 'تم التواصل مع المتجر وسيتم إرسال المنتجات الناقصة خلال 24 ساعة. نعتذر عن الإزعاج.',
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'المنتج وصل تالف والتغليف مفتوح',
                'type' => ComplaintType::PRODUCT->value,
                'status' => ComplaintStatus::RESOLVED->value,
                'admin_response' => 'تم استرجاع المبلغ كاملاً إلى محفظتك. شكراً لتفهمك.',
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'السائق تأخر كثيراً في التوصيل بدون سبب واضح',
                'type' => ComplaintType::DRIVER->value,
                'status' => ComplaintStatus::RESOLVED->value,
                'admin_response' => 'تم اتخاذ الإجراءات اللازمة مع السائق. نعتذر عن التأخير وتم إضافة رصيد نقاط إلى حسابك.',
                'images' => null,
            ],

            // Rejected Complaints
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'المنتج لا يعجبني واريد استرجاع المبلغ',
                'type' => ComplaintType::PRODUCT->value,
                'status' => ComplaintStatus::REJECTED->value,
                'admin_response' => 'عذراً، المنتج مطابق للمواصفات المذكورة. سياسة الاسترجاع لا تشمل عدم الإعجاب الشخصي.',
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'السعر مرتفع جداً مقارنة بالمتاجر الأخرى',
                'type' => ComplaintType::MERCHANT->value,
                'status' => ComplaintStatus::REJECTED->value,
                'admin_response' => 'الأسعار يحددها المتجر وفقاً لسياسته. يمكنك المقارنة بين المتاجر المختلفة على المنصة.',
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'التوصيل استغرق يومين وهذا كثير',
                'type' => ComplaintType::ORDER->value,
                'status' => ComplaintStatus::REJECTED->value,
                'admin_response' => 'وقت التوصيل المذكور كان 2-3 أيام عمل. الطلب وصل ضمن الوقت المحدد.',
                'images' => null,
            ],

            // More New Complaints
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'جودة المنتج أقل بكثير من المتوقع حسب الصور',
                'type' => ComplaintType::PRODUCT->value,
                'status' => ComplaintStatus::NEW->value,
                'admin_response' => null,
                'images' => null,
            ],
            [
                'user_id' => $users->random()->id,
                'order_id' => $orders->random()->id,
                'message' => 'المتجر لم يرد على استفساراتي قبل الشراء',
                'type' => ComplaintType::MERCHANT->value,
                'status' => ComplaintStatus::NEW->value,
                'admin_response' => null,
                'images' => null,
            ],
        ];

        foreach ($complaints as $complaint) {
            Complaint::create($complaint);
        }

        $this->command->info('Complaints seeded successfully!');
    }
}

