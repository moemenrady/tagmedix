<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function store(Request $request): Order
    {
        $validated = $request->validate([

            // Order info
            'customer_name' => 'required|string|max:255',
            'phone_1' => 'required|string|max:20',
            'phone_2' => 'nullable|string|max:20',
            'governorate' => 'required|string|max:255',
            'full_address' => 'required|string',
            'instagram_profile' => 'nullable|string|max:255',
            'source' => 'required|in:new,from_stock,replacement',
            'status' => 'nullable|in:new,confirmed',

            'paid_amount' => 'nullable|numeric|min:0',

            // Items
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

        return DB::transaction(function () use ($validated) {

            // إنشاء الأوردر
            $order = Order::create([
                'customer_name'      => $validated['customer_name'],
                'phone_1'            => $validated['phone_1'],
                'phone_2'            => $validated['phone_2'] ?? null,
                'governorate'        => $validated['governorate'],
                'full_address'       => $validated['full_address'],
                'instagram_profile'  => $validated['instagram_profile'] ?? null,
                'source'             => $validated['source'],
                'status'             => $validated['status'] ?? 'new',
                'total_price'        => 0,
            ]);

            $totalPrice = 0;

            foreach ($validated['items'] as $item) {

                $itemTotal = $item['price'] * $item['quantity'];

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

                $totalPrice += $itemTotal;
            }

            $paidAmount = $validated['paid_amount'] ?? 0;

            $order->update([
                'total_price' => $totalPrice,
            ]);

            $paidAmount = $validated['paid_amount'] ?? 0;

            if ($paidAmount > 0) {
                $this->paymentService->createOrderPayment(
                    $order,
                    $paidAmount,
                    'مقدم للطلب #' . $order->order_number
                );
            }

            return $order->load([
                'items',
                'payments',
            ]);
        });
    }
}
