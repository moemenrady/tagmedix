<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the settings.
     */
    public function index()
    {
        $defaultDeliveryDays = (int) Setting::get('default_delivery_days', 7);
        $urgentDeliveryDays = (int) Setting::get('urgent_delivery_days', 3);

        return view('pages.settings.index', compact('defaultDeliveryDays', 'urgentDeliveryDays'));
    }

    /**
     * Update delivery duration settings.
     */
    public function updateDeliverySettings(Request $request)
    {
        $validated = $request->validate([
            'default_delivery_days' => 'required|integer|min:1|max:365',
            'urgent_delivery_days'  => 'required|integer|min:1|max:365',
        ], [
            'default_delivery_days.required' => 'يرجى إدخال مدة تسليم الطلب العادي بالأيام.',
            'urgent_delivery_days.required'  => 'يرجى إدخال مدة تسليم الطلب المستعجل بالأيام.',
        ]);

        Setting::set('default_delivery_days', $validated['default_delivery_days']);
        Setting::set('urgent_delivery_days', $validated['urgent_delivery_days']);

        return redirect()
            ->route('settings.index')
            ->with('success', 'تم حفظ وتحديث إعدادات مدة تسليم الطلبات بنجاح.');
    }
}
