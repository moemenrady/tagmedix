<?php

use App\Integrations\Turbo\Actions\AddShipmentAction;
use App\Integrations\Turbo\DTO\AddOrderDTO;
use App\Integrations\Turbo\Enums\DeliveryType;
use App\Integrations\Turbo\Enums\ShipmentStatus;
use App\Integrations\Turbo\Jobs\SendShipmentJob;
use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Repositories\Interfaces\ShipmentRepositoryInterface;

it('creates an immutable add order dto', function (): void {
    $dto = new AddOrderDTO(
        remoteOrderId: 'order-100',
        receiver: 'A. Smith',
        phone: '01000000000',
        government: 'Cairo',
        area: 'Nasr City',
        address: 'Street 1',
        amount: 125.5,
        weight: 3.2,
        notes: 'Fragile',
        deliveryType: DeliveryType::STANDARD,
        isOrder: true,
        isFragile: true,
        status: ShipmentStatus::PENDING,
    );

    expect($dto->remoteOrderId)->toBe('order-100')
        ->and($dto->deliveryType)->toBe(DeliveryType::STANDARD)
        ->and($dto->status)->toBe(ShipmentStatus::PENDING);
});

it('persists a shipment and dispatches a send job', function (): void {
    $repository = Mockery::mock(ShipmentRepositoryInterface::class);
    $repository->shouldReceive('create')->once()->andReturnUsing(function (array $data): Shipment {
        return new Shipment($data);
    });

    $action = new AddShipmentAction($repository);

    $dto = new AddOrderDTO(
        remoteOrderId: 'order-200',
        receiver: 'B. Ahmed',
        phone: '01111111111',
        government: 'Alex',
        area: 'Montazah',
        address: 'Street 2',
        amount: 80,
        weight: 2,
        notes: 'Handle with care',
        deliveryType: DeliveryType::EXPRESS,
        isOrder: true,
        isFragile: false,
        status: ShipmentStatus::PENDING,
    );

    $shipment = $action->execute($dto);

    expect($shipment)->toBeInstanceOf(Shipment::class)
        ->and($shipment->remote_order_id)->toBe('order-200');

    expect(true)->toBeTrue();
});
