@extends('layouts.app')

@section('content')
    <div class="p-4 md:p-8 space-y-8 bg-gray-50/50 min-h-screen" x-data="{ activeTab: 'all' }">

        {{-- الهيدر وأزرار الإجراءات --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">مركز الماليات</h1>
                <p class="text-sm text-gray-500 mt-1">متابعة المقدمات، توريدات شركات الشحن، والدفعات المتوقعة</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('payments.export', request()->all()) }}"
                    class="flex-1 md:flex-none justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm flex items-center gap-2 transform hover:-translate-y-0.5 active:scale-95 transition-all duration-200 shadow-sm hover:shadow-emerald-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    تصدير إكسيل
                </a>
                <a href="{{ route('payments.company-supply.create') }}"
                    class="flex-1 md:flex-none justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm flex items-center gap-2 transform hover:-translate-y-0.5 active:scale-95 transition-all duration-200 shadow-sm hover:shadow-indigo-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    إضافة توريد شركة شحن
                </a>
            </div>
        </div>

        {{-- شريط الفلاتر السريعة والتواريخ --}}
        <div
            class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col xl:flex-row xl:items-center justify-between gap-6 transition-all">
            <form method="GET" action="{{ route('payments.index') }}"
                class="flex flex-wrap items-end gap-4 w-full xl:w-auto">
                <div class="flex flex-col gap-1.5 flex-1 min-w-[140px]">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">من تاريخ</label>
                    <input type="date" name="from" value="{{ $financialData['from'] }}"
                        class="w-full text-sm border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all bg-gray-50 hover:bg-white cursor-pointer">
                </div>
                <div class="flex flex-col gap-1.5 flex-1 min-w-[140px]">
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $financialData['to'] }}"
                        class="w-full text-sm border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all bg-gray-50 hover:bg-white cursor-pointer">
                </div>
                <button type="submit"
                    class="px-6 py-2.5 h-[42px] bg-gray-900 text-white font-medium rounded-xl text-sm hover:bg-gray-800 active:scale-95 transition-all duration-200 shadow-sm">
                    تطبيق الفلتر
                </button>
            </form>

            {{-- أزرار الوصول السريع للتاريخ --}}
            <div
                class="flex flex-wrap items-center gap-1.5 bg-gray-50/80 p-1.5 rounded-xl text-sm font-medium w-full xl:w-auto overflow-x-auto hide-scrollbar">
                @php
                    $quickLinks = [
                        ['label' => 'اليوم', 'from' => now()->toDateString(), 'to' => now()->toDateString()],
                        [
                            'label' => 'أمس',
                            'from' => now()->subDay()->toDateString(),
                            'to' => now()->subDay()->toDateString(),
                        ],
                        [
                            'label' => 'هذا الأسبوع',
                            'from' => now()->startOfWeek()->toDateString(),
                            'to' => now()->endOfWeek()->toDateString(),
                        ],
                        [
                            'label' => 'هذا الشهر',
                            'from' => now()->startOfMonth()->toDateString(),
                            'to' => now()->endOfMonth()->toDateString(),
                        ],
                    ];
                @endphp
                @foreach ($quickLinks as $link)
                    @php
                        $isActive =
                            request('from') == $link['from'] &&
                            (request('to') == $link['to'] || (!request('to') && $link['label'] == 'أمس'));
                    @endphp
                    <a href="{{ route('payments.index', ['from' => $link['from'], 'to' => $link['to']]) }}"
                        class="px-4 py-2 rounded-lg transition-all duration-200 whitespace-nowrap {{ $isActive ? 'bg-white shadow-sm text-indigo-700 font-bold ring-1 ring-gray-200/50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/50' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- بطاقات الملخص المالي --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- المقدمات --}}
            <div @click="activeTab = 'advances'"
                :class="activeTab === 'advances' ? 'ring-2 ring-blue-500 scale-[1.02] shadow-md bg-blue-50/10' :
                    'border-gray-100 hover:-translate-y-1 hover:shadow-md'"
                class="bg-white p-6 rounded-2xl shadow-sm border cursor-pointer transition-all duration-300 relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-2 h-full bg-blue-500 rounded-r-2xl opacity-0 group-hover:opacity-100 transition-opacity">
                </div>
                <div class="flex items-center justify-between">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-blue-700 bg-blue-100/80 px-3 py-1.5 rounded-full">المقدمات
                        المقبوضة</span>
                    <span
                        class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md">{{ $financialData['advances']['count'] }}
                        عملية</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-gray-900 tracking-tight">
                        {{ number_format($financialData['advances']['total'], 2) }} <span
                            class="text-lg font-medium text-gray-500">ج.م</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">إجمالي العربون المستلم على الطلبات</p>
                </div>
            </div>

            {{-- توريدات الشحن --}}
            <div @click="activeTab = 'supplies'"
                :class="activeTab === 'supplies' ? 'ring-2 ring-indigo-500 scale-[1.02] shadow-md bg-indigo-50/10' :
                    'border-gray-100 hover:-translate-y-1 hover:shadow-md'"
                class="bg-white p-6 rounded-2xl shadow-sm border cursor-pointer transition-all duration-300 relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-2 h-full bg-indigo-500 rounded-r-2xl opacity-0 group-hover:opacity-100 transition-opacity">
                </div>
                <div class="flex items-center justify-between">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-indigo-700 bg-indigo-100/80 px-3 py-1.5 rounded-full">توريدات
                        شركات الشحن</span>
                    <span
                        class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md">{{ $financialData['supplies']['count'] }}
                        عملية</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-gray-900 tracking-tight">
                        {{ number_format($financialData['supplies']['total'], 2) }} <span
                            class="text-lg font-medium text-gray-500">ج.م</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">المبالغ الموردة من شركة الشحن</p>
                </div>
            </div>

            {{-- المتوقع --}}
            <div @click="activeTab = 'expected'"
                :class="activeTab === 'expected' ? 'ring-2 ring-amber-500 scale-[1.02] shadow-md bg-amber-50/10' :
                    'border-gray-100 hover:-translate-y-1 hover:shadow-md'"
                class="bg-white p-6 rounded-2xl shadow-sm border cursor-pointer transition-all duration-300 relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-2 h-full bg-amber-500 rounded-r-2xl opacity-0 group-hover:opacity-100 transition-opacity">
                </div>
                <div class="flex items-center justify-between">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-amber-700 bg-amber-100/80 px-3 py-1.5 rounded-full">المتوقع
                        تحصيله</span>
                    <span
                        class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md">{{ $financialData['expected']['count'] }}
                        طلب</span>
                </div>
                <div class="mt-6">
                    <div class="text-3xl font-black text-amber-600 tracking-tight">
                        {{ number_format($financialData['expected']['total'], 2) }} <span
                            class="text-lg font-medium text-amber-500/70">ج.م</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">باقي المستحق للطلبات غير المشحونة</p>
                </div>
            </div>
        </div>

        {{-- منطقة عرض البيانات (الجدول / الكروت) --}}
        <div class="bg-transparent lg:bg-white lg:rounded-2xl lg:shadow-sm lg:border lg:border-gray-100">

            {{-- هيدر التبويبات --}}
            <div
                class="border-b border-gray-100 bg-white lg:bg-gray-50/30 px-2 sm:px-6 flex overflow-x-auto hide-scrollbar rounded-t-2xl mb-4 lg:mb-0 shadow-sm lg:shadow-none">
                <button @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'text-indigo-600 border-indigo-600' :
                        'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-4 text-sm font-bold border-b-2 transition-all whitespace-nowrap">جميع الحركة
                    المالية</button>
                <button @click="activeTab = 'advances'"
                    :class="activeTab === 'advances' ? 'text-blue-600 border-blue-600' :
                        'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-4 text-sm font-bold border-b-2 transition-all whitespace-nowrap">المقدمات فقط</button>
                <button @click="activeTab = 'supplies'"
                    :class="activeTab === 'supplies' ? 'text-indigo-600 border-indigo-600' :
                        'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-4 text-sm font-bold border-b-2 transition-all whitespace-nowrap">التوريدات فقط</button>
                <button @click="activeTab = 'expected'"
                    :class="activeTab === 'expected' ? 'text-amber-600 border-amber-600' :
                        'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-4 text-sm font-bold border-b-2 transition-all whitespace-nowrap">المتوقع تحصيله</button>
            </div>

            {{-- قائمة المدفوعات المتجاوبة --}}
            <div x-show="activeTab !== 'expected'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0" class="w-full">

                {{-- استخدام Table ولكن يتم تحويله لـ Blocks على الموبايل --}}
                <table class="w-full text-right text-sm block lg:table">

                    {{-- هيدر الجدول (يظهر فقط على الديسكتوب) --}}
                    <thead
                        class="hidden lg:table-header-group bg-gray-50/80 text-gray-500 font-semibold text-xs uppercase tracking-wider">
                        <tr>
                            <th class="py-4 px-6">التاريخ</th>
                            <th class="py-4 px-6">نوع العملية</th>
                            <th class="py-4 px-6">المبلغ</th>
                            <th class="py-4 px-6">الطلب / التفاصيل</th>
                            <th class="py-4 px-6">المسؤول</th>
                            <th class="py-4 px-6">ملاحظات</th>
                        </tr>
                    </thead>

                    {{-- جسم الجدول (يتحول لكروت على الموبايل) --}}
                    <tbody class="block lg:table-row-group space-y-4 lg:space-y-0 lg:divide-y lg:divide-gray-100">
                        @forelse($financialData['payments'] as $payment)
                            <tr x-show="activeTab === 'all' || (activeTab === 'advances' && '{{ $payment->type }}' === 'order_advance') || (activeTab === 'supplies' && '{{ $payment->type }}' === 'company_supply')"
                                x-transition
                                class="block lg:table-row bg-white border border-gray-200 lg:border-0 rounded-2xl lg:rounded-none shadow-sm lg:shadow-none hover:bg-gray-50/80 transition-colors group overflow-hidden">

                                {{-- التاريخ --}}
                                <td
                                    class="flex justify-between items-center lg:table-cell py-3 px-5 lg:py-4 lg:px-6 border-b border-gray-100 border-dashed lg:border-0 text-gray-600 font-medium">
                                    <span
                                        class="lg:hidden text-[11px] font-bold text-gray-400 uppercase tracking-wider">التاريخ</span>
                                    <span>{{ $payment->payment_date->format('Y-m-d') }}</span>
                                </td>

                                {{-- نوع العملية --}}
                                <td
                                    class="flex justify-between items-center lg:table-cell py-3 px-5 lg:py-4 lg:px-6 border-b border-gray-100 border-dashed lg:border-0">
                                    <span
                                        class="lg:hidden text-[11px] font-bold text-gray-400 uppercase tracking-wider">نوع
                                        العملية</span>
                                    @if ($payment->isOrderAdvance())
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 text-xs rounded-full font-bold border border-blue-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            مقدم طلب
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs rounded-full font-bold border border-indigo-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            توريد شحن
                                        </span>
                                    @endif
                                </td>

                                {{-- المبلغ (تصميم بارز للموبايل) --}}
                                <td
                                    class="flex justify-between items-center lg:table-cell py-3 px-5 lg:py-4 lg:px-6 border-b border-gray-100 border-dashed lg:border-0 bg-gray-50/50 lg:bg-transparent">
                                    <span
                                        class="lg:hidden text-[11px] font-bold text-gray-400 uppercase tracking-wider">المبلغ</span>
                                    <div class="font-black text-gray-900 text-lg lg:text-base">
                                        {{ number_format($payment->amount, 2) }} <span
                                            class="text-xs text-gray-500 font-medium">ج.م</span>
                                    </div>
                                </td>

                                {{-- الطلب / التفاصيل --}}
                                <td
                                    class="flex flex-col lg:table-cell py-3 px-5 lg:py-4 lg:px-6 border-b border-gray-100 border-dashed lg:border-0 gap-1 lg:gap-0">
                                    <span
                                        class="lg:hidden text-[11px] font-bold text-gray-400 uppercase tracking-wider">الطلب
                                        / التفاصيل</span>
                                    @if ($payment->order)
                                        <div class="flex flex-col lg:items-start items-end text-left lg:text-right">
                                            <a href="{{ route('orders.show', $payment->order_id) }}"
                                                class="text-indigo-600 font-bold hover:text-indigo-800 hover:underline transition">#{{ $payment->order->order_number }}</a>
                                            <span
                                                class="text-xs text-gray-500 mt-0.5">{{ $payment->order->customer?->name }}</span>
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex self-end lg:self-auto items-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-500 font-mono text-xs">توريد
                                            شركة الشحن</span>
                                    @endif
                                </td>

                                {{-- المسؤول --}}
                                <td
                                    class="flex justify-between items-center lg:table-cell py-3 px-5 lg:py-4 lg:px-6 border-b border-gray-100 border-dashed lg:border-0 text-gray-600">
                                    <span
                                        class="lg:hidden text-[11px] font-bold text-gray-400 uppercase tracking-wider">المسؤول</span>
                                    <div class="flex items-center gap-2 justify-end">
                                        <div
                                            class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                            {{ mb_substr($payment->creator?->name ?? 'ن', 0, 1) }}
                                        </div>
                                        <span class="text-sm">{{ $payment->creator?->name ?? 'النظام' }}</span>
                                    </div>
                                </td>

                                {{-- ملاحظات --}}
                                <td
                                    class="flex flex-col lg:table-cell py-3 px-5 lg:py-4 lg:px-6 lg:border-0 gap-1 lg:gap-0">
                                    <span
                                        class="lg:hidden text-[11px] font-bold text-gray-400 uppercase tracking-wider">ملاحظات</span>
                                    <span
                                        class="text-gray-500 text-sm max-w-[200px] truncate block text-left lg:text-right"
                                        title="{{ $payment->note }}">
                                        {{ $payment->note ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr class="block lg:table-row bg-white rounded-2xl lg:rounded-none shadow-sm lg:shadow-none">
                                <td colspan="6" class="block lg:table-cell py-12 lg:py-16 text-center border-0">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div
                                            class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-2">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <h3 class="text-base font-semibold text-gray-900">لا توجد حركات مالية</h3>
                                        <p class="text-sm text-gray-500">لم يتم تسجيل أي عمليات خلال الفترة المحددة أعلاه.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- رسالة تبويب "المتوقع تحصيله" --}}
            <div x-show="activeTab === 'expected'" x-cloak class="p-12 text-center text-gray-500 bg-white rounded-b-2xl"
                x-transition>
                يتم حساب المتوقع تلقائياً بناءً على الطلبات الجاري شحنها.
            </div>
        </div>
    </div>

    <style>
        /* إخفاء شريط التمرير الأفقي مع الاحتفاظ بوظيفته للتبويبات */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
