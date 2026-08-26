<?php

namespace App\Http\Requests\Admin\Setting;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $key = $this->route('key');
        $setting = Setting::where('key', $key)->first();
        $type = $setting?->type ?? 'string';

        $valueRules = ['required'];

        switch ($type) {
            case 'boolean':
                $valueRules[] = 'boolean';
                break;
            case 'integer':
                $valueRules[] = 'integer';
                break;
            case 'number':
                $valueRules[] = 'numeric';
                break;
            case 'json':
                $valueRules[] = 'array';
                break;
            case 'file':
                if ($this->hasFile('value')) {
                    $valueRules = array_merge($valueRules, [
                        'file',
                        'image',
                        'max:5120',
                        'mimes:jpg,jpeg,png,webp',
                    ]);
                } else {
                    $valueRules[] = 'string';
                }
                break;
            case 'string':
            default:
                $valueRules[] = 'string';
                break;
        }

        if ($key === 'quick_order_card_variant') {
            $valueRules[] = Rule::in(['horizontal', 'vertical', 'square']);
        }

        if (in_array($key, ['quick_order_background_color', 'quick_order_card_background_color'], true)) {
            $valueRules[] = 'max:50';
        }

        if ($key === 'payment_default') {
            $valueRules[] = Rule::exists('payment_methods', 'id')
                ->where(fn ($query) => $query->where('is_active', true));
        }

        $rules = [
            'value' => $valueRules,
        ];

        if ($key === 'quick_order_steps') {
            $rules['value'][] = 'min:1';
            $rules['value'][] = 'max:6';
            $rules['value.*.number'] = ['nullable', 'integer', 'min:1'];
            $rules['value.*.icon'] = ['nullable', 'string', 'max:50'];
            $rules['value.*.title'] = ['nullable', 'array'];
            $rules['value.*.title.ar'] = ['nullable', 'string', 'max:255'];
            $rules['value.*.title.en'] = ['nullable', 'string', 'max:255'];
            $rules['value.*.description'] = ['nullable', 'array'];
            $rules['value.*.description.ar'] = ['nullable', 'string', 'max:500'];
            $rules['value.*.description.en'] = ['nullable', 'string', 'max:500'];
        }

        if (in_array($key, [
            'quick_order_badge',
            'quick_order_title',
            'quick_order_subtitle',
            'quick_order_cta',
        ], true)) {
            $rules['value.ar'] = ['nullable', 'string', 'max:500'];
            $rules['value.en'] = ['nullable', 'string', 'max:500'];
        }

        if ($key === 'quick_order_page_ids') {
            $rules['value'][] = 'max:100';
            $rules['value.*'] = ['integer', 'distinct', Rule::exists('pages', 'id')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $key = $this->route('key');
        $setting = Setting::where('key', $key)->first();

        if ($setting?->type === 'boolean' && $this->has('value') && ! is_bool($this->input('value'))) {
            $this->merge([
                'value' => filter_var($this->input('value'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }
}
