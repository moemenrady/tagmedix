<?php

declare(strict_types=1);

namespace App\Integrations\Turbo\Requests;

use App\Integrations\Turbo\DTO\SearchOrderDTO;
use Illuminate\Foundation\Http\FormRequest;

class SearchOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'remote_order_id' => ['nullable', 'string', 'max:191'],
            'tracking_number' => ['nullable', 'string', 'max:191'],
            'receiver' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function toDto(): SearchOrderDTO
    {
        return new SearchOrderDTO(
            remoteOrderId: $this->filled('remote_order_id') ? (string) $this->input('remote_order_id') : null,
            trackingNumber: $this->filled('tracking_number') ? (string) $this->input('tracking_number') : null,
            receiver: $this->filled('receiver') ? (string) $this->input('receiver') : null,
            phone: $this->filled('phone') ? (string) $this->input('phone') : null,
            status: $this->filled('status') ? (string) $this->input('status') : null,
        );
    }
}
