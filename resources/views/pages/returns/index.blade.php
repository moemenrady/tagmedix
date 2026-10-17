@extends('layouts.app')

@section('title', 'إدارة المرتجعات | Returns Management')
@section('page_title', 'إدارة المرتجعات وفحص الجودة المخزنية')

@section('content')
<div class="space-y-6 pb-16 font-sans w-full">

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="bg-green-50 border-r-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-rotate-left text-[#8A704C]"></i>
                <span>طلبات المرتجعات وفحص الاسترجاع</span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">متابعة مرتجعات العملاء، فحص جودة القطع، وإعادة السليم لمخزون المنتجات التامة</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('returns.create') }}"
                class="w-full sm:w-auto bg-[#8A704C] hover:bg-[#735d3f] text-white px-5 py-2.5 rounded-xl font-bold transition shadow-md flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-plus"></i>
                <span>تسجيل طلب مرتجع جديد</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي المرتجعات</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_returns_count']) }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-gray-50 text-gray-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">قيد الانتظار والشحن</p>
                    <p class="text-2xl font-black text-amber-600">{{ number_format($stats['pending_count']) }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">مكتمل وتمت إعادة التخزين</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['completed_count']) }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي المبالغ المستردة</p>
                    <p class="text-2xl font-black text-[#8A704C]">{{ number_format($stats['total_refund_amount'], 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <form method="GET" action="{{ route('returns.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">بحث برقم المرتجع أو الأوردر أو العميل</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="رقم المرتجع أو هاتف العميل..."
                    class="w-full rounded-xl border-gray-200 border px-3.5 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">حالة المرتجع</label>
                <select name="status" class="w-full rounded-xl border-gray-200 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                    <option value="">جميع الحالات</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>تم الاستلام</option>
                    <option value="inspected" {{ request('status') == 'inspected' ? 'selected' : '' }}>تم الفحص</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل (معاد للمخزن)</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">نوع المرتجع</label>
                <select name="return_type" class="w-full rounded-xl border-gray-200 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                    <option value="">الكل</option>
                    <option value="full" {{ request('return_type') == 'full' ? 'selected' : '' }}>مرتجع كلي للطلب</option>
                    <option value="partial" {{ request('return_type') == 'partial' ? 'selected' : '' }}>مرتجع جزئي لبنود محددة</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">نتيجة فحص الجودة (QC)</label>
                <select name="qc_status" class="w-full rounded-xl border-gray-200 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                    <option value="">الكل</option>
                    <option value="pending" {{ request('qc_status') == 'pending' ? 'selected' : '' }}>قيد الفحص</option>
                    <option value="good" {{ request('qc_status') == 'good' ? 'selected' : '' }}>سليم (معاد للمخزن)</option>
                    <option value="damaged" {{ request('qc_status') == 'damaged' ? 'selected' : '' }}>تالف / معيب صناعة</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gray-900 hover:bg-black text-white py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> تصفية
                </button>
                @if (request()->hasAny(['search', 'status', 'return_type', 'qc_status']))
                    <a href="{{ route('returns.index') }}" class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-100 text-xs transition" title="إعادة تعيين">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Returns Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50/70 text-gray-500 text-xs border-b border-gray-100">
                    <tr>
                        <th class="p-4">رقم المرتجع</th>
                        <th class="p-4">رقم الطلب</th>
                        <th class="p-4">العميل</th>
                        <th class="p-4">نوع المرتجع والسبب</th>
                        <th class="p-4">المبلغ المسترد</th>
                        <th class="p-4 text-center">حالة الفحص والمخزن</th>
                        <th class="p-4 text-center">حالة الطلب</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($returns as $ret)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-4 font-bold text-gray-900">
                                <a href="{{ route('returns.show', $ret->id) }}" class="text-[#8A704C] hover:underline">
                                    {{ $ret->return_number }}
                                </a>
                                <div class="text-[11px] text-gray-400">{{ $ret->created_at->format('Y-m-d') }}</div>
                            </td>
                            <td class="p-4">
                                <a href="{{ route('orders.show', $ret->order) }}" class="font-bold text-gray-900 hover:text-[#8A704C] hover:underline">
                                    {{ $ret->order->order_number ?? '#' . $ret->order_id }}
                                </a>
                            </td>
                            <td class="p-4 text-xs">
                                <div class="font-bold text-gray-900">{{ $ret->order->customer_name }}</div>
                                <div class="text-gray-400">{{ $ret->order->customer_phone_1 ?? '-' }}</div>
                            </td>
                            <td class="p-4 text-xs">
                                <span class="px-2 py-0.5 rounded-full font-bold {{ $ret->return_type === 'full' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $ret->return_type === 'full' ? 'مرتجع كلي' : 'مرتجع جزئي (' . $ret->items->count() . ' بنود)' }}
                                </span>
                                <div class="text-gray-600 mt-1 truncate max-w-xs" title="{{ $ret->reason }}">
                                    السبب: {{ $ret->reason }}
                                </div>
                            </td>
                            <td class="p-4 font-black text-gray-900 text-sm">
                                {{ number_format($ret->refund_amount, 2) }} ج.م
                                @if ($ret->shipping_cost_deducted > 0)
                                    <div class="text-[10px] text-gray-400 font-normal">خصم شحن: {{ number_format($ret->shipping_cost_deducted, 2) }}</div>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if ($ret->qc_status === 'good')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[10px]"></i> سليم (معاد للمخزن)
                                    </span>
                                @elseif ($ret->qc_status === 'damaged')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                        <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> تالف / هالك
                                    </span>
                                @elseif ($ret->qc_status === 'partial_good')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                        جزئي (سليم وتالف)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-magnifying-glass text-[10px]"></i> قيد الفحص
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if ($ret->status === 'completed')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">مكتمل</span>
                                @elseif ($ret->status === 'pending')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200">قيد الانتظار</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-700">{{ $ret->status }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <a href="{{ route('returns.show', $ret->id) }}" class="p-2 rounded-xl text-gray-500 hover:text-[#8A704C] hover:bg-gray-100 transition inline-block" title="عرض وفحص">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-gray-400">
                                <i class="fa-solid fa-rotate-left text-4xl mb-3 block text-gray-300"></i>
                                لا توجد طلبات مرتجعات مسجلة مطابقة للبحث.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($returns->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
