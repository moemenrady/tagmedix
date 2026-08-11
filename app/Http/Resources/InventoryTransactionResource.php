<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_number' => $this->transaction_number,
            'item' => $this->item?->name,
            'transaction_type' => $this->type,
            'quantity' => (float) $this->quantity,
            'balance_before' => (float) $this->balance_before,
            'balance_after' => (float) $this->balance_after,
            'unit_cost' => (float) $this->unit_cost,
            'total_cost' => (float) $this->total_cost,
            'performed_by' => $this->performedBy?->name,
            'approved_by' => $this->approvedBy?->name,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'purpose' => $this->purpose,
            'notes' => $this->notes,
            'transaction_date' => $this->transaction_date,
        ];
    }
}
