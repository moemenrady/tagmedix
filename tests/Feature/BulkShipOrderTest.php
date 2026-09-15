<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Customer;
use App\Models\CustomerOrderAddress;
use App\Models\Government;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Turbo\TurboApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class BulkShipOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Government $cairoGov;
    protected Area $maadiArea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->cairoGov = Government::firstOrCreate(['name' => 'القاهرة'], ['code' => 'CAI', 'real_id' => 1]);
        $this->maadiArea = Area::firstOrCreate(
            ['name' => 'المعادي', 'government_id' => $this->cairoGov->id],
            ['code' => 'MAA', 'real_id' => 10]
        );
    }

    public function test_bulk_ship_validation_requires_order_ids()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('finished-goods.bulk-ship'), [
                'order_ids' => [],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['order_ids']);
    }

    public function test_bulk_ship_processes_multiple_orders_successfully()
    {
        $customer1 = Customer::create([
            'name' => 'Customer One',
            'phone_1' => '01011112222',
        ]);
        $customer2 = Customer::create([
            'name' => 'Customer Two',
            'phone_1' => '01033334444',
        ]);

        $order1 = Order::create([
            'customer_id' => $customer1->id,
            'order_number' => 'ORD-BULK-001',
            'customer_name' => 'Customer One',
            'customer_phone_1' => '01011112222',
            'total_price' => 450,
            'status' => 'ready-to-ship',
        ]);
        CustomerOrderAddress::create([
            'customer_id' => $customer1->id,
            'order_id' => $order1->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'المعادي',
            'full_address' => 'شارع النصر، المعادي',
        ]);

        $order2 = Order::create([
            'customer_id' => $customer2->id,
            'order_number' => 'ORD-BULK-002',
            'customer_name' => 'Customer Two',
            'customer_phone_1' => '01033334444',
            'total_price' => 600,
            'status' => 'ready-to-ship',
        ]);
        CustomerOrderAddress::create([
            'customer_id' => $customer2->id,
            'order_id' => $order2->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'المعادي',
            'full_address' => 'شارع اللاسلكي، المعادي',
        ]);

        $mockTurbo = Mockery::mock(TurboApiService::class);
        $mockTurbo->shouldReceive('addOrder')
            ->twice()
            ->andReturn(
                [
                    'success' => 1,
                    'result' => [
                        'code' => 'TURBO-B1',
                        'bar_code' => 'BC-B1',
                    ],
                ],
                [
                    'success' => 1,
                    'result' => [
                        'code' => 'TURBO-B2',
                        'bar_code' => 'BC-B2',
                    ],
                ]
            );
        $this->app->instance(TurboApiService::class, $mockTurbo);

        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('finished-goods.bulk-ship'), [
                'order_ids' => [$order1->id, $order2->id],
                'weight' => 1.5,
                'can_open' => 1,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2,
            'success_count' => 2,
            'failed_count' => 0,
        ]);

        $results = $response->json('results');
        $this->assertCount(2, $results);
        $this->assertTrue($results[0]['success']);
        $this->assertEquals('TURBO-B1', $results[0]['turbo_id']);
        $this->assertTrue($results[1]['success']);
        $this->assertEquals('TURBO-B2', $results[1]['turbo_id']);

        // Assert database statuses updated
        $this->assertEquals('shipped', $order1->fresh()->status);
        $this->assertEquals('shipped', $order2->fresh()->status);

        // Assert shipments created
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order1->id,
            'turbo_id' => 'TURBO-B1',
        ]);
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order2->id,
            'turbo_id' => 'TURBO-B2',
        ]);
    }

    public function test_bulk_ship_handles_partial_failures_without_crashing_batch()
    {
        $customer1 = Customer::create([
            'name' => 'Valid Customer',
            'phone_1' => '01011112222',
        ]);
        $customer2 = Customer::create([
            'name' => 'Invalid Customer',
            'phone_1' => '01022223333',
        ]);

        $order1 = Order::create([
            'customer_id' => $customer1->id,
            'order_number' => 'ORD-PARTIAL-001',
            'customer_name' => 'Valid Customer',
            'customer_phone_1' => '01011112222',
            'total_price' => 300,
            'status' => 'ready-to-ship',
        ]);
        CustomerOrderAddress::create([
            'customer_id' => $customer1->id,
            'order_id' => $order1->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'المعادي',
            'full_address' => 'شارع النصر',
        ]);

        $order2 = Order::create([
            'customer_id' => $customer2->id,
            'order_number' => 'ORD-PARTIAL-002',
            'customer_name' => 'Invalid Customer',
            'customer_phone_1' => '01022223333',
            'total_price' => 400,
            'status' => 'ready-to-ship',
        ]);
        CustomerOrderAddress::create([
            'customer_id' => $customer2->id,
            'order_id' => $order2->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'المعادي',
            'full_address' => 'شارع غير معروف',
        ]);

        $mockTurbo = Mockery::mock(TurboApiService::class);
        // Order 1 succeeds, Order 2 throws Turbo API error
        $mockTurbo->shouldReceive('addOrder')
            ->once()
            ->andReturn([
                'success' => 1,
                'result' => [
                    'code' => 'TURBO-P1',
                    'bar_code' => 'BC-P1',
                ],
            ]);
        $mockTurbo->shouldReceive('addOrder')
            ->once()
            ->andThrow(new RuntimeException('فشل إرسال الشحنة لشركة Turbo: العنوان غير صحيح'));

        $this->app->instance(TurboApiService::class, $mockTurbo);

        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('finished-goods.bulk-ship'), [
                'order_ids' => [$order1->id, $order2->id],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 2,
            'success_count' => 1,
            'failed_count' => 1,
        ]);

        $results = $response->json('results');
        $this->assertTrue($results[0]['success']);
        $this->assertFalse($results[1]['success']);
        $this->assertStringContainsString('العنوان غير صحيح', $results[1]['message']);

        // Order 1 is shipped, Order 2 remains ready-to-ship
        $this->assertEquals('shipped', $order1->fresh()->status);
        $this->assertEquals('ready-to-ship', $order2->fresh()->status);
    }

    public function test_single_order_ship_modal_still_works_as_expected()
    {
        $customer = Customer::create([
            'name' => 'Single Modal Customer',
            'phone_1' => '01122334455',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-SINGLE-001',
            'customer_name' => 'Single Modal Customer',
            'customer_phone_1' => '01122334455',
            'total_price' => 500,
            'status' => 'ready-to-ship',
        ]);
        CustomerOrderAddress::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'المعادي',
            'full_address' => 'شارع 100 المعادي',
        ]);

        $mockTurbo = Mockery::mock(TurboApiService::class);
        $mockTurbo->shouldReceive('addOrder')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['receiver'] === 'Single Custom Receiver'
                    && $data['phone1'] === '01122334455'
                    && $data['amount_to_be_collected'] == 550;
            }))
            ->andReturn([
                'success' => 1,
                'result' => [
                    'code' => 'TURBO-SINGLE-123',
                    'bar_code' => 'BC-SINGLE-123',
                ],
            ]);
        $this->app->instance(TurboApiService::class, $mockTurbo);

        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->postJson(route('finished-goods.ship', $order->id), [
                'receiver' => 'Single Custom Receiver',
                'phone1' => '01122334455',
                'government' => 'القاهرة',
                'area' => 'المعادي',
                'address' => 'شارع 100 المعادي المعدل',
                'amount_to_be_collected' => 550,
                'return_amount' => 50,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'turbo_id' => 'TURBO-SINGLE-123',
            'order_number' => 'ORD-SINGLE-001',
        ]);

        $this->assertEquals('shipped', $order->fresh()->status);
        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'turbo_id' => 'TURBO-SINGLE-123',
        ]);
    }

    public function test_finished_goods_index_renders_with_multi_select_and_bulk_modal()
    {
        $response = $this->actingAs($this->user)->get(route('finished-goods.index'));

        $response->assertStatus(200);
        $response->assertSee('toggleSelectAll');
        $response->assertSee('toggleOrderSelection');
        $response->assertSee('showBulkModal');
        $response->assertSee('openShipModal');
        $response->assertSee('onOrderLocationChange');
        $response->assertSee('onShipmentPriceChange');
    }

    public function test_ship_order_with_expanded_and_edited_fields_sends_custom_data_to_turbo()
    {
        $customer = Customer::create([
            'name' => 'Original Customer',
            'phone_1' => '01011112222',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-EDIT-001',
            'customer_name' => 'Original Customer',
            'customer_phone_1' => '01011112222',
            'total_price' => 500,
            'status' => 'ready-to-ship',
        ]);
        CustomerOrderAddress::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'المعادي',
            'full_address' => 'العنوان القديم',
        ]);

        $mockTurbo = Mockery::mock(TurboApiService::class);
        $mockTurbo->shouldReceive('addOrder')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['receiver'] === 'Monica Maher v2'
                    && $data['phone1'] === '01211029987'
                    && $data['phone2'] === '01000000000'
                    && $data['api_followup_phone'] === '01555555555'
                    && $data['address'] === '١٦ شارع خالد امين بجوار معهد القاهره التعليمي'
                    && $data['order_summary'] === '1x تصميم modern'
                    && $data['number_of_items'] == 2
                    && $data['weight'] == 2.5
                    && $data['delivery_type'] == 0
                    && $data['can_open'] == 1
                    && $data['is_fragile'] == 1
                    && $data['is_order'] === '0'
                    && $data['return_amount'] == 95
                    && $data['amount_to_be_collected'] == 595
                    && $data['invoice_number'] === 'INV-EDIT-001';
            }))
            ->andReturn([
                'success' => 1,
                'result' => [
                    'code' => 'TURBO-EDIT-001',
                    'bar_code' => 'BC-EDIT-001',
                ],
            ]);
        $this->app->instance(TurboApiService::class, $mockTurbo);

        $response = $this->actingAs($this->user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->postJson(route('finished-goods.ship', $order->id), [
                'receiver' => 'Monica Maher v2',
                'phone1' => '01211029987',
                'phone2' => '01000000000',
                'api_followup_phone' => '01555555555',
                'government' => 'القاهرة',
                'area' => 'المعادي',
                'address' => '١٦ شارع خالد امين بجوار معهد القاهره التعليمي',
                'order_summary' => '1x تصميم modern',
                'number_of_items' => 2,
                'weight' => 2.5,
                'delivery_type' => 0,
                'can_open' => 1,
                'is_fragile' => 1,
                'is_order' => '0',
                'return_amount' => 95,
                'amount_to_be_collected' => 595,
                'invoice_number' => 'INV-EDIT-001',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'turbo_id' => 'TURBO-EDIT-001',
            'order_number' => 'ORD-EDIT-001',
        ]);
        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_ship_order_with_is_shipped_paid_updates_order()
    {
        $customer = Customer::create([
            'name' => 'Paid Shipping Customer',
            'phone_1' => '01011119999',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-PAID-001',
            'total_price' => 500,
            'status' => 'ready-to-ship',
            'source' => 'new',
            'is_shipped_paid' => false,
        ]);

        $mockTurbo = Mockery::mock(TurboApiService::class);
        $mockTurbo->shouldReceive('addOrder')
            ->once()
            ->andReturn([
                'success' => true,
                'result' => [
                    'code' => 'TURBO-PAID-001',
                    'invoice_number' => 'INV-PAID-001',
                ],
            ]);
        $this->app->instance(TurboApiService::class, $mockTurbo);

        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('finished-goods.ship', $order->id), [
                'receiver' => 'Paid Shipping Customer',
                'phone1' => '01011119999',
                'government' => 'القاهرة',
                'area' => 'المعادي',
                'address' => 'شارع المعادي',
                'return_amount' => 60,
                'amount_to_be_collected' => 500,
                'is_shipped_paid' => 1,
            ]);

        $response->assertStatus(200);
        $this->assertTrue((bool) $order->fresh()->is_shipped_paid);
    }
}

