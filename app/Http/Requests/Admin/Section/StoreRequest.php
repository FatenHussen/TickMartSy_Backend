<?php

namespace App\Http\Requests\Admin\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedTypes = array_keys(config('section_items'));

        return [
            'name' => ['required', 'array'],
            'name.ar' => ['required', 'string', 'max:255'],
            'name.en' => ['required', 'string', 'max:255'],

            'type' => ['required', 'string', Rule::in($allowedTypes)],

            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*.item_id' => ['required', 'integer'],
            'item_ids.*.link' => ['nullable', 'string', 'max:255'],
            'item_ids.*.order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $typeConfig = config("section_items.$type");

            // تأكد أن type موجود في config (أمان إضافي)
            if (!$typeConfig) {
                $validator->errors()->add('type', 'نوع السيكشن غير موجود في الإعدادات.');
                return; // وقف باقي التحقق إذا النوع غير موجود
            }

            // تأكد أن كل item_ids تكون أرقام موجبة
            foreach ($this->input('item_ids', []) as $index => $item) {
                if (!isset($item['item_id']) || $item['item_id'] <= 0) {
                    $validator->errors()->add(
                        "item_ids.$index.item_id",
                        "رقم العنصر غير صحيح"
                    );
                }
            }

            // أي قواعد إضافية مستقبلية ممكن تضيفها هنا
        });
    }
}
