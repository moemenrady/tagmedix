<?php

namespace App\Modules\Inventory\Requests;

use App\Modules\Inventory\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockOutRequest extends FormRequest
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

            /*
            |--------------------------------------------------------------------------
            | Individual
            |--------------------------------------------------------------------------
            */

            'unit_ids' => [
                'nullable',
                'array',
                'min:1',
            ],

            'unit_ids.*' => [
                'integer',
                'distinct',
                'exists:inventory_units,id',
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

            if (!$this->filled('inventory_item_id')) {
                return;
            }

            $item = InventoryItem::find(
                $this->inventory_item_id
            );

            if (!$item) {
                return;
            }

            if ($item->stock_tracking_type === 'individual') {

                if (empty($this->unit_ids)) {
                    $validator->errors()->add(
                        'unit_ids',
                        'Please select at least one inventory unit.'
                    );
                }

            } else {

                if (!$this->filled('quantity')) {
                    $validator->errors()->add(
                        'quantity',
                        'Quantity is required.'
                    );
                }
            }
        });
    }
}