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
        protected PaymentService $paymentService,
        protected CustomerService $customerService
    ) {}

    public function store(Request $request): Order
    {
        $validated = $this->validateRequest($request);

        return DB::transaction(function () use ($validated): Order {
            // 1. إنشاء/تحديث العميل في سجل العملاء العام
            $customer = $this->customerService->upsertCustomer($validated);

            // 2. وضع رقم طلب مؤقت
            $orderNumber = 'TEMP-' . uniqid();

            // 3. إنشاء الطلب وتخزين الـ Snapshot الخاص بالعميل
            $order = Order::create([
                'customer_id'         => $customer->id,
                'order_number'        => $orderNumber,
                'customer_name'       => $validated['customer_name'],
                'customer_phone_1'    => $validated['phone_1'],
                'customer_phone_2'    => $validated['phone_2'] ?? null,
                'customer_instagram'  => $validated['instagram_profile'] ?? null,
                'source'              => $validated['source'],
                'status'              => $validated['status'] ?? 'new',
                'total_price'         => 0,
                'shipping_due_amount' => $validated['shipping_due_amount'] ?? 0,
                'return_due_amount'   => $validated['return_due_amount'] ?? 0,
                'order_contents'      => $validated['order_contents'] ?? null,
            ]);

            // 4. إنشاء snapshot مستقل لعنوان هذا الطلب تحديداً
            $this->createOrderAddressSnapshot($validated, $customer, $order);

            // 5. إنشاء عناصر الطلب وحساب الإجمالي
            $calculatedTotal = $this->createOrderItems($order, $validated['items']);

            $order->update([
                'total_price'  => $calculatedTotal,
                'order_number' => 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
            ]);

            // 6. إضافة الدفعات إن وجدت
            $paidAmount = $validated['paid_amount'] ?? 0;
            $this->createPaymentIfNeeded($order, $paidAmount);

            return $order->load(['customer', 'address', 'items', 'payments']);
        });
    }


    protected function validateRequest(Request $request): array
    {
        // Clean phone numbers (remove spaces)
        if ($request->has('phone_1')) {
            $request->merge(['phone_1' => str_replace(' ', '', $request->input('phone_1'))]);
        }
        if ($request->has('phone_2')) {
            $request->merge(['phone_2' => str_replace(' ', '', $request->input('phone_2'))]);
        }

        // Clean full_address (remove newlines and quotes)
        $address = $request->input('address');
        if (isset($address['full_address'])) {
            $cleanAddress = str_replace(["\r\n", "\r", "\n", '"'], ' - ', $address['full_address']);
            $cleanAddress = preg_replace('/\s+/', ' ', $cleanAddress);
            $cleanAddress = preg_replace('/(\s*-\s*)+/', ' - ', $cleanAddress);
            $address['full_address'] = trim($cleanAddress, ' -');
            $request->merge(['address' => $address]);
        }

        return $request->validate([
            'customer_name'         => 'required|string|max:255',
            'phone_1'               => ['required', 'string', 'regex:/^01[0125][0-9]{8}$/'],
            'phone_2'               => ['nullable', 'string', 'regex:/^01[0125][0-9]{8}$/'],
            'instagram_profile'     => 'nullable|string|max:255',

            'address.governorate'   => 'required|string|max:255',
            'address.area'          => 'required|string|max:255',
            'address.full_address'  => 'required|string|max:500',

            'source'                => 'required|in:new,from_stock,replacement',
            'status'                => 'nullable|in:new,confirmed',
            'paid_amount'           => 'nullable|numeric|min:0',
            'shipping_due_amount'   => 'nullable|numeric|min:0',
            'return_due_amount'     => 'nullable|numeric|min:0',
            'order_contents'        => 'nullable|string',

            'items'                 => 'required|array|min:1',
            'items.*.style_id'      => 'nullable|exists:styles,id',
            'items.*.color_id'      => 'nullable|exists:colors,id',
            'items.*.size_id'       => 'nullable|exists:sizes,id',
            'items.*.type_id'       => 'nullable|exists:types,id',
            'items.*.pant_type_id'  => 'nullable|exists:pant_types,id',
            'items.*.sleeve_type_id' => 'nullable|exists:sleeve_types,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.price'         => 'required|numeric|min:0',
            'items.*.notes'         => 'nullable|string',
        ], [
            'phone_1.regex' => 'رقم الهاتف الأساسي يجب أن يكون رقم مصري صحيح (11 رقم يبدأ بـ 01).',
            'phone_2.regex' => 'رقم الهاتف الإضافي يجب أن يكون رقم مصري صحيح (11 رقم يبدأ بـ 01).',
        ]);
    }

    protected function createOrderAddressSnapshot(array $data, Customer $customer, Order $order): CustomerOrderAddress
    {
        $address = $data['address'];
        $isInternational = ($address['governorate'] ?? '') === 'شحن دولي';

        return CustomerOrderAddress::create([
            'customer_id'  => $customer->id,
            'order_id'     => $order->id,
            'country'      => $isInternational ? 'دولى' : 'مصر',
            'governorate'  => $address['governorate'],
            'city'         => $isInternational ? 'شحن دولي' : $address['area'],
            'full_address' => $address['full_address'],
        ]);
    }

    protected function createOrderItems(Order $order, array $items): float
    {
        $calculatedTotal = 0.0;

        foreach ($items as $item) {
            $itemTotal = (float) $item['price'] * (int) $item['quantity'];

            $order->items()->create([
                'style_id'       => $item['style_id'] ?? null,
                'color_id'       => $item['color_id'] ?? null,
                'size_id'        => $item['size_id'] ?? null,
                'type_id'        => $item['type_id'] ?? null,
                'pant_type_id'   => $item['pant_type_id'] ?? null,
                'sleeve_type_id' => $item['sleeve_type_id'] ?? null,
                'quantity'       => $item['quantity'],
                'price'          => $item['price'],
                'total'          => $itemTotal,
                'notes'          => $item['notes'] ?? null,
            ]);

            $calculatedTotal += $itemTotal;
        }

        return round($calculatedTotal, 2);
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
