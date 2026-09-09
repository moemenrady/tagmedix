<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class OrderCreationConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_orders_keep_their_customer_name_when_new_order_created_for_same_phone()
    {
        $orderService = app(OrderService::class);
        $phone = '01012345678';
        
        $request1 = new Request([
            'customer_name' => 'Ahmed',
            'phone_1' => $phone,
            'address' => [
                'governorate' => 'القاهرة',
                'area' => 'المعادي',
                'full_address' => 'شارع 9',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 1, 'price' => 100]
            ],
        ]);

        $order1 = $orderService->store($request1);

        $this->assertEquals('Ahmed', $order1->customer_name);
        $this->assertEquals('Ahmed', Customer::where('phone_1', $phone)->first()->name);

        $request2 = new Request([
            'customer_name' => 'Mohamed',
            'phone_1' => $phone,
            'address' => [
                'governorate' => 'الجيزة',
                'area' => 'الدقي',
                'full_address' => 'شارع مصدق',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 1, 'price' => 150]
            ],
        ]);

        $order2 = $orderService->store($request2);

        // Snapshot stays Ahmed for order 1
        $this->assertEquals('Ahmed', Order::find($order1->id)->customer_name);
        // Order 2 snapshot is Mohamed
        $this->assertEquals('Mohamed', Order::find($order2->id)->customer_name);
        
        // Ensure customer name in customers table wasn't overwritten
        $this->assertEquals('Ahmed', Customer::where('phone_1', $phone)->first()->name);
    }

    public function test_generating_order_number_after_deletion()
    {
        $orderService = app(OrderService::class);
        $phone = '01111111111';

        $request1 = new Request([
            'customer_name' => 'Test User',
            'phone_1' => $phone,
            'address' => [
                'governorate' => 'القاهرة',
                'area' => 'مدينة نصر',
                'full_address' => 'مكرم عبيد',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 1, 'price' => 200]
            ],
        ]);

        $order1 = $orderService->store($request1);
        
        // Force delete the order
        $order1->forceDelete();

        $request2 = new Request([
            'customer_name' => 'Test User 2',
            'phone_1' => '01222222222',
            'address' => [
                'governorate' => 'الإسكندرية',
                'area' => 'سموحة',
                'full_address' => 'شارع فوزي معاذ',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 1, 'price' => 300]
            ],
        ]);

        $order2 = $orderService->store($request2);
        
        $this->assertStringStartsWith('ORD-', $order2->order_number);
        $this->assertEquals('ORD-' . str_pad($order2->id, 5, '0', STR_PAD_LEFT), $order2->order_number);
    }

    public function test_concurrency_order_number_sequence()
    {
        $orderService = app(OrderService::class);
        
        $orders = [];
        for ($i = 0; $i < 5; $i++) {
            $request = new Request([
                'customer_name' => 'Concurrent User ' . $i,
                'phone_1' => '0100000000' . $i,
                'address' => [
                    'governorate' => 'Cairo',
                    'area' => 'Maadi',
                    'full_address' => 'Street ' . $i,
                ],
                'source' => 'new',
                'items' => [
                    ['quantity' => 1, 'price' => 100]
                ],
            ]);

            $orders[] = $orderService->store($request);
        }

        foreach ($orders as $order) {
            $this->assertEquals('ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT), $order->order_number);
        }
    }
}
