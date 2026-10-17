<?php

namespace App\Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'inventory_item_id' => [
                'required',
                'integer',
                'exists:inventory_items,id',
            ],

            'inventory_item_variant_id' => [
                'required',
                'integer',
                'exists:inventory_item_variants,id',
            ],

        

            /*
            |--------------------------------------------------------------------------
            | Bulk
            |--------------------------------------------------------------------------
            */

            'quantity' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'unit_type' => [
                'nullable',
                Rule::in([
                    'meter',
                    'kg',
                    'piece',
                    'quantity',
                ]),
            ],

            'unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Individual
            |--------------------------------------------------------------------------
            */

            'units' => [
                'nullable',
                'array',
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
                'max:100',
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
                'max:1000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $item = $this->inventoryItem();

            if (!$item) {
                return;
            }

            if ($item->stock_tracking_type === 'individual') {

                if (empty($this->units)) {
                    $validator->errors()->add(
                        'units',
                        'Please add at least one inventory unit.'
                    );
                }

            } else {

                if (!$this->filled('quantity')) {
                    $validator->errors()->add(
                        'quantity',
                        'Quantity is required.'
                    );
                }

                if (!$this->filled('unit_type')) {
                    $validator->errors()->add(
                        'unit_type',
                        'Unit type is required.'
                    );
                }

                if (!$this->filled('unit_cost')) {
                    $validator->errors()->add(
                        'unit_cost',
                        'Unit cost is required.'
                    );
                }
            }
        });
    }

    private function inventoryItem()
    {
        if (!$this->filled('inventory_item_id')) {
            return null;
        }

        return \App\Modules\Inventory\Models\InventoryItem::find(
            $this->inventory_item_id
        );
    }
}