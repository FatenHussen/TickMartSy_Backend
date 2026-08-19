<?php

namespace App\Http\Requests\Admin\NavMenuItem;

use App\Models\NavMenuItem;
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
        return [
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.ar' => 'required|string|max:255',

            'type' => ['required', Rule::in(NavMenuItem::TYPES)],

            'page_id' => 'required_if:type,page|nullable|exists:pages,id',
            'category_id' => 'required_if:type,category|nullable|exists:categories,id',
            'brand_id' => 'required_if:type,brand|nullable|exists:brands,id',
            'url' => 'required_if:type,url|nullable|string|max:2048',
            'route_key' => ['required_if:type,route', 'nullable', Rule::in(NavMenuItem::ROUTE_KEYS)],

            'icon' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'open_in_new_tab' => 'sometimes|boolean',
        ];
    }
}
