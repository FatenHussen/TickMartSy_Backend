<?php

namespace App\Http\Requests\Admin\DeliveryDistanceRange;

use App\Models\DeliveryDistanceRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'min_distance' => 'required|numeric|min:0',
            'max_distance' => 'nullable|numeric|gt:min_distance',
            'multiplier' => 'required|numeric|gt:0',
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $min = (float) $this->input('min_distance');
            $max = $this->input('max_distance');
            $max = $max !== null ? (float) $max : null;

            if (DeliveryDistanceRange::hasOverlap($min, $max)) {
                $validator->errors()->add('range', 'يوجد تعارض في المسافات مع Range آخر');
            }
        });
    }
}
