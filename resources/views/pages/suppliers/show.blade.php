@extends('layouts.app')

@section('title', 'ملف المورد | ' . $supplier->name)
@section('page_title', 'ملف المورد وكشف الحساب - ' . $supplier->name)

@section('content')
<div class="space-y-6 pb-12 font-sans w-full" x-data="{ isPaymentModalOpen: false }">

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

    @if ($errors->any())
        <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm">
            <p class="font-bold mb-1">يرجى تصحيح الأخطاء التالية:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-[#8A704C]/10 border border-[#8A704C]/20 flex items-center justify-center text-[#8A704C] font-bold text-2xl shadow-sm">
                {{ strtoupper(mb_substr($supplier->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-black text-gray-900">{{ $supplier->name }}</h2>
                    <span class="px-3 py-1 text-xs font-bold rounded-full {{ $supplier->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $supplier->is_active ? 'نشط' : 'غير نشط' }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 mt-1">
                    @if ($supplier->phone)
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-[#8A704C]"></i> {{ $supplier->phone }}</span>
                    @endif
                    @if ($supplier->email)
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-[#8A704C]"></i> {{ $supplier->email }}</span>
                    @endif
                    @if ($supplier->address)
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-[#8A704C]"></i> {{ $supplier->address }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <button @click="isPaymentModalOpen = true" class="flex-1 sm:flex-initial bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-bold transition shadow-sm flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-money-bill-wave"></i>
                <span>تسجيل دفعة / سند صرف</span>
            </button>
            <a href="{{ route('purchase-invoices.create') }}?supplier_id={{ $supplier->id }}" class="flex-1 sm:flex-initial bg-[#8A704C] hover:bg-[#735d3f] text-white px-4 py-2.5 rounded-xl font-bold transition shadow-sm flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-plus"></i>
                <span>فاتورة شراء جديدة</span>
            </a>
            <a href="{{ route('suppliers.index') }}" class="p-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition" title="العودة لقائمة الموردين">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي المشتريات</p>
                    <p class="text-2xl font-black text-gray-900">{{ number_format($supplier->total_purchases, 2) }} <span class="text-xs font-normal text-gray-500">ج.م</span></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-3 font-medium">عدد الفواتير: {{ $supplier->purchaseInvoices()->count() }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي المسدد</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($supplier->total_paid, 2) }} <span class="text-xs font-normal text-gray-500">ج.م</span></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-3 font-medium">عدد الدفعات: {{ $supplier->payments()->count() }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">الرصيد المتبقي (مستحق للمورد)</p>
                    <p class="text-2xl font-black {{ $supplier->balance > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ number_format($supplier->balance, 2) }} <span class="text-xs font-normal text-gray-500">ج.م</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-scale-unbalanced"></i>
                </div>
            </div>
            <p class="text-[11px] text-gray-400 mt-3 font-medium">
                {{ $supplier->balance > 0 ? 'يوجد مستحقات قائمة للمورد' : 'الحساب خالص بالكامل' }}
            </p>
        </div>
    </div>

    <!-- Notes if any -->
    @if ($supplier->notes)
        <div class="bg-amber-50/70 border border-amber-200/60 p-4 rounded-xl text-amber-900 text-sm flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-amber-600 text-lg mt-0.5"></i>
            <div>
                <span class="font-bold">ملاحظات المورد:</span> {{ $supplier->notes }}
            </div>
        </div>
    @endif

    <!-- Tables Tabs: Purchase Invoices & Payments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Purchase Invoices Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/40">
                <div class="flex items-center gap-2 font-bold text-gray-800">
                    <i class="fa-solid fa-file-invoice text-[#8A704C]"></i>
                    <span>فواتير الشراء</span>
                </div>
                <a href="{{ route('purchase-invoices.create') }}?supplier_id={{ $supplier->id }}" class="text-xs font-bold text-[#8A704C] hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-plus"></i> إضافة فاتورة
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/60 text-gray-500 text-xs">
                        <tr>
                            <th class="p-3.5">رقم الفاتورة</th>
                            <th class="p-3.5">التاريخ</th>
                            <th class="p-3.5">الإجمالي</th>
                            <th class="p-3.5">الحالة</th>
                            <th class="p-3.5 text-center">إجراء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-3.5 font-bold text-gray-900">
                                    <a href="{{ route('purchase-invoices.show', $inv) }}" class="text-[#8A704C] hover:underline">
                                        {{ $inv->invoice_number }}
                                    </a>
                                    @if ($inv->supplier_invoice_number)
                                        <div class="text-[11px] text-gray-400">ورقي: {{ $inv->supplier_invoice_number }}</div>
                                    @endif
                                </td>
                                <td class="p-3.5 text-gray-600 text-xs">{{ $inv->invoice_date->format('Y-m-d') }}</td>
                                <td class="p-3.5 font-bold text-gray-900">
                                    {{ number_format($inv->total_amount, 2) }}
                                    <div class="text-[10px] {{ $inv->remaining_amount > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                        متبقي: {{ number_format($inv->remaining_amount, 2) }}
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    @if ($inv->status === 'received')
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-700">مستلمة بالمخزن</span>
                                    @elseif ($inv->status === 'draft')
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-amber-100 text-amber-700">مسودة</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-red-100 text-red-700">ملغاة</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    <a href="{{ route('purchase-invoices.show', $inv) }}" class="p-1.5 rounded-lg text-gray-500 hover:text-[#8A704C] hover:bg-gray-100 transition inline-block">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400">لا توجد فواتير شراء مسجلة لهذا المورد حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($invoices->hasPages())
                <div class="p-3 border-t border-gray-100 bg-gray-50/30">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>

        <!-- Payments / Vouchers Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/40">
                <div class="flex items-center gap-2 font-bold text-gray-800">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <span>سندات الصرف والدفعات المسددة</span>
                </div>
                <button @click="isPaymentModalOpen = true" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-plus"></i> تسجيل دفعة
                </button>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/60 text-gray-500 text-xs">
                        <tr>
                            <th class="p-3.5">رقم السند</th>
                            <th class="p-3.5">التاريخ</th>
                            <th class="p-3.5">المبلغ</th>
                            <th class="p-3.5">طريقة الدفع</th>
                            <th class="p-3.5">الفاتورة المرتبطة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payments as $pay)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-3.5 font-bold text-gray-900">{{ $pay->payment_number }}</td>
                                <td class="p-3.5 text-gray-600 text-xs">{{ $pay->payment_date->format('Y-m-d') }}</td>
                                <td class="p-3.5 font-black text-emerald-600">{{ number_format($pay->amount, 2) }} ج.م</td>
                                <td class="p-3.5 text-xs text-gray-600">
                                    @php
                                        $methods = [
                                            'cash' => 'نقداً (كاش)',
                                            'bank_transfer' => 'تحويل بنكي',
                                            'vodafone_cash' => 'محفظة إلكترونية',
                                            'cheque' => 'شيك بنكي',
                                        ];
                                    @endphp
                                    {{ $methods[$pay->payment_method] ?? $pay->payment_method }}
                                </td>
                                <td class="p-3.5 text-xs">
                                    @if ($pay->purchaseInvoice)
                                        <a href="{{ route('purchase-invoices.show', $pay->purchaseInvoice) }}" class="text-[#8A704C] hover:underline font-bold">
                                            {{ $pay->purchaseInvoice->invoice_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">على الحساب العام</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400">لا توجد سندات صرف أو دفعات مسددة لهذا المورد بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="p-3 border-t border-gray-100 bg-gray-50/30">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Add Payment / Voucher -->
    <div x-show="isPaymentModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
        <div @click.away="isPaymentModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                    <span>تسجيل سند صرف / دفعة للمورد</span>
                </h3>
                <button @click="isPaymentModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('suppliers.payments.store', $supplier) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">المبلغ المسدد (ج.م) *</label>
                    <input type="number" step="0.01" name="amount" required max="{{ $supplier->balance > 0 ? $supplier->balance : '' }}" value="{{ $supplier->balance > 0 ? $supplier->balance : '' }}"
                        class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-base font-bold">
                    @if ($supplier->balance > 0)
                        <p class="text-xs text-gray-500 mt-1">الرصيد المستحق الحالي: {{ number_format($supplier->balance, 2) }} ج.م</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">تاريخ الصرف *</label>
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                            class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة الدفع *</label>
                        <select name="payment_method" required class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                            <option value="cash">نقداً (كاش)</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="vodafone_cash">فودافون كاش / محفظة</option>
                            <option value="cheque">شيك بنكي</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">ربط بفاتورة شراء محددة (اختياري)</label>
                    <select name="purchase_invoice_id" class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                        <option value="">-- دفع على الحساب العام للمورد --</option>
                        @foreach ($supplier->purchaseInvoices()->where('remaining_amount', '>', 0)->get() as $unpaidInv)
                            <option value="{{ $unpaidInv->id }}">
                                {{ $unpaidInv->invoice_number }} (متبقي: {{ number_format($unpaidInv->remaining_amount, 2) }} ج.م)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">رقم الإيصال / المرجع (اختياري)</label>
                    <input type="text" name="reference_number" placeholder="رقم الشيك أو الحوالة إن وجد"
                        class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" placeholder="أي تفاصيل إضافية..."
                        class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="isPaymentModalOpen = false" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-check"></i>
                        <span>تأكيد وحفظ السند</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
