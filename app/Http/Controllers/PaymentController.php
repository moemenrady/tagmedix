<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Exports\PaymentsExport;
class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

// تحديث دالة الـ index لاستقبال التواريخ واستخدام المركز المالي
    public function index(Request $request)
    {
        $from = $request->input('from', now()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $financialData = $this->paymentService->financialCenter($from, $to);

        return view('pages.payments.index', compact('financialData'));
    }

    // --- دوال توريد شركة الشحن ---
    public function createCompanySupply()
    {
        return view('pages.payments.company-supply');
    }

    public function storeCompanySupply(Request $request)
    {
        $payment = $this->paymentService->createCompanySupply($request);

        return redirect()->route('payments.index')
            ->with('success', 'تم إضافة توريد شركة الشحن بنجاح');
    }

    // --- دالة التصدير للإكسيل ---
    public function export(Request $request)
    {
        $from = $request->input('from', now()->toDateString());
        $to = $request->input('to', now()->toDateString());

        // تأكد من إنشاء كلاس PaymentsExport باستخدام حزمة Excel
        return \Maatwebsite\Excel\Facades\Excel::download(
            new PaymentsExport($from, $to),
            'financial-center-' . $from . '-to-' . $to . '.xlsx'
        );
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
