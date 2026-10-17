<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'barcode' => $this->barcode,
            'category_id' => $this->category_id,
            'category' => $this->category?->name,
            'unit' => $this->unit,
            'description' => $this->description,
            'minimum_quantity' => (float) $this->minimum_quantity,
            'current_quantity' => (float) $this->current_quantity,
            'average_cost' => (float) $this->average_cost,
            'last_purchase_price' => (float) $this->last_purchase_price,
            'total_inventory_cost' => (float) $this->total_inventory_cost,
            'inventory_value' => (float) $this->total_inventory_cost,
            'status' => $this->current_quantity <= 0 ? 'out_of_stock' : ($this->current_quantity <= $this->minimum_quantity ? 'low_stock' : 'available'),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
