<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'category' => $this->resource->category?->name,
            'unit' => $this->resource->unit,
            'current_quantity' => (float) $this->resource->current_quantity,
            'minimum_quantity' => (float) $this->resource->minimum_quantity,
            'last_purchase_price' => (float) $this->resource->last_purchase_price,
            'inventory_value' => (float) $this->resource->total_inventory_cost,
            'status' => $this->resource->current_quantity <= 0 ? 'out_of_stock' : ($this->resource->current_quantity <= $this->resource->minimum_quantity ? 'low_stock' : 'available'),
        ];
    }
}
