<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TurboOperationsController extends Controller
{
    public function index()
    {
        return view('pages.turbo.index');
    }

    public function data()
    {
        // هنا يتم استدعاء Turbo API الجانبي أو إرجاع بيانات المهيأة
        return response()->json([
            'turbo' => [
                [
                    'id' => 1024,
                    'category_id' => 1,
                    'status_id' => 1,
                    'order_code' => 'ORD-8812',
                    'title' => 'تأخير في استلام الشحنة',
                    'content' => 'تم تأخير الطلب الخاص بالعميل ولم يستلم الشحنة في الموعد المحدد.',
                    'updated_at_human' => 'منذ ساعتين',
                    'created_at' => '2026-07-24 10:00 AM',
                    'messages' => [
                        ['id' => 1, 'message' => 'نواجه تأخير في التسليم للعميل', 'is_sender_me' => true, 'created_at' => '10:05 AM'],
                        ['id' => 2, 'message' => 'جاري المتابعة مع المندوب الخاص بمنطقتكم.', 'is_sender_me' => false, 'created_at' => '10:30 AM'],
                    ]
                ]
            ],
            'categories' => [
                ['id' => 1, 'name' => 'مشاكل الشحن والتوصيل'],
                ['id' => 2, 'name' => 'استفسار عن شحنة'],
                ['id' => 3, 'name' => 'مرتجعات ومفقودات'],
            ],
            'statuses' => [
                ['id' => 1, 'name' => 'مفتوحة (تحتاج متابعة)'],
                ['id' => 2, 'name' => 'قيد المعالجة مع Turbo'],
                ['id' => 3, 'name' => 'مغلقة'],
            ]
        ]);
    }

    public function storeTurbo(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required',
            'order_code'  => 'nullable|string',
            'content'     => 'required|string',
        ]);

        // إرسال البيانات لـ API Turbo
        return response()->json(['success' => true]);
    }

    public function sendMessage(Request $request, $turbo)
    {
        $request->validate(['message' => 'required|string']);
        return response()->json(['success' => true]);
    }

    public function rateTurbo(Request $request, $turbo)
    {
        $request->validate(['rating' => 'required|integer|min:1|max:5']);
        return response()->json(['success' => true]);
    }
}