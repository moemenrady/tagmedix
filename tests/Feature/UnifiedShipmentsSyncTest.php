<?php

namespace Tests\Feature;

use App\Integrations\Turbo\Models\Shipment;
use App\Integrations\Turbo\Models\ShipmentStatusHistory;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\Turbo\TurboApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UnifiedShipmentsSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_shipment_model_accessors_extract_rich_data()
    {
        $shipment = Shipment::create([
            'tracking_number' => 'TRK-1001',
            'remote_order_id' => 'ORD-1001',
            'receiver' => 'أحمد محمود',
            'phone' => '01012345678',
            'amount' => 500.0,
            'status' => 'delivered',
            'status_text' => 'تم التسليم بنجاح',
            'response' => [
                'captain_name' => 'محمد إبراهيم',
                'captain_number1' => '01223334444',
                'delay_reason' => 'تم التأجيل بطلب من العميل',
                'return_reason' => null,
                'branch' => 'فرع مدينة نصر',
                'expected_branch' => 'فرع التجمع',
            ],
        ]);

        $this->assertEquals('محمد إبراهيم', $shipment->captain_name);
        $this->assertEquals('01223334444', $shipment->captain_phone);
        $this->assertEquals('https://wa.me/201223334444', $shipment->captain_whatsapp_url);
        $this->assertEquals('تم التأجيل بطلب من العميل', $shipment->delay_reason);
        $this->assertNull($shipment->return_reason);
        $this->assertEquals('فرع مدينة نصر', $shipment->current_branch);
        $this->assertEquals('فرع التجمع', $shipment->expected_branch);
        $this->assertTrue($shipment->is_delayed);
        $this->assertTrue($shipment->is_delivered);
    }

    public function test_apply_shipment_update_updates_shipment_order_and_creates_history()
    {
        $customer = Customer::create([
            'name' => 'عميل اختبار',
            'phone_1' => '01099998888',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-999',
            'customer_id' => $customer->id,
            'total_price' => 750,
            'status' => 'shipped',
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'remote_order_id' => 'ORD-999',
            'tracking_number' => 'TRK-999',
            'amount' => 750,
            'status' => 'in_transit',
            'status_text' => 'مع المندوب',
        ]);

        $service = app(TurboApiService::class);

        $payload = [
            'status' => 'delivered',
            'status_text' => 'تم التسليم بنجاح',
            'received_amount' => 750,
            'captain' => 'كابتن محمود',
            'captain_number1' => '01122233344',
        ];

        $service->applyShipmentUpdate($shipment, $payload, 'test_sync');

        $shipment->refresh();
        $order->refresh();

        // 1. Verify shipment attributes
        $this->assertEquals('delivered', $shipment->status);
        $this->assertEquals(750, (float) $shipment->collected_amount);
        $this->assertNotNull($shipment->last_synced_at);
        $this->assertNotNull($shipment->delivered_at);
        $this->assertEquals('كابتن محمود', $shipment->captain_name);
        $this->assertEquals('01122233344', $shipment->captain_phone);

        // 2. Verify order status synced
        $this->assertEquals('delivered', $order->status);

        // 3. Verify history record created
        $history = ShipmentStatusHistory::where('shipment_id', $shipment->id)->latest()->first();
        $this->assertNotNull($history);
        $this->assertEquals('in_transit', $history->old_status);
        $this->assertEquals('delivered', $history->new_status);
    }

    public function test_shipments_index_page_loads_with_enriched_view()
    {
        Shipment::create([
            'tracking_number' => 'TRK-TEST-1',
            'remote_order_id' => 'ORD-TEST-1',
            'receiver' => 'محمد أحمد',
            'amount' => 300,
            'status' => 'in_transit',
            'response' => [
                'captain_name' => 'كابتن علي',
                'captain_number1' => '01055554444',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('shipments.index'));

        $response->assertStatus(200);
        $response->assertSee('TRK-TEST-1');
        $response->assertSee('محمد أحمد');
        $response->assertSee('كابتن علي');
        $response->assertSee('مزامنة الشحنات من Turbo');
    }

    public function test_turbo_route_redirects_to_shipments_index()
    {
        $response = $this->actingAs($this->admin)->get('/turbo');
        $response->assertRedirect(route('shipments.index'));
    }

    public function test_single_shipment_sync_endpoint()
    {
        $shipment = Shipment::create([
            'tracking_number' => 'TURBO-SYNC-123',
            'remote_order_id' => 'ORD-SYNC-123',
            'receiver' => 'عميل سينك',
            'amount' => 450,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://platform.turbo.info/external-api/get-status' => Http::response([
                'success' => 1,
                'data' => [
                    [
                        'code' => 'TURBO-SYNC-123',
                        'status' => 'delivered',
                        'status_text' => 'تم التسليم',
                        'received_amount' => 450,
                        'captain' => 'محمود كابتن',
                        'captain_number1' => '01011112222',
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('shipments.sync_single', $shipment->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'synced' => true,
        ]);

        $shipment->refresh();
        $this->assertEquals('delivered', $shipment->status);
        $this->assertEquals(450, (float) $shipment->collected_amount);
        $this->assertEquals('محمود كابتن', $shipment->captain_name);
    }

    public function test_bulk_shipments_sync_endpoint()
    {
        $shipment1 = Shipment::create([
            'tracking_number' => 'BULK-1',
            'amount' => 200,
            'status' => 'pending',
        ]);

        $shipment2 = Shipment::create([
            'tracking_number' => 'BULK-2',
            'amount' => 400,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://platform.turbo.info/external-api/get-status' => Http::response([
                'success' => 1,
                'data' => [
                    [
                        'code' => 'BULK-1',
                        'status' => 'delivered',
                        'received_amount' => 200,
                    ],
                    [
                        'code' => 'BULK-2',
                        'status' => 'in_transit',
                        'delay_reason' => 'تأجيل بمعرفة العميل',
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('shipments.sync_turbo'), [
            'from' => '2026-08-01',
            'to' => '2026-08-30',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_fetched' => 2,
                'updated_count' => 2,
            ]
        ]);

        $shipment1->refresh();
        $shipment2->refresh();

        $this->assertEquals('delivered', $shipment1->status);
        $this->assertEquals(200, (float) $shipment1->collected_amount);

        $this->assertEquals('in_transit', $shipment2->status);
        $this->assertEquals('تأجيل بمعرفة العميل', $shipment2->delay_reason);
    }

    public function test_debug_turbo_shipment_endpoint()
    {
        $shipment = Shipment::create([
            'tracking_number' => 'DEBUG-TRK-999',
            'remote_order_id' => 'ORD-DEBUG-999',
            'receiver' => 'مطور النظام',
            'amount' => 500,
            'status' => 'pending',
            'response' => ['test' => 'data'],
        ]);

        Http::fake([
            'https://platform.turbo.info/external-api/search-order' => Http::response([
                'success' => true,
                'result' => [
                    [
                        'code' => 'DEBUG-TRK-999',
                        'status' => 'قيد الإنتظار',
                        'branch' => 'الفرع الرئيسي',
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('shipments.debug_turbo', $shipment->id));

        $response->assertStatus(200);
        $response->assertJson([
            'shipment_id' => $shipment->id,
            'search_key_used' => 'DEBUG-TRK-999',
            'stored_shipment_response' => ['test' => 'data'],
        ]);
    }

    public function test_cancel_shipment_endpoint_marks_shipment_deleted_and_syncs_order()
    {
        $customer = Customer::create([
            'name' => 'عميل إلغاء',
            'phone_1' => '01011112222',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-CANCEL-1',
            'customer_id' => $customer->id,
            'total_price' => 300,
            'status' => 'shipped',
        ]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'remote_order_id' => 'ORD-CANCEL-1',
            'tracking_number' => 'TRK-CANCEL-1',
            'turbo_id' => '38543067',
            'amount' => 300,
            'status' => 'قيد الإنتظار',
            'status_text' => 'قيد الإنتظار',
        ]);

        Http::fake([
            'https://platform.turbo.info/external-api/canceled' => Http::response([
                'success' => true,
                'message' => 'Shipment canceled successfully',
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('shipments.cancel', $shipment->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $shipment->refresh();
        $order->refresh();

        $this->assertEquals('محذوفة', $shipment->status);
        $this->assertEquals('محذوفة', $shipment->status_text);
        $this->assertTrue($shipment->is_canceled);
        $this->assertTrue($shipment->is_returned);
        $this->assertEquals('canceled', $order->status);

        // Ensure audit history was logged
        $history = ShipmentStatusHistory::where('shipment_id', $shipment->id)->latest()->first();
        $this->assertNotNull($history);
        $this->assertEquals('قيد الإنتظار', $history->old_status);
        $this->assertEquals('محذوفة', $history->new_status);
    }
}

