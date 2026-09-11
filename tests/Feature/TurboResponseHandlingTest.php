<?php

namespace Tests\Feature;

use App\Services\Turbo\TurboApiService;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use RuntimeException;
use Tests\TestCase;

class TurboResponseHandlingTest extends TestCase
{
    use RefreshDatabase;
    public function test_normalizes_egyptian_phone_numbers_for_turbo()
    {
        $service = app(TurboApiService::class);

        $this->assertEquals('01223237666', $service->normalizePhoneForTurbo('+201223237666'));
        $this->assertEquals('01223237666', $service->normalizePhoneForTurbo('201223237666'));
        $this->assertEquals('01223237666', $service->normalizePhoneForTurbo('00201223237666'));
        $this->assertEquals('01223237666', $service->normalizePhoneForTurbo('01223237666'));
        $this->assertEquals('01223237666', $service->normalizePhoneForTurbo('1223237666'));
        $this->assertEquals('01223237666', $service->normalizePhoneForTurbo('+20 122 323 7666'));
        $this->assertEquals('01223237666', $service->normalizePhoneForTurbo('+٢٠١٢٢٣٢٣٧٦٦٦'));
    }

    public function test_handle_response_throws_when_turbo_returns_success_zero_with_errors()
    {
        $service = app(TurboApiService::class);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('handleResponse');
        $method->setAccessible(true);

        $fakeResponseBody = json_encode([
            'success' => 0,
            'error_msg' => 'The First Phone format is invalid.',
            'errors' => [
                'phone1' => ['The First Phone format is invalid.'],
                'government' => ['The government field is required.'],
            ],
        ]);

        $fakeResponse = new Response(new Psr7Response(200, ['Content-Type' => 'application/json'], $fakeResponseBody));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('رقم الهاتف الأساسي');

        $method->invoke($service, $fakeResponse);
    }

    public function test_handle_response_succeeds_when_turbo_returns_success_true()
    {
        $service = app(TurboApiService::class);

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('handleResponse');
        $method->setAccessible(true);

        $fakeResponseBody = json_encode([
            'success' => 1,
            'result' => [
                'code' => 12345,
                'bar_code' => 'TURBO-12345',
            ],
        ]);

        $fakeResponse = new Response(new Psr7Response(200, ['Content-Type' => 'application/json'], $fakeResponseBody));

        $result = $method->invoke($service, $fakeResponse);
        $this->assertIsArray($result);
        $this->assertEquals(12345, $result['result']['code']);
    }

    public function test_ship_order_ajax_returns_422_when_turbo_fails()
    {
        $user = \App\Models\User::factory()->create();
        $customer = \App\Models\Customer::create([
            'name' => 'Test Customer',
            'phone_1' => '+201223237666',
        ]);
        $gov = \App\Models\Government::firstOrCreate(['name' => 'القاهرة'], ['code' => 'CAI', 'real_id' => 1]);
        $area = \App\Models\Area::firstOrCreate(['name' => 'المعادي', 'government_id' => $gov->id], ['code' => 'MAA', 'real_id' => 10]);

        $order = \App\Models\Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-TEST-422',
            'customer_name' => 'Test Customer',
            'customer_phone_1' => '+201223237666',
            'total_price' => 500,
            'status' => 'ready-to-ship',
        ]);

        // Mock TurboApiService to simulate a Turbo API failure
        $mockTurbo = \Mockery::mock(TurboApiService::class);
        $mockTurbo->shouldReceive('addOrder')->andThrow(new RuntimeException('فشل إرسال الشحنة لشركة Turbo: صيغة رقم الهاتف غير صالحة'));
        $this->app->instance(TurboApiService::class, $mockTurbo);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->postJson(route('finished-goods.ship', $order->id), [
                'receiver' => 'Test Customer',
                'phone1' => '01223237666',
                'government' => 'القاهرة',
                'area' => 'المعادي',
                'address' => 'شارع 9 المعادي',
                'amount_to_be_collected' => 550,
                'return_amount' => 50,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('فشل إرسال الشحنة لشركة Turbo', $response->json('message'));
        $this->assertEquals('ready-to-ship', $order->fresh()->status);
    }

    public function test_ship_order_ajax_succeeds_and_creates_shipment()
    {
        $user = \App\Models\User::factory()->create();
        $customer = \App\Models\Customer::create([
            'name' => 'Success Customer',
            'phone_1' => '+201223237666',
        ]);
        $gov = \App\Models\Government::firstOrCreate(['name' => 'الجيزة'], ['code' => 'GIZ', 'real_id' => 2]);
        $area = \App\Models\Area::firstOrCreate(['name' => 'الدقي'], ['code' => 'DOK', 'real_id' => 20, 'government_id' => $gov->id]);

        $order = \App\Models\Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-TEST-SUCCESS',
            'customer_name' => 'Success Customer',
            'customer_phone_1' => '+201223237666',
            'total_price' => 700,
            'status' => 'ready-to-ship',
        ]);

        $mockTurbo = \Mockery::mock(TurboApiService::class);
        $mockTurbo->shouldReceive('addOrder')->andReturn([
            'success' => 1,
            'result' => [
                'code' => 'TURBO-9999',
                'bar_code' => 'BC-9999',
                'invoice_number' => 'INV-9999',
            ],
        ]);
        $this->app->instance(TurboApiService::class, $mockTurbo);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->postJson(route('finished-goods.ship', $order->id), [
                'receiver' => 'Success Customer',
                'phone1' => '01223237666',
                'government' => 'الجيزة',
                'area' => 'الدقي',
                'address' => 'شارع التحرير الدقي',
                'amount_to_be_collected' => 750,
                'return_amount' => 50,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'turbo_id' => 'TURBO-9999',
            'order_number' => 'ORD-TEST-SUCCESS',
        ]);
        $this->assertEquals('shipped', $order->fresh()->status);

        // Verify order show page displays shipment details
        $showResponse = $this->actingAs($user)->get(route('orders.show', $order->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('تمت إضافة الطلب للشحنات (شركة Turbo)');
        $showResponse->assertSee('TURBO-9999');

        // Verify orders index page displays shipment badge
        $indexResponse = $this->actingAs($user)->get(route('orders.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('TURBO-9999');
    }
}

