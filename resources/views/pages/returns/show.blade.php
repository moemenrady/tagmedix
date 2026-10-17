@extends('layouts.app')

@section('title', 'تفاصيل المرتجع | ' . $orderReturn->return_number)
@section('page_title', 'تفاصيل طلب المرتجع - ' . $orderReturn->return_number)

@section('content')
<div class="space-y-6 pb-16 font-sans w-full" x-data="{ isRefundModalOpen: false }">

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

    <!-- Actions & Toolbar (Hidden on print) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('returns.index') }}" class="p-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition" title="العودة للمرتجعات">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                    <span>مرتجع: {{ $orderReturn->return_number }}</span>
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    الطلب: <a href="{{ route('orders.show', $orderReturn->order) }}" class="font-bold text-[#8A704C] hover:underline">{{ $orderReturn->order->order_number }}</a>
                    - سجل في {{ $orderReturn->created_at->format('Y-m-d H:i') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            @if ($orderReturn->refund_status !== 'refunded')
                <button @click="isRefundModalOpen = true" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    <span>تأكيد استرداد المبلغ للعميل</span>
                </button>
            @endif

            <button onclick="window.print()" class="bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>طباعة تقرير المرتجع</span>
            </button>
        </div>
    </div>

    <!-- Main Sheet -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-md p-8 sm:p-10 space-y-8">

        <!-- Top Header & Badges -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-gray-100 gap-4">
            <div>
                <h1 class="text-2xl font-black text-[#8A704C]">Tagmedix - إشعار مرتجع وفحص جودة</h1>
                <p class="text-xs text-gray-400 mt-0.5">رقم المرتجع: <span class="font-mono text-gray-900 font-bold">{{ $orderReturn->return_number }}</span></p>
                <div class="mt-2 text-xs text-gray-600">
                    <span class="font-bold">سبب الإرجاع:</span> {{ $orderReturn->reason }}
                </div>
            </div>

            <div class="text-left rtl:text-right sm:rtl:text-left space-y-2">
                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    <!-- Status -->
                    @if ($orderReturn->status === 'completed')
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                            <i class="fa-solid fa-check"></i> مكتمل ومعاد للمخزن
                        </span>
                    @elseif ($orderReturn->status === 'inspected')
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                            تم الفحص
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                            <i class="fa-solid fa-clock"></i> قيد الانتظار والفحص
                        </span>
                    @endif

                    <!-- Refund Status -->
                    @if ($orderReturn->refund_status === 'refunded')
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            تم استرداد المبلغ
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-50 text-red-700 border border-red-200">
                            المبلغ معلق لم يسترد
                        </span>
                    @endif
                </div>

                <p class="text-xs text-gray-400">تاريخ التسجيل: {{ $orderReturn->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <!-- Order & Customer Summary Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/80 rounded-2xl p-6 border border-gray-100 text-sm">
            <!-- Customer -->
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">بيانات العميل والأوردر</p>
                <h3 class="font-black text-gray-900 text-base">{{ $orderReturn->order->customer_name }}</h3>
                <div class="text-xs text-gray-600 mt-1 space-y-1">
                    <p><i class="fa-solid fa-phone text-[#8A704C] ml-1"></i> {{ $orderReturn->order->customer_phone_1 ?? '-' }}</p>
                    <p><i class="fa-solid fa-location-dot text-[#8A704C] ml-1"></i> {{ $orderReturn->order->full_address ?? 'العنوان غير محدد' }}</p>
                    <p class="font-bold text-gray-800 pt-1">
                        رقم الطلب الأصلي:
                        <a href="{{ route('orders.show', $orderReturn->order) }}" class="text-[#8A704C] hover:underline">
                            {{ $orderReturn->order->order_number }}
                        </a>
                    </p>
                </div>
            </div>

            <!-- Financials -->
            <div class="border-t md:border-t-0 md:border-r border-gray-200 md:pr-6 space-y-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">القيم المالية لعملية الإرجاع</p>
                <div class="flex justify-between items-center text-xs text-gray-600">
                    <span>إجمالي قيمة الأصناف المرتجعة:</span>
                    <span class="font-bold text-gray-900">{{ number_format($orderReturn->items->sum('total_price'), 2) }} ج.م</span>
                </div>
                <div class="flex justify-between items-center text-xs text-red-600">
                    <span>مصاريف الشحن المخصومة:</span>
                    <span class="font-bold">- {{ number_format($orderReturn->shipping_cost_deducted, 2) }} ج.م</span>
                </div>
                <div class="h-px bg-gray-200 my-1"></div>
                <div class="flex justify-between items-center text-base font-black text-gray-900">
                    <span>صافي المبلغ المسترد للعميل:</span>
                    <span class="text-[#8A704C]">{{ number_format($orderReturn->refund_amount, 2) }} ج.م</span>
                </div>
                <div class="text-[11px] text-gray-500 mt-1">
                    الإجراء المتخذ: <span class="font-bold text-gray-800">{{ $orderReturn->action_taken ?? 'استرجاع نقدي' }}</span>
                </div>
            </div>
        </div>

        <!-- QC Inspection & Items Table -->
        <div class="space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <h3 class="text-sm font-black text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-[#8A704C]"></i>
                    <span>البنود المرتجعة ونتائج فحص الجودة (Quality Check)</span>
                </h3>

                @if ($orderReturn->status !== 'completed')
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                        قم بفحص القطع واختيار نتيجتها لإعادتها للمخزن
                    </span>
                @else
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200 flex items-center gap-1">
                        <i class="fa-solid fa-circle-check"></i> تم فحص الجودة وإعادة التخزين
                    </span>
                @endif
            </div>

            <!-- Form to process QC -->
            <form action="{{ route('returns.qc', $orderReturn->id) }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs">
                            <tr>
                                <th class="p-3.5">#</th>
                                <th class="p-3.5">الموديل والصنف</th>
                                <th class="p-3.5">المقاس واللون</th>
                                <th class="p-3.5 text-center">الكمية</th>
                                <th class="p-3.5 text-center">سعر الوحدة</th>
                                <th class="p-3.5 text-center">الإجمالي</th>
                                <th class="p-3.5 text-center">نتيجة الفحص (QC)</th>
                                <th class="p-3.5 text-center">المخزن</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($orderReturn->items as $index => $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="p-3.5 text-xs text-gray-400 text-center">{{ $index + 1 }}</td>
                                    <td class="p-3.5 font-bold text-gray-900">
                                        {{ $item->orderItem?->style?->name ?? 'منتج سكراب' }}
                                        <div class="text-[11px] text-gray-400 font-normal">{{ $item->orderItem?->type?->name }}</div>
                                    </td>
                                    <td class="p-3.5 text-xs text-gray-700">
                                        {{ $item->orderItem?->color?->name ?? '-' }} / {{ $item->orderItem?->size?->name ?? '-' }}
                                    </td>
                                    <td class="p-3.5 text-center font-black text-gray-900">{{ $item->quantity }}</td>
                                    <td class="p-3.5 text-center text-xs font-bold">{{ number_format($item->unit_price, 2) }} ج.م</td>
                                    <td class="p-3.5 text-center font-black text-gray-900">{{ number_format($item->total_price, 2) }} ج.م</td>

                                    <!-- QC Result Input / Badge -->
                                    <td class="p-3.5 text-center">
                                        @if ($orderReturn->status !== 'completed')
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            <select name="items[{{ $index }}][qc_result]" class="rounded-xl border-gray-300 border px-3 py-1.5 text-xs font-bold outline-none focus:ring-1 focus:ring-[#8A704C]">
                                                <option value="good" {{ $item->qc_result === 'good' ? 'selected' : '' }}>سليم (صالح للبيع)</option>
                                                <option value="damaged" {{ $item->qc_result === 'damaged' ? 'selected' : '' }}>تالف (عيب صناعة / هالك)</option>
                                            </select>
                                        @else
                                            @if ($item->qc_result === 'good')
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                                    <i class="fa-solid fa-check"></i> سليم (معاد للمخزن)
                                                </span>
                                            @elseif ($item->qc_result === 'damaged')
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                                    <i class="fa-solid fa-ban"></i> تالف (هالك)
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-700">لم يفحص</span>
                                            @endif
                                        @endif
                                    </td>

                                    <!-- Warehouse Restock Status -->
                                    <td class="p-3.5 text-center text-xs">
                                        @if ($item->is_restocked)
                                            <span class="text-emerald-700 font-bold flex items-center justify-center gap-1">
                                                <i class="fa-solid fa-circle-check"></i> تم التخزين
                                            </span>
                                        @else
                                            <span class="text-gray-400">قيد التخزين</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($orderReturn->status !== 'completed')
                    <div class="mt-6 p-4 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4 print:hidden">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-800">
                            <input type="checkbox" name="restock" value="1" checked class="w-4 h-4 text-[#8A704C] rounded border-gray-300 focus:ring-[#8A704C]">
                            <span>إعادة إضافة القطع السليمة تلقائياً إلى مخزون المنتجات التامة (Finished Goods)</span>
                        </label>

                        <button type="submit" class="bg-[#8A704C] hover:bg-[#735d3f] text-white px-6 py-2.5 rounded-xl font-bold transition shadow-md flex items-center gap-2 text-xs">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>اعتماد نتيجة الفحص والتخزين</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>

        @if ($orderReturn->notes)
            <div class="bg-amber-50/70 border border-amber-200/60 p-4 rounded-xl text-amber-900 text-xs">
                <span class="font-bold">ملاحظات إضافية:</span> {{ $orderReturn->notes }}
            </div>
        @endif

    </div>

    <!-- Modal: Confirm Refund to Customer -->
    <div x-show="isRefundModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 print:hidden">
        <div @click.away="isRefundModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-base font-bold text-gray-800">تأكيد استرداد المبلغ للعميل</h3>
                <button @click="isRefundModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('returns.refund', $orderReturn->id) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-emerald-50 p-4 rounded-xl text-center">
                    <p class="text-xs text-emerald-700 font-bold">المبلغ المسترد للعميل:</p>
                    <p class="text-2xl font-black text-emerald-800 mt-1">{{ number_format($orderReturn->refund_amount, 2) }} ج.م</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة / إجراء الاسترداد</label>
                    <select name="action_taken" class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <option value="استرجاع نقدي (فودافون كاش / إنستاباي)">استرجاع نقدي (فودافون كاش / إنستاباي)</option>
                        <option value="رصيد دائن للعميل لطلب قادم">رصيد دائن للعميل لطلب قادم</option>
                        <option value="استبدال بموديل / مقاس آخر">استبدال بموديل / مقاس آخر</option>
                        <option value="استرجاع كاش مع مندوب الشحن">استرجاع كاش مع مندوب الشحن</option>
                    </select>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="isRefundModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> تأكيد الاسترداد
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
