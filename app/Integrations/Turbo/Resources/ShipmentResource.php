<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transform a shipment into a safe API response payload.
 */
class ShipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'turbo_id' => $this->turbo_id,
            'remote_order_id' => $this->remote_order_id,
            'tracking_number' => $this->tracking_number,
            'receiver' => $this->receiver,
            'phone' => $this->phone,
            'government' => $this->government,
            'area' => $this->area,
            'address' => $this->address,
            'amount' => $this->amount,
            'weight' => $this->weight,
            'status' => $this->status,
            'status_text' => $this->status_text,
            'delivery_type' => $this->delivery_type,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
