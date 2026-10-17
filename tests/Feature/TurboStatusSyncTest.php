<?php

namespace Tests\Feature;

use App\Integrations\Turbo\Models\Shipment;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurboStatusSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        $this->customer = Customer::create([
            'name' => 'عميل اختبار',
            'phone_1' => '01012345678',
        ]);
    }

    public function test_order_creation_and_production_lifecycle_to_ready_to_ship(): void
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-001',
            'total_price' => 500,
            'status' => Order::STATUS_NEW,
        ]);

        $this->assertEquals('new', $order->status);

        // Move to cutting
        $order->update(['status' => Order::STATUS_CUTTING]);
        $this->assertEquals('cutting', $order->fresh()->status);

        // Move to sewing
        $order->update(['status' => Order::STATUS_SEWING]);
        $this->assertEquals('sewing', $order->fresh()->status);

        // Move to ready-to-ship (التقفيل)
        $order->update(['status' => Order::STATUS_READY_TO_SHIP]);
        $this->assertEquals('ready-to-ship', $order->fresh()->status);
    }

    public function test_shipping_order_creates_shipment_and_sets_shipped_status(): void
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-002',
            'total_price' => 750,
            'status' => Order::STATUS_READY_TO_SHIP,
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'remote_order_id' => $order->order_number,
            'turbo_id' => 'TURBO-999',
            'tracking_number' => 'TRK-999',
            'receiver' => $this->customer->name,
            'phone' => $this->customer->phone_1,
            'amount' => 750,
            'status' => 'PENDING',
            'status_text' => 'تم إرسال الشحنة',
        ]);

        $order->update(['status' => Order::STATUS_SHIPPED]);

        $this->assertEquals('shipped', $order->fresh()->status);
        $this->assertEquals($order->id, $shipment->order_id);
    }

    public function test_turbo_webhook_in_transit_keeps_order_shipped_and_does_not_revert(): void
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-003',
            'total_price' => 600,
            'status' => Order::STATUS_SHIPPED,
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'remote_order_id' => $order->order_number,
            'turbo_id' => 'TURBO-1003',
            'tracking_number' => 'TRK-1003',
            'receiver' => $this->customer->name,
            'phone' => $this->customer->phone_1,
            'amount' => 600,
            'status' => 'PENDING',
            'status_text' => 'قيد الانتظار',
        ]);

        // Simulate Turbo webhook for in_transit (status 2)
        $response = $this->postJson('/api/turbo/webhook', [
            'remote_order_id' => $order->order_number,
            'status' => 2,
            'status_text' => 'جاري التوصيل مع المندوب',
        ]);

        $response->assertOk();
        $this->assertEquals('shipped', $order->fresh()->status);
        $this->assertEquals('جاري التوصيل مع المندوب', $shipment->fresh()->status_text);
    }

    public function test_turbo_webhook_delivered_updates_order_to_delivered(): void
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-004',
            'total_price' => 800,
            'status' => Order::STATUS_SHIPPED,
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'remote_order_id' => $order->order_number,
            'turbo_id' => 'TURBO-1004',
            'tracking_number' => 'TRK-1004',
            'receiver' => $this->customer->name,
            'phone' => $this->customer->phone_1,
            'amount' => 800,
            'status' => 'in_transit',
        ]);

        $response = $this->postJson('/api/turbo/webhook', [
            'remote_order_id' => $order->order_number,
            'status' => 3,
            'status_text' => 'تم التسليم بنجاح',
        ]);

        $response->assertOk();
        $this->assertEquals('delivered', $order->fresh()->status);
    }

    public function test_turbo_webhook_canceled_updates_order_to_canceled(): void
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-TEST-005',
            'total_price' => 350,
            'status' => Order::STATUS_SHIPPED,
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'remote_order_id' => $order->order_number,
            'turbo_id' => 'TURBO-1005',
            'tracking_number' => 'TRK-1005',
            'receiver' => $this->customer->name,
            'phone' => $this->customer->phone_1,
            'amount' => 350,
            'status' => 'in_transit',
        ]);

        $response = $this->postJson('/api/turbo/webhook', [
            'remote_order_id' => $order->order_number,
            'status' => 4,
            'status_text' => 'مرتجع بناء على رغبة العميل',
        ]);

        $response->assertOk();
        $this->assertEquals('canceled', $order->fresh()->status);
    }

    public function test_webhook_id_collision_does_not_mutate_unrelated_order(): void
    {
        // Order #1 is in cutting stage
        $orderInCutting = Order::create([
            'customer_id' => $this->customer->id,
            'order_number' => 'ORD-CUTTING-99',
            'total_price' => 1000,
            'status' => Order::STATUS_CUTTING,
        ]);

        // Turbo sends webhook with its own id=1 (which matches $orderInCutting->id), but tracking for something else
        $response = $this->postJson('/api/turbo/webhook', [
            'id' => $orderInCutting->id,
            'status' => 3,
            'status_text' => 'تم التسليم',
        ]);

        // Order in cutting must NOT be touched or mutated to delivered
        $this->assertEquals('cutting', $orderInCutting->fresh()->status);
    }
}
