<?php

namespace App\Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Item
            |--------------------------------------------------------------------------
            */

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'category' => [
                'required',
                Rule::in([
                    'fabric',
                    'accessory',
                    'finished_product',
                ]),
            ],

            'color_id' => [
                'required',
                'integer',
                'exists:colors,id',
            ],

            'minimum_stock' => [
                'required',
                'numeric',
                'min:0',
            ],

            'stock_tracking_type' => [
                'required',
                Rule::in([
                    'bulk',
                    'individual',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Bulk
            |--------------------------------------------------------------------------
            */

            'unit_type' => [
                'nullable',
                Rule::in([
                    'meter',
                    'kg',
                    'piece',
                    'quantity',
                ]),
                'required_if:stock_tracking_type,bulk',
            ],

            'quantity' => [
                'nullable',
                'numeric',
                'gt:0',
                'required_if:stock_tracking_type,bulk',
            ],

            'unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:stock_tracking_type,bulk',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Individual Units
            |--------------------------------------------------------------------------
            */

            'units' => [
                'nullable',
                'array',
                'required_if:stock_tracking_type,individual',
                'min:1',
            ],

            'units.*.unit_type' => [
                'required',
                Rule::in([
                    'meter',
                    'kg',
                    'piece',
                    'quantity',
                ]),
            ],

            'units.*.unit_code' => [
                'required',
                'string',
                'max:255',
                'distinct',
            ],

            'units.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'units.*.unit_cost' => [
                'required',
                'numeric',
                'min:0',
            ],

            'units.*.notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم الصنف',
            'category' => 'الفئة',
            'color_id' => 'اللون',
            'minimum_stock' => 'الحد الأدنى للمخزون',
            'stock_tracking_type' => 'طريقة تتبع المخزون',
            'unit_type' => 'وحدة القياس',
            'quantity' => 'الكمية',
            'unit_cost' => 'تكلفة الوحدة',
            'units' => 'الوحدات',
            'units.*.unit_type' => 'وحدة القياس',
            'units.*.unit_code' => 'كود الوحدة',
            'units.*.quantity' => 'كمية الوحدة',
            'units.*.unit_cost' => 'تكلفة الوحدة',
        ];
    }
}
