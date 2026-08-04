<?php

namespace Tests\Feature;

use App\Integrations\Turbo\Models\Shipment;
use App\Models\Customer;
use App\Models\CustomerOrderAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurboOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        Http::fake([
            'platform.turbo.info/*' => Http::response([
                'status' => 'success',
                'success' => true,
                'id' => '1001',
                'code' => 'TURBO-1001',
                'tracking_number' => 'TRK-1001',
                'data' => [
                    ['id' => '1001', 'code' => 'TURBO-1001', 'status' => 'IN_TRANSIT']
                ]
            ], 200),
        ]);
    }

    /**
     * Test GET /turbo (index route) requires auth and returns 200 for authenticated user.
     */
    public function test_turbo_index_requires_authentication(): void
    {
        $response = $this->get(route('turbo.index'));
        $response->assertRedirect('/login');
    }

    public function test_turbo_index_loads_view_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('turbo.index'));

        $response->assertOk();
        $response->assertViewIs('pages.turbo.index');
    }

    /**
     * Test GET /turbo/data (data route).
     */
    public function test_turbo_data_returns_json_structure(): void
    {
        $response = $this->actingAs($this->user)->get(route('turbo.data'));

        $response->assertOk();
        $response->assertJsonStructure([
            'turbo',
            'unshipped_orders',
            'categories',
            'statuses',
        ]);
    }

    /**
     * Test POST /turbo/send-order (send order to Turbo)
     */
    public function test_send_order_to_turbo_creates_shipment(): void
    {
        $customer = Customer::create([
            'name' => 'سامي أحمد',
            'phone_1' => '01011112222',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-7711',
            'total_price' => 850.00,
            'source' => 'new',
            'status' => 'confirmed',
        ]);

        CustomerOrderAddress::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'مدينة نصر',
            'full_address' => 'مدينة نصر',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('turbo.send_order'), [
                'order_id' => $order->id,
                'weight' => 1.5,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $shipment = Shipment::where('remote_order_id', $order->order_number)->first();
        $this->assertNotNull($shipment);
        $this->assertEquals(850.00, (float) $shipment->amount);
        $this->assertEquals('shipped', $order->fresh()->status);
    }

    /**
     * Test POST /turbo/resend-request passing id
     */
    public function test_resend_request(): void
    {
        $shipment = Shipment::create([
            'turbo_id' => 'TURBO-9988',
            'remote_order_id' => 'ORD-9988',
            'receiver' => 'Test Receiver',
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('turbo.resend_request'), [
                'id' => $shipment->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * Test POST /turbo/cancel-request passing id
     */
    public function test_cancel_request(): void
    {
        $shipment = Shipment::create([
            'turbo_id' => 'TURBO-9977',
            'remote_order_id' => 'ORD-9977',
            'receiver' => 'Test Receiver',
            'status' => 'IN_TRANSIT',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('turbo.cancel_request'), [
                'id' => $shipment->id,
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    /**
     * Test POST /turbo/delete-order
     */
    public function test_delete_order(): void
    {
        $shipment = Shipment::create([
            'remote_order_id' => 'ORD-9966',
            'receiver' => 'Test Receiver',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('turbo.delete_order'), [
                'remote_order_id' => 'ORD-9966',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertNull(Shipment::find($shipment->id));
    }

    /**
     * Test Webhook endpoint POST /api/turbo/v1/update-order
     */
    public function test_webhook_update_order_updates_shipment_and_captain_info(): void
    {
        $shipment = Shipment::create([
            'turbo_id' => '95403',
            'remote_order_id' => '36429876',
            'receiver' => 'عميل تجريبي',
            'status' => 'PENDING',
        ]);

        $webhookPayload = [
            'order_number' => '36429876',
            'status' => 3,
            'status_text' => 'المقبوله',
            'internal_status' => 1,
            'order_price' => '535.00',
            'delay_reason' => 'ملاحظات كابتن التوصيل',
            'remote_order_id' => '36429876',
            'captain_name' => 'أحمد الكابتن',
            'captain_number1' => '01000221878',
            'branch_name' => 'فرع الرئيسي',
        ];

        $response = $this->postJson('/api/turbo/v1/update-order', $webhookPayload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $shipment->refresh();
        $this->assertEquals('المقبوله', $shipment->status_text);
        $this->assertEquals(535.00, (float) $shipment->amount);
    }
}
