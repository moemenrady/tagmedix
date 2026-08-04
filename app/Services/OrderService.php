<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerOrderAddress;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function store(Request $request): Order
    {
        $validated = $this->validateRequest($request);

        try {
            return DB::transaction(function () use ($validated): Order {
                $customer = $this->createCustomer($validated['customer']);
                $order = $this->createOrder($validated, $customer);
                $this->createOrderAddress($validated['address'], $customer, $order);
                $calculatedTotal = $this->createOrderItems($order, $validated['items']);
                $this->updateOrderTotal($order, $calculatedTotal);
                $this->createPaymentIfNeeded($order, $validated['paid_amount'] ?? 0);

                return $order->load([
                    'customer',
                    'address',
                    'items',
                    'payments',
                ]);
            });
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }

    protected function validateRequest(Request $request): array
    {
        if (!$request->has('customer') && $request->has('customer_name')) {
            $request->merge([
                'customer' => [
                    'name' => $request->input('customer_name'),
                    'phone_1' => $request->input('phone_1'),
                    'phone_2' => $request->input('phone_2'),
                    'instagram_profile' => $request->input('instagram_profile'),
                ]
            ]);
        }

        if (!$request->has('address') && ($request->has('governorate') || $request->has('full_address'))) {
            $request->merge([
                'address' => [
                    'country' => $request->input('country', 'مصر'),
                    'governorate' => $request->input('governorate'),
                    'city' => $request->input('city', $request->input('governorate', 'القاهرة')),
                    'full_address' => $request->input('full_address'),
                ]
            ]);
        }

        return $request->validate([
            'customer' => 'required|array',
            'customer.name' => 'required|string|max:255',
            'customer.phone_1' => 'required|string|max:20',
            'customer.phone_2' => 'nullable|string|max:20',
            'customer.instagram_profile' => 'nullable|string|max:255',

            'address' => 'required|array',
            'address.country' => 'required|string|max:255',
            'address.governorate' => 'required|string|max:255',
            'address.city' => 'required|string|max:255',
            'address.full_address' => 'required|string',

            'source' => 'required|in:new,from_stock,replacement',
            'status' => 'nullable|in:new,confirmed',
            'paid_amount' => 'nullable|numeric|min:0',
            'shipping_due_amount' => 'nullable|numeric|min:0',
            'return_due_amount' => 'nullable|numeric|min:0',
            'order_contents' => 'nullable|string',

            'items' => 'required|array|min:1',
            'items.*.style_id' => 'nullable|exists:styles,id',
            'items.*.color_id' => 'nullable|exists:colors,id',
            'items.*.size_id' => 'nullable|exists:sizes,id',
            'items.*.type_id' => 'nullable|exists:types,id',
            'items.*.pant_type_id' => 'nullable|exists:pant_types,id',
            'items.*.sleeve' => 'nullable|in:long,short',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);
    }

    protected function createCustomer(array $customerData): Customer
    {
        return Customer::updateOrCreate(
            [
                'phone_1' => $customerData['phone_1'],
            ],
            [
                'name' => $customerData['name'],
                'phone_2' => $customerData['phone_2'] ?? null,
                'instagram_profile' => $customerData['instagram_profile'] ?? null,
            ]
        );
    }

    protected function createOrder(array $validatedData, Customer $customer): Order
    {
        return Order::create([
            'customer_id' => $customer->id,
            'source' => $validatedData['source'],
            'status' => $validatedData['status'] ?? 'new',
            'total_price' => 0,
            'shipping_due_amount' => $validatedData['shipping_due_amount'] ?? 0,
            'return_due_amount' => $validatedData['return_due_amount'] ?? 0,
            'order_contents' => $validatedData['order_contents'] ?? null,
        ]);
    }

    protected function createOrderAddress(array $addressData, Customer $customer, Order $order): CustomerOrderAddress
    {
        return CustomerOrderAddress::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'country' => $addressData['country'],
            'governorate' => $addressData['governorate'],
            'city' => $addressData['city'],
            'full_address' => $addressData['full_address'],
        ]);
    }

    protected function createOrderItems(Order $order, array $items): float
    {
        $calculatedTotal = 0.0;

        foreach ($items as $item) {
            $itemTotal = (float) $item['price'] * (int) $item['quantity'];

            $order->items()->create([
                'style_id' => $item['style_id'] ?? null,
                'color_id' => $item['color_id'] ?? null,
                'size_id' => $item['size_id'] ?? null,
                'type_id' => $item['type_id'] ?? null,
                'pant_type_id' => $item['pant_type_id'] ?? null,
                'sleeve' => $item['sleeve'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $itemTotal,
                'notes' => $item['notes'] ?? null,
            ]);

            $calculatedTotal += $itemTotal;
        }

        return round($calculatedTotal, 2);
    }

    protected function updateOrderTotal(Order $order, float $calculatedTotal): void
    {
        $order->update([
            'total_price' => $calculatedTotal,
        ]);
    }

    protected function createPaymentIfNeeded(Order $order, float $paidAmount): void
    {
        if ($paidAmount <= 0) {
            return;
        }

        $this->paymentService->createOrderPayment(
            $order,
            $paidAmount,
            'مقدم للطلب #' . $order->order_number
        );
    }
}
