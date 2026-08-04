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
        if (!$userId) {
            $user = \App\Models\User::factory()->create();
            $userId = $user->id;
        }

        Payment::create([
            'order_id'     => $order->id,
            'amount'       => $amount,
            'payment_date' => now(),
            'created_by'   => $userId,
            'note'         => $note ?? 'مقدم للطلب #' . $order->order_number,
        ]);
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
