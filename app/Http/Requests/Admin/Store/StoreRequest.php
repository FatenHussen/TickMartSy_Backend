<?php

namespace App\Http\Requests\Admin\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
      return [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:stores,email',
            'owner_phone' => 'required|string|unique:stores,owner_phone',
            'password'    => 'required|min:8',

            'store_name'  => 'required|array',
            'area_id'     => 'required|exists:areas,id',
            'category_id' => 'required|exists:categories,id',
        ];
    }
}
