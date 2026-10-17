@extends('layouts.app')

@section('title', 'فواتير الشراء | Purchase Invoices')
@section('page_title', 'فواتير الشراء والمشتريات')

@section('content')
<div class="space-y-6 pb-12 font-sans w-full">

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

    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-[#8A704C]"></i>
                <span>فواتير مشتريات الخامات والمستلزمات</span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">إدارة مشتريات الأقمشة والإكسسوارات وربطها بالمخازن والموردين</p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('purchase-invoices.create') }}"
                class="w-full sm:w-auto bg-[#8A704C] hover:bg-[#735d3f] text-white px-5 py-2.5 rounded-xl font-bold transition shadow-md flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-plus"></i>
                <span>إنشاء فاتورة شراء جديدة</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">عدد الفواتير</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_invoices_count']) }}</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-gray-50 text-gray-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي المشتريات</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_amount'], 2) }} <span class="text-xs font-normal text-gray-500">ج.م</span></p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي المسدد</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['total_paid'], 2) }} <span class="text-xs font-normal text-gray-500">ج.م</span></p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">المتبقي (آجل للموردين)</p>
                    <p class="text-2xl font-black {{ $stats['total_remaining'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ number_format($stats['total_remaining'], 2) }} <span class="text-xs font-normal text-gray-500">ج.م</span>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-scale-unbalanced"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
        <form method="GET" action="{{ route('purchase-invoices.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">بحث برقم الفاتورة أو المورد</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="رقم الفاتورة..."
                    class="w-full rounded-xl border-gray-200 border px-3.5 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">المورد</label>
                <select name="supplier_id" class="w-full rounded-xl border-gray-200 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                    <option value="">جميع الموردين</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">حالة الفاتورة المخزنية</label>
                <select name="status" class="w-full rounded-xl border-gray-200 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                    <option value="">الكل</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>مستلمة بالمخزن</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغاة</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">حالة الدفع</label>
                <select name="payment_status" class="w-full rounded-xl border-gray-200 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                    <option value="">الكل</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوعة بالكامل</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة (آجل)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gray-900 hover:bg-black text-white py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> تصفية
                </button>
                @if (request()->hasAny(['search', 'supplier_id', 'status', 'payment_status', 'date_from', 'date_to']))
                    <a href="{{ route('purchase-invoices.index') }}" class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-100 text-xs transition" title="إعادة تعيين">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50/70 text-gray-500 text-xs border-b border-gray-100">
                    <tr>
                        <th class="p-4">رقم الفاتورة</th>
                        <th class="p-4">المورد</th>
                        <th class="p-4">تاريخ الفاتورة</th>
                        <th class="p-4">الإجمالي</th>
                        <th class="p-4">المدفوع / المتبقي</th>
                        <th class="p-4 text-center">المخزن</th>
                        <th class="p-4 text-center">الدفع</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($invoices as $inv)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-4">
                                <a href="{{ route('purchase-invoices.show', $inv) }}" class="font-black text-[#8A704C] hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                                @if ($inv->supplier_invoice_number)
                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                        فاتورة المورد: {{ $inv->supplier_invoice_number }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 font-bold text-gray-800">
                                <a href="{{ route('suppliers.show', $inv->supplier) }}" class="hover:text-[#8A704C] hover:underline flex items-center gap-1.5">
                                    <i class="fa-solid fa-truck-field text-xs text-gray-400"></i>
                                    <span>{{ $inv->supplier->name }}</span>
                                </a>
                            </td>
                            <td class="p-4 text-xs text-gray-600">
                                {{ $inv->invoice_date->format('Y-m-d') }}
                            </td>
                            <td class="p-4 font-black text-gray-900 text-base">
                                {{ number_format($inv->total_amount, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span>
                            </td>
                            <td class="p-4 text-xs">
                                <div class="text-emerald-700 font-bold">مسدد: {{ number_format($inv->paid_amount, 2) }} ج.م</div>
                                @if ($inv->remaining_amount > 0)
                                    <div class="text-red-600 font-bold mt-0.5">متبقي: {{ number_format($inv->remaining_amount, 2) }} ج.م</div>
                                @else
                                    <div class="text-gray-400 mt-0.5">خالص بالكامل</div>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if ($inv->status === 'received')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-boxes-stacked text-[10px]"></i> مستلمة
                                    </span>
                                @elseif ($inv->status === 'draft')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-clock text-[10px]"></i> مسودة
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                        <i class="fa-solid fa-ban text-[10px]"></i> ملغاة
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if ($inv->payment_status === 'paid')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">مدفوع</span>
                                @elseif ($inv->payment_status === 'partial')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-200">جزئي</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-50 text-red-700 border border-red-200">آجل</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('purchase-invoices.show', $inv) }}" class="p-2 rounded-xl text-gray-500 hover:text-[#8A704C] hover:bg-gray-100 transition" title="عرض التفاصيل">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-gray-400">
                                <i class="fa-solid fa-file-invoice text-4xl mb-3 block text-gray-300"></i>
                                لا توجد فواتير شراء مسجلة مطابقة لبحثك.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
