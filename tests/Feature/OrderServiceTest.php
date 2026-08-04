<?php

use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

describe('OrderService', function () {
    it('creates a customer, order, address, items, and totals from the nested payload', function () {
        $requestData = [
            'customer' => [
                'name' => 'Ali Hassan',
                'phone_1' => '01000000000',
                'phone_2' => '01111111111',
                'instagram_profile' => '@ali',
            ],
            'address' => [
                'country' => 'Egypt',
                'governorate' => 'Alexandria',
                'city' => 'Sidi Gaber',
                'full_address' => '123 Test Street',
            ],
            'source' => 'new',
            'status' => 'confirmed',
            'paid_amount' => 0,
            'shipping_due_amount' => 50,
            'return_due_amount' => 10,
            'order_contents' => 'Two shirts',
            'items' => [
                [
                    'quantity' => 2,
                    'price' => 150,
                    'notes' => 'first item',
                ],
            ],
        ];

        $service = app(OrderService::class);
        $order = $service->store(new Request($requestData));

        expect($order->customer)->not->toBeNull();
        expect($order->customer->name)->toBe('Ali Hassan');
        expect($order->address->country)->toBe('Egypt');
        expect($order->items)->toHaveCount(1);
        expect($order->total_price)->toBe(300.00);
        expect((float) $order->shipping_due_amount)->toBe(50.0);
        expect((float) $order->return_due_amount)->toBe(10.0);
        expect(Customer::where('phone_1', '01000000000')->count())->toBe(1);
        expect(Order::count())->toBe(1);
    });

    it('creates a customer, order, address, and items from flat web form fields', function () {
        $requestData = [
            'customer_name' => 'كمال عبد الفتاح',
            'phone_1' => '01234567890',
            'phone_2' => '01555555555',
            'governorate' => 'القاهرة',
            'full_address' => 'شارع عباس العقاد - مدينة نصر',
            'instagram_profile' => '@kamal_tag',
            'source' => 'new',
            'paid_amount' => 100,
            'items' => [
                [
                    'quantity' => 1,
                    'price' => 500,
                    'notes' => 'سكراب كحلي مقاس لارج',
                ],
            ],
        ];

        $service = app(OrderService::class);
        $order = $service->store(new Request($requestData));

        expect($order->customer)->not->toBeNull();
        expect($order->customer->name)->toBe('كمال عبد الفتاح');
        expect($order->address->governorate)->toBe('القاهرة');
        expect($order->address->full_address)->toBe('شارع عباس العقاد - مدينة نصر');
        expect($order->items)->toHaveCount(1);
        expect($order->total_price)->toBe(500.00);
        expect($order->payments)->toHaveCount(1);
    });
});
