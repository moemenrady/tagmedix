<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Requests;

use App\Integrations\Turbo\DTO\UpdateOrderDTO;
use App\Integrations\Turbo\Enums\DeliveryType;
use App\Integrations\Turbo\Enums\ShipmentStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remote_order_id' => ['required', 'string', 'max:191'],
            'receiver' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:20'],
            'government' => ['nullable', 'string', 'max:191'],
            'area' => ['nullable', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:500'],
            'amount' => ['nullable', 'numeric'],
            'weight' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'delivery_type' => ['nullable', 'string', 'in:' . implode(',', array_map(static fn (DeliveryType $type): string => $type->value, DeliveryType::cases()))],
            'is_order' => ['nullable', 'boolean'],
            'is_fragile' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:' . implode(',', array_map(static fn (ShipmentStatus $type): string => $type->value, ShipmentStatus::cases()))],
        ];
    }

    public function toDto(): UpdateOrderDTO
    {
        return new UpdateOrderDTO(
            remoteOrderId: (string) $this->string('remote_order_id'),
            receiver: $this->filled('receiver') ? (string) $this->input('receiver') : null,
            phone: $this->filled('phone') ? (string) $this->input('phone') : null,
            government: $this->filled('government') ? (string) $this->input('government') : null,
            area: $this->filled('area') ? (string) $this->input('area') : null,
            address: $this->filled('address') ? (string) $this->input('address') : null,
            amount: $this->filled('amount') ? (float) $this->input('amount') : null,
            weight: $this->filled('weight') ? (float) $this->input('weight') : null,
            notes: $this->filled('notes') ? (string) $this->input('notes') : null,
            deliveryType: $this->filled('delivery_type') ? DeliveryType::from((string) $this->input('delivery_type')) : null,
            isOrder: $this->has('is_order') ? (bool) $this->boolean('is_order') : null,
            isFragile: $this->has('is_fragile') ? (bool) $this->boolean('is_fragile') : null,
            status: $this->filled('status') ? ShipmentStatus::from((string) $this->input('status')) : null,
        );
    }
}
