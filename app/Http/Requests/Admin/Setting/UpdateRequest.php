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
        $setting = Setting::where('key', $this->route('key'))->first();
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
                $valueRules[] = $this->hasFile('value') ? 'file' : 'string';
                break;
            case 'string':
            default:
                $valueRules[] = 'string';
                break;
        }

        return [
            'value' => array_merge(
                $valueRules,
                $this->route('key') === 'payment_default'
                    ? [
                        Rule::exists('payment_methods', 'id')->where(fn($query) => $query->where('is_active', true)),
                    ]
                    : []
            ),
        ];
    }
}
