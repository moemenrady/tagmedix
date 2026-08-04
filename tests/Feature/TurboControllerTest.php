<?php

namespace Tests\Feature;

use App\Integrations\Turbo\Models\Shipment;
use App\Models\User;
use App\Services\Turbo\TurboApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurboControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_turbo_page_view(): void
    {
        $response = $this->actingAs($this->user)->get(route('turbo.index'));

        $response->assertOk();
        $response->assertViewIs('pages.turbo.index');
    }

    public function test_get_data_returns_shipment_list(): void
    {
        Shipment::create([
            'turbo_id' => 'TURBO-101',
            'remote_order_id' => 'ORD-101',
            'receiver' => 'أحمد علي',
            'phone' => '01012345678',
            'government' => 'القاهرة',
            'address' => 'مدينة نصر',
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('turbo.data'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'turbo']);
    }

    public function test_delete_order_deletes_shipment(): void
    {
        $shipment = Shipment::create([
            'remote_order_id' => 'ORD-999',
            'receiver' => 'محمد سعيد',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('turbo.delete_order'), [
            'remote_order_id' => 'ORD-999',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertNull(Shipment::find($shipment->id));
    }
}
