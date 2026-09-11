<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Services\CustomerService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class InternationalPhoneSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_with_egyptian_international_format_plus_20()
    {
        $orderService = app(OrderService::class);

        $request = new Request([
            'customer_name' => 'Tarek Mansour',
            'phone_1' => '+201223237666',
            'phone_2' => '+201012345678',
            'address' => [
                'governorate' => 'القاهرة',
                'area' => 'المعادي',
                'full_address' => 'شارع النصر 15',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 2, 'price' => 250]
            ],
        ]);

        $order = $orderService->store($request);

        $this->assertEquals('+201223237666', $order->customer_phone_1);
        $this->assertEquals('+201012345678', $order->customer_phone_2);
        $this->assertEquals('Tarek Mansour', $order->customer_name);
    }

    public function test_can_create_order_with_foreign_international_number()
    {
        $orderService = app(OrderService::class);

        // Saudi Arabia number (+966501234567)
        $request = new Request([
            'customer_name' => 'Saud Al-Otaibi',
            'phone_1' => '+966501234567',
            'address' => [
                'governorate' => 'شحن دولي',
                'area' => 'الرياض',
                'full_address' => 'طريق الملك فهد',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 1, 'price' => 500]
            ],
        ]);

        $order = $orderService->store($request);

        $this->assertEquals('+966501234567', $order->customer_phone_1);
        $this->assertEquals('Saud Al-Otaibi', $order->customer_name);
    }

    public function test_cleans_spaces_and_arabic_numerals_properly()
    {
        $orderService = app(OrderService::class);

        $this->assertEquals('+201223237666', $orderService->cleanPhoneNumber(' +20 122 323 7666 '));
        $this->assertEquals('+201223237666', $orderService->cleanPhoneNumber('+٢٠١٢٢٣٢٣٧٦٦٦'));
        $this->assertEquals('01223237666', $orderService->cleanPhoneNumber('012-2323-7666'));
        $this->assertEquals('201223237666', $orderService->cleanPhoneNumber('201223237666'));
    }

    public function test_upsert_customer_matches_between_local_and_international_formats()
    {
        $orderService = app(OrderService::class);

        // First order with standard local Egyptian number
        $request1 = new Request([
            'customer_name' => 'Mahmoud',
            'phone_1' => '01223237666',
            'address' => [
                'governorate' => 'القاهرة',
                'area' => 'مدينة نصر',
                'full_address' => 'عباس العقاد',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 1, 'price' => 100]
            ],
        ]);
        $order1 = $orderService->store($request1);

        // Second order with international format +201223237666 for same customer
        $request2 = new Request([
            'customer_name' => 'Mahmoud Updated',
            'phone_1' => '+201223237666',
            'address' => [
                'governorate' => 'القاهرة',
                'area' => 'مدينة نصر',
                'full_address' => 'عباس العقاد 20',
            ],
            'source' => 'new',
            'items' => [
                ['quantity' => 1, 'price' => 200]
            ],
        ]);
        $order2 = $orderService->store($request2);

        // Both orders should link to the SAME customer in the database
        $this->assertEquals($order1->customer_id, $order2->customer_id);
    }

    public function test_search_customer_by_phone_finds_cross_format()
    {
        $customerService = app(CustomerService::class);

        Customer::create([
            'name' => 'Kareem',
            'phone_1' => '01223237666',
        ]);

        // Search with +201223237666 should find Kareem
        $results1 = $customerService->searchByPhone('+201223237666');
        $this->assertNotEmpty($results1);
        $this->assertEquals('Kareem', $results1->first()->name);

        // Search with 201223237666 should find Kareem
        $results2 = $customerService->searchByPhone('201223237666');
        $this->assertNotEmpty($results2);
        $this->assertEquals('Kareem', $results2->first()->name);
    }
}
