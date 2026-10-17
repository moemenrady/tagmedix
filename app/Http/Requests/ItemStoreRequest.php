<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:items,code'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:items,barcode'],
            'category_id' => ['required', 'exists:item_categories,id'],
            'unit' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'minimum_quantity' => ['nullable', 'numeric', 'min:0'],
            'current_quantity' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
