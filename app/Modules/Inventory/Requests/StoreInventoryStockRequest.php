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
            'existing_item_id' => [
                'nullable',
                'integer',
                'exists:inventory_items,id',
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
            | Finished Product
            |--------------------------------------------------------------------------
            |
            | عند اختيار finished_product يتم استقبال بيانات المنتج
            | من items[0] القادمة من الفورم.
            |
            */

            'items' => [
                'nullable',
                'array',
                'required_if:category,finished_product',
                'min:1',
            ],

            'items.*.pant_type_id' => [
                'required_if:category,finished_product',
                'nullable',
                'integer',
                'exists:pant_types,id',
            ],

            'items.*.type_id' => [
                'required_if:category,finished_product',
                'nullable',
                'integer',
                'exists:types,id',
            ],

            'items.*.style_id' => [
                'nullable',
                'integer',
                'exists:styles,id',
            ],

            'items.*.color_id' => [
                'required_if:category,finished_product',
                'integer',
                'exists:colors,id',
            ],

            'items.*.size_id' => [
                'nullable',
                'integer',
                'exists:sizes,id',
            ],

            'items.*.sleeve_type_id' => [
                'nullable',
                'integer',
                'exists:sleeve_types,id',
            ],

            'items.*.quantity' => [
                'required_if:category,finished_product',
                'numeric',
                'gt:0',
            ],

            'items.*.price' => [
                'required_if:category,finished_product',
                'numeric',
                'min:0',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Bulk
            |--------------------------------------------------------------------------
            */

            'unit_type' => [
                'exclude_if:category,finished_product',
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
                'exclude_if:category,finished_product',
                'nullable',
                'numeric',
                'gt:0',
                'required_if:stock_tracking_type,bulk',
            ],

            'unit_cost' => [
                'exclude_if:category,finished_product',
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

            /*
            |--------------------------------------------------------------------------
            | Item
            |--------------------------------------------------------------------------
            */

            'name' => 'اسم الصنف',
            'category' => 'الفئة',
            'color_id' => 'اللون',
            'minimum_stock' => 'الحد الأدنى للمخزون',
            'stock_tracking_type' => 'طريقة تتبع المخزون',
            'existing_item_id' => 'الصنف الموجود',
            /*
            |--------------------------------------------------------------------------
            | Bulk
            |--------------------------------------------------------------------------
            */

            'unit_type' => 'وحدة القياس',
            'quantity' => 'الكمية',
            'unit_cost' => 'تكلفة الوحدة',
            'notes' => 'الملاحظات',

            /*
            |--------------------------------------------------------------------------
            | Individual Units
            |--------------------------------------------------------------------------
            */

            'units' => 'الوحدات',
            'units.*.unit_type' => 'وحدة القياس',
            'units.*.unit_code' => 'كود الوحدة',
            'units.*.quantity' => 'كمية الوحدة',
            'units.*.unit_cost' => 'تكلفة الوحدة',
            'units.*.notes' => 'ملاحظات الوحدة',

            /*
            |--------------------------------------------------------------------------
            | Finished Product
            |--------------------------------------------------------------------------
            */

            'items' => 'بيانات المنتج الجاهز',

            'items.*.pant_type_id' => 'نوع البنطلون',

            'items.*.type_id' => 'نوع المنتج',

            'items.*.style_id' => 'الستايل',

            'items.*.color_id' => 'لون المنتج الجاهز',

            'items.*.size_id' => 'المقاس',

            'items.*.sleeve_type_id' => 'نوع الكم',

            'items.*.quantity' => 'كمية المنتج الجاهز',

            'items.*.price' => 'تكلفة قطعة المنتج الجاهز',

            'items.*.notes' => 'ملاحظات المنتج الجاهز',
        ];
    }
}
