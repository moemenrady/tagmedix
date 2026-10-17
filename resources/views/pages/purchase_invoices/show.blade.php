@extends('layouts.app')

@section('title', 'فاتورة شراء | ' . $purchaseInvoice->invoice_number)
@section('page_title', 'تفاصيل فاتورة الشراء - ' . $purchaseInvoice->invoice_number)

@section('content')
<div class="space-y-6 pb-16 font-sans w-full" x-data="{ isPaymentModalOpen: false }">

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="bg-green-50 border-r-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm flex justify-between items-center print:hidden">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if (session('info'))
        <div class="bg-blue-50 border-r-4 border-blue-500 text-blue-700 p-4 rounded-xl shadow-sm print:hidden">
            <p class="font-medium">{{ session('info') }}</p>
        </div>
    @endif

    <!-- Actions & Toolbar (Hidden on print) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-invoices.index') }}" class="p-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition" title="العودة للقائمة">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                    <span>فاتورة شراء: {{ $purchaseInvoice->invoice_number }}</span>
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">أنشئت بواسطة: {{ $purchaseInvoice->creator?->name ?? 'النظام' }} في {{ $purchaseInvoice->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Receive Button if Draft -->
            @if ($purchaseInvoice->status === 'draft')
                <form action="{{ route('purchase-invoices.receive', $purchaseInvoice) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من اعتماد الفاتورة واستلام جميع بنودها في المخزن؟ سيتم زيادة كميات المخزون فوراً.')">
                    @csrf
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>اعتماد واستلام بالمخزن</span>
                    </button>
                </form>
            @endif

            <!-- Add Payment Button if Remaining > 0 -->
            @if ($purchaseInvoice->remaining_amount > 0)
                <button @click="isPaymentModalOpen = true" class="bg-[#8A704C] hover:bg-[#735d3f] text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>سداد دفعة</span>
                </button>
            @endif

            <!-- Print Button -->
            <button onclick="window.print()" class="bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>طباعة الفاتورة</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-md p-8 sm:p-12 space-y-8">

        <!-- Top Header & Branding -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-8 border-b-2 border-gray-100 gap-6">
            <div>
                <h1 class="text-3xl font-black text-[#8A704C] tracking-tight">Tagmedix</h1>
                <p class="text-xs font-bold text-gray-400 mt-1 uppercase tracking-widest">Medical Scrubs & Apparel ERP</p>
                <div class="mt-4 text-xs text-gray-500 space-y-1">
                    <p><span class="font-bold text-gray-700">نوع المستند:</span> فاتورة توريد وشراء مواد خام</p>
                    <p><span class="font-bold text-gray-700">رقم الفاتورة:</span> <span class="font-mono text-gray-900 font-bold">{{ $purchaseInvoice->invoice_number }}</span></p>
                    @if ($purchaseInvoice->supplier_invoice_number)
                        <p><span class="font-bold text-gray-700">رقم فاتورة المورد الورقية:</span> {{ $purchaseInvoice->supplier_invoice_number }}</p>
                    @endif
                </div>
            </div>

            <!-- Status Badges & Dates -->
            <div class="text-left rtl:text-right sm:rtl:text-left space-y-3">
                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    <!-- Warehouse Status -->
                    @if ($purchaseInvoice->status === 'received')
                        <span class="px-3.5 py-1 text-xs font-black rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-xs"></i> مستلمة في المخزن
                        </span>
                    @elseif ($purchaseInvoice->status === 'draft')
                        <span class="px-3.5 py-1 text-xs font-black rounded-full bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock text-xs"></i> مسودة غير مستلمة
                        </span>
                    @else
                        <span class="px-3.5 py-1 text-xs font-black rounded-full bg-red-100 text-red-800 border border-red-200">
                            ملغاة
                        </span>
                    @endif

                    <!-- Payment Status -->
                    @if ($purchaseInvoice->payment_status === 'paid')
                        <span class="px-3.5 py-1 text-xs font-black rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            مدفوعة بالكامل
                        </span>
                    @elseif ($purchaseInvoice->payment_status === 'partial')
                        <span class="px-3.5 py-1 text-xs font-black rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                            مدفوعة جزئياً
                        </span>
                    @else
                        <span class="px-3.5 py-1 text-xs font-black rounded-full bg-red-50 text-red-700 border border-red-200">
                            آجل (غير مدفوعة)
                        </span>
                    @endif
                </div>

                <div class="text-xs text-gray-500 space-y-1 sm:text-left">
                    <p><span class="font-bold text-gray-700">تاريخ الفاتورة:</span> {{ $purchaseInvoice->invoice_date->format('Y-m-d') }}</p>
                    @if ($purchaseInvoice->due_date)
                        <p><span class="font-bold text-gray-700">تاريخ الاستحقاق:</span> {{ $purchaseInvoice->due_date->format('Y-m-d') }}</p>
                    @endif
                    @if ($purchaseInvoice->received_at)
                        <p><span class="font-bold text-emerald-700">تاريخ الاستلام المخزني:</span> {{ $purchaseInvoice->received_at->format('Y-m-d H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Supplier Details Box -->
        <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">بيانات المورد</p>
                <h3 class="text-xl font-black text-gray-900">{{ $purchaseInvoice->supplier->name }}</h3>
                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 mt-2">
                    @if ($purchaseInvoice->supplier->phone)
                        <span><i class="fa-solid fa-phone text-[#8A704C] ml-1"></i> {{ $purchaseInvoice->supplier->phone }}</span>
                    @endif
                    @if ($purchaseInvoice->supplier->email)
                        <span><i class="fa-solid fa-envelope text-[#8A704C] ml-1"></i> {{ $purchaseInvoice->supplier->email }}</span>
                    @endif
                    @if ($purchaseInvoice->supplier->address)
                        <span><i class="fa-solid fa-location-dot text-[#8A704C] ml-1"></i> {{ $purchaseInvoice->supplier->address }}</span>
                    @endif
                </div>
            </div>

            <div class="print:hidden">
                <a href="{{ route('suppliers.show', $purchaseInvoice->supplier) }}" class="text-xs font-bold text-[#8A704C] hover:underline flex items-center gap-1.5">
                    <span>فتح كشف حساب المورد</span>
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-200 text-gray-500 text-xs font-black uppercase">
                        <th class="py-3 px-2 w-12 text-center">#</th>
                        <th class="py-3 px-4">الصنف وبيانات الخامة</th>
                        <th class="py-3 px-4 text-center">المتغير / اللون</th>
                        <th class="py-3 px-4 text-center">الكمية</th>
                        <th class="py-3 px-4 text-center">الوحدة</th>
                        <th class="py-3 px-4 text-center">سعر الوحدة</th>
                        <th class="py-3 px-4 text-left">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($purchaseInvoice->items as $index => $item)
                        <tr class="hover:bg-gray-50/40 transition">
                            <td class="py-4 px-2 text-center text-xs font-bold text-gray-400">{{ $index + 1 }}</td>
                            <td class="py-4 px-4 font-bold text-gray-900">
                                <div>{{ $item->item_name }}</div>
                                <div class="text-[11px] text-gray-400 font-normal">كود: {{ $item->inventoryItem?->code ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 text-center text-xs text-gray-600">
                                {{ $item->variant?->color_name ?? $item->variant?->sku ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-center font-black text-gray-900">
                                {{ number_format($item->quantity, 2) }}
                            </td>
                            <td class="py-4 px-4 text-center text-xs text-gray-500">
                                {{ $item->unit_type }}
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-gray-800">
                                {{ number_format($item->unit_price, 2) }} ج.م
                            </td>
                            <td class="py-4 px-4 text-left font-black text-gray-900">
                                {{ number_format($item->total_price, 2) }} ج.م
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary & Totals Calculation Box -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-8 pt-4 border-t border-gray-100">
            <!-- Notes -->
            <div class="w-full sm:w-1/2 text-xs text-gray-500 space-y-2">
                @if ($purchaseInvoice->notes)
                    <p class="font-bold text-gray-700">ملاحظات وشروط الفاتورة:</p>
                    <p class="p-3 bg-gray-50 rounded-xl border border-gray-100 whitespace-pre-line">{{ $purchaseInvoice->notes }}</p>
                @endif
            </div>

            <!-- Financial Totals -->
            <div class="w-full sm:w-1/2 max-w-sm space-y-2.5 text-sm mr-auto">
                <div class="flex justify-between items-center text-gray-600">
                    <span>المجموع الفرعي:</span>
                    <span class="font-bold text-gray-900">{{ number_format($purchaseInvoice->subtotal, 2) }} ج.م</span>
                </div>

                @if ($purchaseInvoice->discount > 0)
                    <div class="flex justify-between items-center text-emerald-700">
                        <span>خصم تجاري:</span>
                        <span class="font-bold">- {{ number_format($purchaseInvoice->discount, 2) }} ج.م</span>
                    </div>
                @endif

                @if ($purchaseInvoice->tax > 0)
                    <div class="flex justify-between items-center text-gray-600">
                        <span>ضريبة / دمغات:</span>
                        <span class="font-bold">+ {{ number_format($purchaseInvoice->tax, 2) }} ج.م</span>
                    </div>
                @endif

                @if ($purchaseInvoice->shipping_cost > 0)
                    <div class="flex justify-between items-center text-gray-600">
                        <span>مصاريف نقل وشحن:</span>
                        <span class="font-bold">+ {{ number_format($purchaseInvoice->shipping_cost, 2) }} ج.م</span>
                    </div>
                @endif

                <div class="h-px bg-gray-200 my-2"></div>

                <div class="flex justify-between items-center text-lg font-black text-gray-900">
                    <span>صافي الفاتورة الإجمالي:</span>
                    <span class="text-[#8A704C]">{{ number_format($purchaseInvoice->total_amount, 2) }} ج.م</span>
                </div>

                <div class="flex justify-between items-center text-sm font-bold text-emerald-700 pt-1">
                    <span>المبلغ المسدد:</span>
                    <span>{{ number_format($purchaseInvoice->paid_amount, 2) }} ج.م</span>
                </div>

                <div class="flex justify-between items-center text-sm font-black {{ $purchaseInvoice->remaining_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">
                    <span>المتبقي (آجل):</span>
                    <span>{{ number_format($purchaseInvoice->remaining_amount, 2) }} ج.م</span>
                </div>
            </div>
        </div>

        <!-- Payments Log for this Invoice -->
        @if ($purchaseInvoice->payments->count() > 0)
            <div class="pt-6 border-t border-gray-100">
                <h4 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-emerald-600"></i>
                    <span>سجل الدفعات المسددة لهذه الفاتورة</span>
                </h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="p-2.5">رقم السند</th>
                                <th class="p-2.5">التاريخ</th>
                                <th class="p-2.5">المبلغ</th>
                                <th class="p-2.5">طريقة الدفع</th>
                                <th class="p-2.5">رقم المرجع / الشيك</th>
                                <th class="p-2.5">الموظف المسجل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($purchaseInvoice->payments as $pay)
                                <tr>
                                    <td class="p-2.5 font-bold text-gray-800">{{ $pay->payment_number }}</td>
                                    <td class="p-2.5 text-gray-600">{{ $pay->payment_date->format('Y-m-d') }}</td>
                                    <td class="p-2.5 font-bold text-emerald-600">{{ number_format($pay->amount, 2) }} ج.م</td>
                                    <td class="p-2.5">{{ $pay->payment_method }}</td>
                                    <td class="p-2.5 text-gray-500">{{ $pay->reference_number ?? '-' }}</td>
                                    <td class="p-2.5 text-gray-500">{{ $pay->creator?->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <!-- Modal: Add Payment against this invoice -->
    <div x-show="isPaymentModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 print:hidden">
        <div @click.away="isPaymentModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gray-50/60">
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                    <span>سداد دفعة لفاتورة: {{ $purchaseInvoice->invoice_number }}</span>
                </h3>
                <button @click="isPaymentModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('purchase-invoices.payments.store', $purchaseInvoice) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">المبلغ المسدد (ج.م) *</label>
                    <input type="number" step="0.01" name="amount" required max="{{ $purchaseInvoice->remaining_amount }}" value="{{ $purchaseInvoice->remaining_amount }}"
                        class="w-full rounded-xl border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-base font-bold">
                    <p class="text-xs text-gray-500 mt-1">المتبقي على الفاتورة: {{ number_format($purchaseInvoice->remaining_amount, 2) }} ج.م</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">تاريخ الصرف *</label>
                        <input type="date" name="payment_date" required value="{{ date('Y-m-d') }}"
                            class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة الدفع *</label>
                        <select name="payment_method" required class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                            <option value="cash">نقداً (كاش)</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="vodafone_cash">فودافون كاش / محفظة</option>
                            <option value="cheque">شيك بنكي</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">رقم المرجع / الشيك (اختياري)</label>
                    <input type="text" name="reference_number" placeholder="رقم الشيك أو التحويل"
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">ملاحظات</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="isPaymentModalOpen = false" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-check"></i>
                        <span>تأكيد السداد</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
