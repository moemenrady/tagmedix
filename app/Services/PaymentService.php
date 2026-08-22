<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
   public function createOrderPayment(Order $order, float $amount, ?string $note = null): void
    {
        if ($amount <= 0) {
            return;
        }

        $userId = Auth::id() ?? \App\Models\User::first()?->id;

        Payment::create([
            'order_id'     => $order->id,
            'type'         => 'order_advance', // تمت إضافة النوع
            'amount'       => $amount,
            'payment_date' => now(),
            'created_by'   => $userId,
            'note'         => $note ?? 'مقدم للطلب #' . $order->order_number,
        ]);
    }

    public function createCompanySupply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Payment::create([
            'order_id' => null,
            'type' => 'company_supply',
            'amount' => $validator->validated()['amount'],
            'payment_date' => $validator->validated()['payment_date'],
            'note' => $validator->validated()['note'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    public function financialCenter(?string $from, ?string $to): array
    {
        $from = $from ?: now()->startOfDay()->toDateString();
        $to   = $to ?: now()->endOfDay()->toDateString();

        $payments = Payment::with(['creator', 'order.customer'])
            ->whereBetween('payment_date', [$from, $to])
            ->latest('payment_date')
            ->latest('id')
            ->get();

        $advances = $payments->where('type', 'order_advance');
        $supplies = $payments->where('type', 'company_supply');

        // جلب المتوقع للطلبات غير المشحونة/الملغاة والتي لها مقدم في نفس الفترة
$expectedOrders = Order::query()
    ->whereNotIn('status', [
        'shipped',
        'delivered',
        'canceled',
    ])
    ->with([
        'customer',
        'payments' => function ($query) {
            $query->where('type', 'order_advance');
        }
    ])
    ->get()
    ->map(function ($order) {

        $totalPaid = $order->payments->sum(
            fn ($payment) => (float) $payment->amount
        );

        $remainingAmount = max(
            0,
            (float) $order->total_price - $totalPaid
        );

        $order->total_paid_for_expected = $totalPaid;
        $order->expected_supply_amount = $remainingAmount;

        return $order;
    })
    ->filter(function ($order) {
        return $order->expected_supply_amount > 0;
    })
    ->values();

$expectedTotal = $expectedOrders->sum(
    fn ($order) => $order->expected_supply_amount
);


        return [
            'from' => $from,
            'to' => $to,
            'payments' => $payments,
            'advances' => [
                'total' => $advances->sum('amount'),
                'count' => $advances->count(),
                'orders_count' => $advances->pluck('order_id')->filter()->unique()->count(),
            ],
            'supplies' => [
                'total' => $supplies->sum('amount'),
                'count' => $supplies->count(),
            ],
            'expected' => [
                'total' => $expectedTotal,
                'count' => $expectedOrders->count(),
                'orders' => $expectedOrders,
            ],
        ];
    }
    public function getAll()
    {
        return Payment::with([
            'creator',
            'order' => function ($query) {
                $query->withSum('payments', 'amount');
            }
        ])
            ->latest()
            ->paginate(20);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();

        $data['created_by'] = auth()->id();

        return Payment::create($data)->load([
            'order',
            'creator'
        ]);
    }

    public function show(Payment $payment)
    {
        return $payment->load([
            'order',
            'creator'
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'sometimes|exists:orders,id',
            'amount' => 'sometimes|numeric|min:0',
            'payment_date' => 'sometimes|date',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payment->update(
            $validator->validated()
        );

        return $payment->load([
            'order',
            'creator'
        ]);
    }

    public function delete(Payment $payment): bool
    {
        DB::transaction(function () use ($payment) {

            $order = $payment->order;

            // حذف الدفعة
            $payment->delete();

            // إعادة حساب إجمالي المدفوع من جميع الدفعات المتبقية
            $paidAmount = $order->payments()->sum('amount');

            // تحديث بيانات الأوردر
            $order->update([
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $order->total_price - $paidAmount,
            ]);
        });

        return true;
    }
}
