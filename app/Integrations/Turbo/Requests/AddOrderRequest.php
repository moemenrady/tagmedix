<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Requests;

use App\Integrations\Turbo\DTO\AddOrderDTO;
use App\Integrations\Turbo\Enums\DeliveryType;
use App\Integrations\Turbo\Enums\ShipmentStatus;
use Illuminate\Foundation\Http\FormRequest;

class AddOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remote_order_id' => ['required', 'string', 'max:191'],
            'receiver' => ['required', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:20'],
            'government' => ['required', 'string', 'max:191'],
            'area' => ['required', 'string', 'max:191'],
            'address' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric'],
            'weight' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'delivery_type' => ['required', 'string', 'in:' . implode(',', array_map(static fn (DeliveryType $type): string => $type->value, DeliveryType::cases()))],
            'is_order' => ['required', 'boolean'],
            'is_fragile' => ['required', 'boolean'],
            'status' => ['required', 'string', 'in:' . implode(',', array_map(static fn (ShipmentStatus $type): string => $type->value, ShipmentStatus::cases()))],
        ];
    }

    public function toDto(): AddOrderDTO
    {
        return new AddOrderDTO(
            remoteOrderId: (string) $this->string('remote_order_id'),
            receiver: (string) $this->string('receiver'),
            phone: (string) $this->string('phone'),
            government: (string) $this->string('government'),
            area: (string) $this->string('area'),
            address: (string) $this->string('address'),
            amount: (float) $this->input('amount'),
            weight: (float) $this->input('weight'),
            notes: $this->filled('notes') ? (string) $this->input('notes') : null,
            deliveryType: DeliveryType::from((string) $this->input('delivery_type')),
            isOrder: (bool) $this->boolean('is_order'),
            isFragile: (bool) $this->boolean('is_fragile'),
            status: ShipmentStatus::from((string) $this->input('status')),
        );
    }
}
