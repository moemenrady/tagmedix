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

            // 3. تحديد حالة الاستعجال وتاريخ التسليم تلقائياً
            $isUrgent = !empty($validated['is_urgent']);
            $defaultDays = (int) \App\Models\Setting::get('default_delivery_days', 7);
            $urgentDays = (int) \App\Models\Setting::get('urgent_delivery_days', 3);
            $daysToAdd = $isUrgent ? $urgentDays : $defaultDays;
            $deliveryDate = !empty($validated['delivery_date']) ? $validated['delivery_date'] : now()->addDays($daysToAdd)->toDateString();

            // 4. إنشاء الطلب وتخزين الـ Snapshot الخاص بالعميل
            $order = Order::create([
                'customer_id'         => $customer->id,
                'order_number'        => $orderNumber,
                'customer_name'       => $validated['customer_name'],
                'customer_phone_1'    => $validated['phone_1'],
                'customer_phone_2'    => $validated['phone_2'] ?? null,
                'customer_instagram'  => $validated['instagram_profile'] ?? null,
                'source'              => $validated['source'],
                'status'              => $validated['status'] ?? 'new',
                'is_urgent'           => $isUrgent,
                'delivery_date'       => $deliveryDate,
                'total_price'         => 0,
                'is_shipped_paid'     => !empty($validated['is_shipped_paid']),
                'shipping_due_amount' => $validated['shipping_due_amount'] ?? 0,
                'return_due_amount'   => $validated['return_due_amount'] ?? 0,
                'order_contents'      => $validated['order_contents'] ?? null,
            ]);

            // 5. إنشاء snapshot مستقل لعنوان هذا الطلب تحديداً
            $this->createOrderAddressSnapshot($validated, $customer, $order);

            // 6. إنشاء عناصر الطلب وحساب الإجمالي
            $calculatedTotal = $this->createOrderItems($order, $validated['items']);

            $orderNumberFinal = 'ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->update([
                'total_price'  => $calculatedTotal,
                'order_number' => $orderNumberFinal,
            ]);

            // 7. إنشاء مهمة الإنتاج الأولى لتفعيل مدة التسليم في مسار العمل تلقائياً
            $userId = auth()->id() ?: (\App\Models\User::value('id') ?? 1);
            \App\Models\ProductionTask::create([
                'order_id'      => $order->id,
                'assigned_to'   => $userId,
                'assigned_by'   => $userId,
                'title'         => "Production Order {$orderNumberFinal}",
                'instruction'   => $order->order_contents ?: ($isUrgent ? 'طلب مستعجل - تسليم فوري' : 'تنفيذ الطلب'),
                'required_date' => $deliveryDate,
                'priority'      => $isUrgent ? 'urgent' : 'medium',
                'status'        => 'pending',
            ]);

            // 8. إضافة الدفعات إن وجدت
            $paidAmount = $validated['paid_amount'] ?? 0;
            $this->createPaymentIfNeeded($order, $paidAmount);

            return $order->load(['customer', 'address', 'items', 'payments', 'productionTasks']);
        });
    }


    public function cleanPhoneNumber(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);
        if ($phone === '') {
            return null;
        }

        // Convert Arabic-Indic numerals (٠-٩ and ۰-۹) to standard Latin digits (0-9)
        $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $latinDigits  = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $phone = str_replace($arabicDigits, $latinDigits, $phone);

        // Retain leading '+' if present
        $hasPlus = str_starts_with($phone, '+');

        // Extract digits only
        $digits = preg_replace('/[^\d]/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        return $hasPlus ? ('+' . $digits) : $digits;
    }

    protected function validateRequest(Request $request): array
    {
        // Clean phone numbers
        if ($request->has('phone_1')) {
            $request->merge(['phone_1' => $this->cleanPhoneNumber($request->input('phone_1'))]);
        }
        if ($request->has('phone_2')) {
            $request->merge(['phone_2' => $this->cleanPhoneNumber($request->input('phone_2'))]);
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
            'phone_1'               => ['required', 'string', 'regex:/^\+?[0-9]{7,20}$/'],
            'phone_2'               => ['nullable', 'string', 'regex:/^\+?[0-9]{7,20}$/'],
            'instagram_profile'     => 'nullable|string|max:255',

            'address.governorate'   => 'required|string|max:255',
            'address.area'          => 'required|string|max:255',
            'address.full_address'  => 'required|string|max:500',

            'source'                => 'required|in:new,from_stock,replacement',
            'status'                => 'nullable|in:new,confirmed',
            'is_urgent'             => 'nullable|boolean',
            'delivery_date'         => 'nullable|date',
            'paid_amount'           => 'nullable|numeric|min:0',
            'is_shipped_paid'       => 'nullable|boolean',
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
            'phone_1.regex' => 'رقم الهاتف الأساسي غير صحيح. يجب أن يتكون من 7 إلى 20 رقماً مع إمكانية البدء بـ (+).',
            'phone_2.regex' => 'رقم الهاتف الإضافي غير صحيح. يجب أن يتكون من 7 إلى 20 رقماً مع إمكانية البدء بـ (+).',
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
                'is_shipped_paid' => (bool) ($order->is_shipped_paid ?? false),
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
