<?php

namespace App\Http\Requests\Admin\DeliveryDistanceRange;

use App\Models\DeliveryDistanceRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_distance' => 'sometimes|numeric|min:0',
            'max_distance' => 'sometimes|nullable|numeric',
            'multiplier' => 'sometimes|numeric|gt:0',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $id = $this->route('delivery_distance_range') ?? $this->route('id');
            $model = DeliveryDistanceRange::query()->findOrFail((int) $id);

            if (
                !$this->has('min_distance')
                && !$this->has('max_distance')
                && !$this->has('multiplier')
            ) {
                $validator->errors()->add('payload', 'يرجى إرسال حقل واحد على الأقل للتحديث');
                return;
            }

            $min = $this->has('min_distance')
                ? (float) $this->input('min_distance')
                : (float) $model->min_distance;

            $max = $this->has('max_distance')
                ? $this->input('max_distance')
                : $model->max_distance;
            $max = $max !== null ? (float) $max : null;

            if ($max !== null && $max <= $min) {
                $validator->errors()->add(
                    'max_distance',
                    'الحد الأعلى للمسافة يجب أن يكون أكبر من الحد الأدنى.'
                );
                return;
            }

            if (DeliveryDistanceRange::hasOverlap($min, $max, $id ? (int) $id : null)) {
                $validator->errors()->add('range', 'يوجد تعارض في المسافات مع Range آخر');
            }
        });
    }
}
