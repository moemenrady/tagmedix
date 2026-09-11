<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerOrderAddress;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_show_view_renders_successfully()
    {
        $user = User::factory()->create();

        $customer = Customer::create([
            'name' => 'Monica Maher v2',
            'phone_1' => '01211029987',
            'phone_2' => '01012345678',
            'instagram_profile' => '@monicamaher_98',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-101',
            'customer_name' => 'Monica Maher v2',
            'customer_phone_1' => '01211029987',
            'total_price' => 1200,
            'status' => 'confirmed',
        ]);

        CustomerOrderAddress::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'country' => 'مصر',
            'governorate' => 'القاهرة',
            'city' => 'المعادي',
            'full_address' => 'شارع 9 المعادي',
        ]);

        $response = $this->actingAs($user)->get(route('customers.show', $customer->id));

        $response->assertStatus(200);
        $response->assertSee('Monica Maher v2');
        $response->assertSee('01211029987');
        $response->assertSee('شارع 9 المعادي');
        $response->assertSee('ORD-101');
        $response->assertSee('1,200.00');
    }
}
