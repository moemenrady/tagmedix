<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Transaction;
use App\Models\User;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    public function index()
    {
    
        return view('pages.users.index', );
    }
    public function blockAccount(Request $request, $id)
    {
        // التأكد أن من يقوم بالعملية هو الأدمن فقط
        if (auth()->user()->role !== 'admin') {
            abort(403, 'غير مصرح لك باتخاذ هذا الإجراء');
        }

        $user = \App\Models\User::findOrFail($id);

        // منع الأدمن من حظر نفسه بالخطأ
        if (auth()->id() === $user->id) {
            return back()->with('error', 'لا يمكنك حظر حسابك الشخصي!');
        }

        // تنفيذ الحظر بجعل القيمة null
        $user->email_verified_at = null;
        $user->save();

        // السناك بار (Snackbar) سيظهر إذا كنت مبرمجه لالتقاط الـ Session ('success')
        return back()->with('success', 'تم حظر الحساب بنجاح، لن يتمكن من استخدام النظام.');
    }
}
