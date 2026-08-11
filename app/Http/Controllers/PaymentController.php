<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        return view('pages.payments.index', [
            'payments' => $this->paymentService->getAll()
        ]);
    }

    public function create()
    {
        $orders = Order::latest()->get();

        return view('pages.payments.create', compact('orders'));
    }
    public function store(Request $request)
    {
        $payment = $this->paymentService->create($request);

        return response()->json([
            'message' => 'Payment created successfully.',
            'data' => $payment
        ], 201);
    }

    public function show(Payment $payment)
    {
        return response()->json(
            $this->paymentService->show($payment)
        );
    }

    public function update(Request $request, Payment $payment)
    {
        $payment = $this->paymentService->update($request, $payment);

        return response()->json([
            'message' => 'Payment updated successfully.',
            'data' => $payment
        ]);
    }

    public function destroy(Payment $payment)
    {
        $this->paymentService->delete($payment);

        return redirect()->back()->with('success', 'تم حذف الدفعة بنجاح');
    }
   
}
