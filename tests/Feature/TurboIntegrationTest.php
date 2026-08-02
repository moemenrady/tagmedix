<?php

use App\Integrations\Turbo\Enums\DeliveryType;
use App\Integrations\Turbo\Enums\ShipmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('creates a shipment via the add order endpoint', function (): void {
    Bus::fake();

    $response = $this->postJson('/api/turbo/add-order', [
        'remote_order_id' => 'order-1',
        'receiver' => 'Mona',
        'phone' => '01010101010',
        'government' => 'Cairo',
        'area' => 'Nasr City',
        'address' => 'Street 5',
        'amount' => 69.99,
        'weight' => 2.5,
        'notes' => 'fragile',
        'delivery_type' => DeliveryType::EXPRESS->value,
        'is_order' => true,
        'is_fragile' => true,
        'status' => ShipmentStatus::PENDING->value,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.remote_order_id', 'order-1');
});
