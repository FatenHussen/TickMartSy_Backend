<?php

namespace App\Http\Requests\User\Shop;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'governorate_id' => $this->normalizeId($this->input('governorate_id', $this->input('governorate'))),
            'category_id' => $this->normalizeId($this->input('category_id', $this->input('category'))),
            'brand_id' => $this->normalizeId($this->input('brand_id', $this->input('brand'))),
            'city_id' => $this->normalizeId($this->input('city_id', $this->input('city'))),
            'area_id' => $this->normalizeId($this->input('area_id', $this->input('area'))),
            'vendor_id' => $this->normalizeId($this->input('vendor_id', $this->input('vendor'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'area_id' => ['nullable', 'required_if:type,zone', 'exists:areas,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'governorate_id' => ['nullable', 'exists:governorates,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'is_active' => ['nullable', 'boolean'],
            'is_service_provider' => ['nullable', 'boolean'],
            'is_restaurant' => ['nullable', 'boolean'],
            'shop_type' => ['nullable', 'in:restaurant,service_provider,store'],
            'type' => ['nullable', 'in:nearby,near_me,offers,top_rated,most_rated,active,open,close,newest,zone'],
            'is_open_now' => ['nullable', 'boolean'],
            'pricing_tier' => ['nullable', 'in:cheap,medium,expensive'],
            'search' => ['nullable', 'string', 'max:255'],
            'lat' => ['required_if:type,nearby,near_me', 'numeric', 'between:-90,90'],
            'lng' => ['required_if:type,nearby,near_me', 'numeric', 'between:-180,180'],
            'sort_by' => ['nullable', 'in:newest,oldest,most_rated,rating_desc,rating_asc,near_me'],
            'max_distance' => ['nullable', 'numeric', 'min:1', 'max:50'],
        ];
    }

    private function normalizeId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
