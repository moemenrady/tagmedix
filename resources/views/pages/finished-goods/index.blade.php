@extends('layouts.app')

@section('title', 'المنتجات التامة')
@section('page_title', 'إدارة المنتجات التامة والشحن')

@section('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <div dir="rtl" class="-mt-4 md:-mt-6 space-y-4 pb-8 font-sans" x-data="finishedGoodsApp()">

        {{-- Dynamic Response Alerts & Error Cards System --}}
        <div class="space-y-3">
            {{-- Success Notification Card --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                    class="bg-emerald-50 border-r-4 border-emerald-500 p-4 rounded-2xl flex items-center justify-between shadow-xs border border-emerald-100">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <i class="fa-solid fa-circle-check text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-emerald-900 text-sm">عملية ناجحة</h4>
                            <p class="text-emerald-700 text-xs mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-emerald-400 hover:text-emerald-700 transition p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            @endif

            {{-- System Error Card --}}
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                    class="bg-rose-50 border-r-4 border-rose-500 p-4 rounded-2xl flex items-center justify-between shadow-xs border border-rose-100">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-rose-900 text-sm">حدث خطأ</h4>
                            <p class="text-rose-700 text-xs mt-0.5">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button @click="show = false" class="text-rose-400 hover:text-rose-700 transition p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            @endif

            {{-- Form Validation Errors (Mobile & Desktop Card) --}}
            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
                    class="bg-rose-50 border-r-4 border-rose-500 p-4 rounded-2xl shadow-xs border border-rose-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-rose-900 text-sm">يرجى تصحيح الأخطاء التالية:</h4>
                                <ul class="mt-2 space-y-1 text-xs text-rose-700 list-disc list-inside font-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button @click="show = false" class="text-rose-400 hover:text-rose-700 transition p-1">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-5">
            <div
                class="bg-white p-5 md:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-xs md:text-sm font-medium mb-1">إجمالي الطلبات التامة</p>
                    <h3 class="text-2xl md:text-3xl font-black text-gray-800">
                        {{ number_format($stats['total_orders'] ?? 0) }}</h3>
                </div>
                <div
                    class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-box-open text-xl md:text-2xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-5 md:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-xs md:text-sm font-medium mb-1">إجمالي قيمة المنتجات التامة</p>
                    <h3 class="text-xl md:text-2xl font-black text-gray-800">
                        {{ number_format($stats['total_value'] ?? 0, 0) }}
                        <span class="text-xs text-gray-400 font-normal">ج.م</span>
                    </h3>
                </div>
                <div
                    class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-wallet text-xl md:text-2xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-5 md:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between group sm:col-span-2 md:col-span-1">
                <div>
                    <p class="text-gray-500 text-xs md:text-sm font-medium mb-1">المتبقي للتحصيل عند الشحن</p>
                    <h3 class="text-xl md:text-2xl font-black text-gray-800">
                        {{ number_format($stats['total_remaining'] ?? 0, 0) }}
                        <span class="text-xs text-gray-400 font-normal">ج.م</span>
                    </h3>
                </div>
                <div
                    class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-hand-holding-dollar text-xl md:text-2xl"></i>
                </div>
            </div>
        </div>

        {{-- Filter & Search Header --}}
        <div
            class="bg-white p-4 md:p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-boxes-packing text-lg"></i>
                </div>
                <div>
                    <h2 class="text-base md:text-lg font-bold text-gray-800">الطلبات التامة الجاهزة للشحن</h2>
                    <p class="text-xs text-gray-400">قائمة بالطلبات المكتملة للتسليم أو الإرسال عبر شركة Turbo</p>
                </div>
            </div>

            <form method="GET" action="{{ route('finished-goods.index') }}"
                class="w-full md:w-80 flex items-center gap-2">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="ابحث برقم الطلب، العميل، أو الهاتف..."
                        class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs md:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                    <div class="absolute right-3 top-3 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                </div>
                @if (!empty($search))
                    <a href="{{ route('finished-goods.index') }}"
                        class="p-2.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition shrink-0"
                        title="إلغاء البحث">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- Mobile View: Responsive Orders Cards (شاشة الموبايل) --}}
        <div class="space-y-2 md:hidden">
            @if ($orders->count() > 0)
                <div class="flex items-center justify-between bg-white px-3.5 py-2 rounded-xl border border-gray-100 shadow-xs">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" :checked="isAllSelected()" @change="toggleSelectAll()"
                            class="w-4 h-4 text-amber-600 bg-gray-50 border-gray-300 rounded focus:ring-amber-500 cursor-pointer">
                        <span class="text-xs font-bold text-gray-700">تحديد جميع الطلبات</span>
                    </label>
                    <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md" x-show="selectedOrderIds.length > 0" x-text="selectedOrderIds.length + ' محدد'"></span>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-2">
                @forelse ($orders as $order)
                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex flex-col gap-2 transition-colors"
                        :class="{'ring-2 ring-amber-500/40 bg-amber-50/20': isOrderSelected({{ $order->id }})}">
                        
                        {{-- رأس الكارت --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" :checked="isOrderSelected({{ $order->id }})" @change="toggleOrderSelection({{ $order->id }})"
                                    class="w-4 h-4 text-amber-600 bg-gray-50 border-gray-300 rounded focus:ring-amber-500 cursor-pointer">
                                <div class="flex items-center gap-1 font-black text-gray-900 text-sm">
                                    <span class="text-gray-400">#</span>{{ $order->order_number }}
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold rounded-md">
                                <i class="fa-solid fa-circle text-[5px] text-amber-500 animate-pulse"></i> منتج تام
                            </span>
                        </div>

                        {{-- العميل والعنوان --}}
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <a href="{{ route('customers.index', ['q' => $order->customer?->phone_1 ?? $order->phone_1 ?? $order->customer?->name ?? $order->customer_name]) }}" class="font-bold text-sm text-gray-800 hover:text-amber-600 hover:underline transition-colors" title="بحث عن العميل">{{ $order->customer?->name ?? $order->customer_name }}</a>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] text-gray-500 font-mono" dir="ltr">{{ $order->customer?->phone_1 ?? $order->phone_1 }}</span>
                                    <span class="text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded"><i class="fa-solid fa-location-dot"></i> {{ $order->address?->governorate ?? $order->governorate ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                            @php $phone = $order->customer?->phone_1 ?? $order->phone_1; @endphp
                            @if ($phone)
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="tel:{{ $phone }}" class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs active:scale-90 transition">
                                        <i class="fa-solid fa-phone"></i>
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" class="w-7 h-7 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-[13px] active:scale-90 transition">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- المالية والإجراءات --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <div>
                                <span class="text-sm font-black text-gray-900">{{ number_format($order->total_price, 0) }} <span class="text-[10px] text-gray-500">ج.م</span></span>
                                @if ($order->getRemainingAmount() > 0)
                                    <span class="text-[10px] text-amber-700 font-bold mr-1">متبقي: {{ number_format($order->getRemainingAmount(), 0) }}</span>
                                @endif
                            </div>
                            <div class="flex gap-1.5">
                                <button type="button" @click="openDetailsModal({{ json_encode($order) }})" class="px-2.5 py-1.5 bg-gray-100 rounded-md text-gray-700 active:bg-gray-200 transition text-[10px] font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-eye"></i> التفاصيل
                                </button>
                                <button type="button" @click="openShipModal({{ json_encode($order) }})" class="px-2.5 py-1.5 bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded-md active:opacity-80 transition text-[10px] font-bold flex items-center gap-1">
                                    <i class="fa-solid fa-truck-fast"></i> شحن
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white p-8 rounded-xl border border-gray-100 text-center text-gray-400">
                        <i class="fa-solid fa-box-open text-3xl mb-2 text-gray-300"></i>
                        <p class="text-sm font-semibold text-gray-600">لا توجد طلبات تامة جاهزة للشحن حالياً</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Desktop View: Orders Table Card (شاشة الديسكتوب) --}}
        <div class="hidden md:block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-right border-collapse min-w-[900px]">
                    <thead>
                        <tr
                            class="bg-gray-50/80 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                            <th class="py-4 px-4 w-12 text-center">
                                <input type="checkbox" :checked="isAllSelected()" @change="toggleSelectAll()" title="تحديد جميع الطلبات"
                                    class="w-4 h-4 text-amber-600 bg-white border-gray-300 rounded focus:ring-amber-500 cursor-pointer transition">
                            </th>
                            <th class="py-4 px-6">رقم الطلب</th>
                            <th class="py-4 px-6">العميل والهاتف</th>
                            <th class="py-4 px-6">المحافظة والعنوان</th>
                            <th class="py-4 px-6">الإجمالي / المتبقي</th>
                            <th class="py-4 px-6 text-center">الحالة</th>
                            <th class="py-4 px-6 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-amber-50/30 transition duration-150"
                                :class="{'bg-amber-50/40': isOrderSelected({{ $order->id }})}">
                                {{-- Checkbox --}}
                                <td class="py-4 px-4 text-center">
                                    <input type="checkbox" :checked="isOrderSelected({{ $order->id }})" @change="toggleOrderSelection({{ $order->id }})"
                                        class="w-4 h-4 text-amber-600 bg-white border-gray-300 rounded focus:ring-amber-500 cursor-pointer transition">
                                </td>

                                {{-- Order Number --}}
                                <td class="py-4 px-6 font-bold text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        <span>{{ $order->order_number }}</span>
                                    </div>
                                    <span class="text-[11px] text-gray-400 block mt-0.5 font-normal">
                                        {{ $order->created_at ? $order->created_at->format('Y-m-d h:i A') : '' }}
                                    </span>
                                </td>

                                {{-- Customer Info --}}
                                <td class="py-4 px-6">
                                    <a href="{{ route('customers.index', ['q' => $order->customer?->phone_1 ?? $order->phone_1 ?? $order->customer?->name ?? $order->customer_name]) }}" class="font-semibold text-gray-800 hover:text-amber-600 hover:underline transition-colors block" title="بحث عن العميل">
                                        {{ $order->customer?->name ?? $order->customer_name }}
                                    </a>
                                    <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                        <span><i
                                                class="fa-solid fa-phone text-gray-400 ml-1"></i>{{ $order->customer?->phone_1 ?? $order->phone_1 }}</span>
                                        @if ($order->customer?->phone_2 || $order->phone_2)
                                            <span class="text-gray-300">|</span>
                                            <span>{{ $order->customer?->phone_2 ?? $order->phone_2 }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Address --}}
                                <td class="py-4 px-6 max-w-xs">
                                    <span
                                        class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-medium mb-1">
                                        {{ $order->address?->governorate ?? $order->governorate }}
                                    </span>
                                    <p class="text-xs text-gray-500 truncate"
                                        title="{{ $order->address?->full_address ?? $order->full_address }}">
                                        {{ $order->address?->full_address ?? $order->full_address }}
                                    </p>
                                </td>

                                {{-- Pricing --}}
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-800">
                                        {{ number_format($order->total_price, 0) }} <span
                                            class="text-xs text-gray-400 font-normal">ج.م</span>
                                    </div>
                                    <div class="text-xs text-amber-700 font-medium mt-0.5">
                                        المتبقي: {{ number_format($order->getRemainingAmount(), 0) }} ج.م
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-6 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-semibold rounded-full">
                                        <i class="fa-solid fa-circle text-[8px] text-amber-500 animate-pulse"></i>
                                        منتج تام
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openDetailsModal({{ json_encode($order) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-xl transition shadow-xs">
                                            <i class="fa-solid fa-eye text-gray-500"></i>
                                            <span>عرض التفاصيل</span>
                                        </button>

                                        <button type="button" @click="openShipModal({{ json_encode($order) }})"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white text-xs font-bold rounded-xl transition shadow-sm hover:shadow-md">
                                            <i class="fa-solid fa-truck-fast"></i>
                                            <span>شحن الطلب</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div
                                        class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3 text-gray-300">
                                        <i class="fa-solid fa-box-open text-2xl"></i>
                                    </div>
                                    <p class="text-base font-semibold text-gray-600">لا توجد طلبات تامة جاهزة للشحن حالياً
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">ستظهر الطلبات هنا فور اكتمال مرحلة التشطيب
                                        والإنتاج</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL 1: Details Modal (Responsive Card Design) --}}
        <div x-show="showDetails" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-2 sm:p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="showDetails = false"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-2xl overflow-hidden transform transition-all my-auto max-h-[92vh] flex flex-col">

                {{-- Modal Header --}}
                <div class="px-5 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold shrink-0">
                            <i class="fa-solid fa-file-invoice text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm sm:text-base"
                                x-text="'تفاصيل الطلب: ' + (activeOrder ? activeOrder.order_number : '')"></h3>
                            <p class="text-xs text-gray-400">معلومات العميل والمنتجات التامة بالطلب</p>
                        </div>
                    </div>
                    <button @click="showDetails = false"
                        class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-4 sm:p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1">

                    {{-- Customer Details Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">اسم العميل</p>
                            <p class="font-bold text-gray-800 text-xs sm:text-sm"
                                x-text="activeOrder ? (activeOrder.customer ? activeOrder.customer.name : (activeOrder.customer_name || '')) : ''">
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">أرقام التواصل</p>
                            <p class="font-medium text-gray-700 text-xs"
                                x-text="activeOrder ? ((activeOrder.customer ? activeOrder.customer.phone_1 : activeOrder.phone_1) + ((activeOrder.customer ? activeOrder.customer.phone_2 : activeOrder.phone_2) ? ' / ' + (activeOrder.customer ? activeOrder.customer.phone_2 : activeOrder.phone_2) : '')) : ''">
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">المحافظة</p>
                            <p class="font-semibold text-gray-800 text-xs"
                                x-text="activeOrder ? (activeOrder.address ? activeOrder.address.governorate : (activeOrder.governorate || '')) : ''">
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">العنوان التفصيلي</p>
                            <p class="font-normal text-gray-700 text-xs"
                                x-text="activeOrder ? (activeOrder.address ? activeOrder.address.full_address : (activeOrder.full_address || '')) : ''">
                            </p>
                        </div>
                    </div>

                    {{-- Items List (Mobile Cards + Desktop Table) --}}
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                            <span>المنتجات بالطلب</span>
                        </h4>

                        {{-- Mobile Items Cards System --}}
                        <div class="space-y-2 sm:hidden">
                            <template x-if="activeOrder && activeOrder.items && activeOrder.items.length">
                                <template x-for="item in activeOrder.items" :key="item.id">
                                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs space-y-1">
                                        <div class="flex justify-between font-bold text-gray-800">
                                            <span x-text="item.style ? item.style.name : 'منتج'"></span>
                                            <span class="text-amber-700" x-text="item.total + ' ج.م'"></span>
                                        </div>
                                        <div class="text-gray-500 text-[11px]">
                                            <span x-text="(item.type ? item.type.name : '')"></span>
                                            <span x-show="item.color"
                                                x-text="' | ' + (item.color ? item.color.name : '')"></span>
                                            <span x-show="item.size"
                                                x-text="' | ' + (item.size ? item.size.name : '')"></span>
                                        </div>
                                        <div class="flex justify-between text-gray-400 pt-1 text-[11px]">
                                            <span>الكمية: <strong class="text-gray-700"
                                                    x-text="item.quantity"></strong></span>
                                            <span>السعر: <strong class="text-gray-700"
                                                    x-text="item.price + ' ج.م'"></strong></span>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>

                        {{-- Desktop Table --}}
                        <div class="hidden sm:block border border-gray-100 rounded-xl overflow-hidden">
                            <table class="w-full text-right text-xs">
                                <thead class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-100">
                                    <tr>
                                        <th class="py-2.5 px-4">الصنف / الموديل</th>
                                        <th class="py-2.5 px-4">النوع / اللون / المقاس</th>
                                        <th class="py-2.5 px-4 text-center">الكمية</th>
                                        <th class="py-2.5 px-4 text-left">السعر</th>
                                        <th class="py-2.5 px-4 text-left">الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-if="activeOrder && activeOrder.items && activeOrder.items.length">
                                        <template x-for="item in activeOrder.items" :key="item.id">
                                            <tr>
                                                <td class="py-3 px-4 font-medium text-gray-800"
                                                    x-text="item.style ? item.style.name : 'منتج'"></td>
                                                <td class="py-3 px-4 text-gray-600">
                                                    <span x-text="(item.type ? item.type.name : '')"></span>
                                                    <span x-show="item.color"
                                                        x-text="' | ' + (item.color ? item.color.name : '')"></span>
                                                    <span x-show="item.size"
                                                        x-text="' | ' + (item.size ? item.size.name : '')"></span>
                                                </td>
                                                <td class="py-3 px-4 text-center font-bold text-gray-800"
                                                    x-text="item.quantity"></td>
                                                <td class="py-3 px-4 text-left text-gray-600"
                                                    x-text="item.price + ' ج.م'"></td>
                                                <td class="py-3 px-4 text-left font-bold text-gray-900"
                                                    x-text="item.total + ' ج.م'"></td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Financial Summary Card --}}
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between p-4 bg-amber-50/50 rounded-xl border border-amber-100 gap-3">
                        <div class="w-full sm:w-auto text-center sm:text-right">
                            <span class="text-xs text-gray-500 block">إجمالي سعر الطلب</span>
                            <span class="text-base sm:text-lg font-black text-gray-800"
                                x-text="activeOrder ? activeOrder.total_price + ' ج.م' : ''"></span>
                        </div>
                        <div
                            class="w-full sm:w-auto text-center sm:text-left border-t sm:border-t-0 pt-2 sm:pt-0 border-amber-200/50">
                            <span class="text-xs text-gray-500 block">المبلغ المتبقي للتحصيل</span>
                            <span class="text-base sm:text-lg font-black text-amber-700"
                                x-text="activeOrder ? calculateRemaining(activeOrder) + ' ج.م' : ''"></span>
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between shrink-0">
                    <button @click="showDetails = false"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-xl transition">
                        إغلاق
                    </button>
                    <button @click="showDetails = false; openShipModal(activeOrder)"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>الانتقال لشحن الطلب</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL 2: Ship Order Form Modal (Mobile Card Optimized) --}}
        <div x-show="showShip" x-cloak @keydown.escape.window="if (showShip) closeShipModal()"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-2 sm:p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="closeShipModal()"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden transform transition-all my-auto max-h-[94vh] flex flex-col">

                {{-- Modal Header --}}
                <div
                    class="px-5 py-4 bg-gradient-to-r from-amber-600 to-amber-700 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white shrink-0">
                            <i class="fa-solid fa-paper-plane text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base sm:text-lg"
                                x-text="'شحن الطلب (' + (shipForm.remote_order_id || '') + ')'"></h3>
                            <p class="text-[11px] sm:text-xs text-amber-100">استكمال بيانات الشحنة للتحويل لشركة الشحن</p>
                        </div>
                    </div>
                    <button @click="closeShipModal()" class="text-amber-100 hover:text-white p-1 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- Form Content --}}
                <form :action="'{{ url('finished-goods') }}/' + (activeOrder ? activeOrder.id : '') + '/ship'"
                    method="POST" @submit="submitShip($event)" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <input type="hidden" name="order_id" :value="activeOrder ? activeOrder.id : ''">

                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto custom-scrollbar flex-1">

                        {{-- خطأ الشحن التفاعلي داخل المودال --}}
                        <div x-show="modalError" x-cloak
                            class="bg-rose-50 border border-rose-200 p-3.5 rounded-xl flex items-start gap-3 text-rose-800 shadow-xs">
                            <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                                <i class="fa-solid fa-circle-exclamation text-base"></i>
                            </div>
                            <div class="text-xs flex-1">
                                <span class="font-bold block text-rose-900 mb-0.5">حدث خطأ من شركة الشحن Turbo:</span>
                                <span class="leading-relaxed" x-text="modalError"></span>
                            </div>
                            <button type="button" @click="modalError = null" class="text-rose-400 hover:text-rose-600 p-1 transition">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>

                        {{-- الراسل الفرعي --}}
                        <div
                            class="bg-gray-100 p-3 rounded-xl text-xs font-bold text-gray-700 flex items-center justify-between">
                            <span>الراسل الفرعي :</span>
                            <span class="text-amber-700 font-extrabold">Tagmedix</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">

                            {{-- المرسل اليه --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">المرسل اليه <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" name="receiver" x-model="shipForm.receiver" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- تيليفون المتابعه --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">تيليفون المتابعه</label>
                                <input type="text" name="api_followup_phone" x-model="shipForm.api_followup_phone"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- تلفيون المرسل اليه ١ --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">تلفيون المرسل اليه ١ <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" name="phone1" x-model="shipForm.phone1" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- تيليفون المرسل اليه ٢ --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">تيليفون المرسل اليه ٢</label>
                                <input type="text" name="phone2" x-model="shipForm.phone2"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- الموقع (المحافظة) --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">المحافظة <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" name="government" x-model="shipForm.government"
                                    placeholder="اسم المحافظة" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- المنطقة --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">المنطقة <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" name="area" x-model="shipForm.area"
                                    placeholder="اسم المنطقة أو الحي" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- تفاصيل التحصيل (Order + Shipping = Total) --}}
                            <div class="md:col-span-2 bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                                <h4 class="text-xs font-bold text-amber-800 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-calculator"></i> تفاصيل التحصيل المالي
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    {{-- Box 1: Order Remaining --}}
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 mb-1.5">المتبقي من الطلب</label>
                                        <div class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 flex justify-between items-center shadow-sm">
                                            <span x-text="shipForm.order_remaining"></span>
                                            <span class="text-[10px] text-gray-400">ج.م</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Box 2: Shipment Price (Calculates Total) --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label class="block text-[11px] font-semibold text-gray-700">تكلفة الشحن <span class="text-rose-500">*</span></label>
                                            <template x-if="shipForm.is_shipped_paid">
                                                <span class="text-[9px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i>
                                                    شحن مدفوع
                                                </span>
                                            </template>
                                            <template x-if="!shipForm.is_shipped_paid && shipForm.original_return_amount !== '' && shipForm.shipment_price == shipForm.original_return_amount">
                                                <span class="text-[9px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i>
                                                    تلقائي
                                                </span>
                                            </template>
                                        </div>
                                        <div class="relative">
                                            <input type="number" step="0.01" x-model="shipForm.shipment_price" @input="shipForm.return_amount = shipForm.shipment_price" placeholder="تكلفة الشحن" required
                                                class="w-full pl-20 pr-3 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold shadow-sm transition-all text-gray-700"
                                                :class="{'border-emerald-500 ring-1 ring-emerald-500 bg-emerald-50/20': shipForm.is_shipped_paid || (shipForm.original_return_amount !== '' && shipForm.shipment_price == shipForm.original_return_amount)}">
                                            <div class="absolute left-2 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                                <template x-if="!shipForm.is_shipped_paid && shipForm.original_return_amount !== '' && shipForm.shipment_price != shipForm.original_return_amount">
                                                    <button type="button" @click="shipForm.shipment_price = shipForm.original_return_amount; shipForm.return_amount = shipForm.original_return_amount" 
                                                        class="text-[9px] bg-gray-100 hover:bg-gray-200 text-gray-600 px-1.5 py-1 rounded font-bold transition flex items-center gap-1 shadow-sm"
                                                        title="العودة للسعر التلقائي">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                        التلقائي
                                                    </button>
                                                </template>
                                                <span class="text-[10px] text-gray-400 font-normal">ج.م</span>
                                            </div>
                                        </div>
                                        <label class="flex items-center gap-1.5 cursor-pointer mt-1.5 select-none">
                                            <input type="checkbox" x-model="shipForm.is_shipped_paid" @change="if(shipForm.is_shipped_paid){ shipForm.shipment_price = 0; } else { shipForm.shipment_price = (shipForm.original_return_amount !== '' ? shipForm.original_return_amount : 0); } shipForm.return_amount = shipForm.shipment_price;"
                                                class="w-3.5 h-3.5 rounded text-emerald-600 border-gray-300 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer">
                                            <span class="text-[10px] font-semibold text-gray-600 hover:text-gray-900">تم سداد الشحن مسبقاً (تكلفة الشحن = 0)</span>
                                        </label>
                                    </div>
                                    
                                    {{-- Box 3: Total Amount --}}
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1.5">المطلوب تحصيله شامل الشحن</label>
                                        <div class="px-3 py-2 bg-gradient-to-l from-amber-100 to-amber-50 border border-amber-200 rounded-xl text-xs font-black text-amber-900 flex justify-between items-center shadow-inner">
                                            <span x-text="(parseFloat(shipForm.order_remaining || 0) + parseFloat(shipForm.shipment_price || 0)).toFixed(2)"></span>
                                            <span class="text-[10px] text-amber-700 font-normal">ج.م</span>
                                        </div>
                                        {{-- Hidden input to send with form --}}
                                        <input type="hidden" name="amount_to_be_collected" :value="parseFloat(shipForm.order_remaining || 0) + parseFloat(shipForm.shipment_price || 0)">
                                        <input type="hidden" name="is_shipped_paid" :value="shipForm.is_shipped_paid ? 1 : 0">
                                    </div>

                                    {{-- Box 4: Return Amount (Sent to API) --}}
                                    <div class="relative">
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1.5">في حالة الارتجاع يدفع العميل <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input type="number" step="0.01" name="return_amount" x-model="shipForm.return_amount" placeholder="سعر الارتجاع" required
                                                class="w-full pl-8 pr-3 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-bold shadow-sm transition-all text-rose-700">
                                            <div class="absolute left-3 top-1/2 -translate-y-1/2">
                                                <span class="text-[10px] text-rose-400">ج.م</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- نوع الطلب --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">نوع الطلب <span
                                        class="text-rose-500">*</span></label>
                                <select name="is_order" x-model="shipForm.is_order" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                    <option value="0">تسليم طرد</option>
                                    <option value="1">مرتجع جزئي</option>
                                    <option value="2">استبدال</option>
                                    <option value="3">استرجاع (مرتجع كامل)</option>
                                </select>
                            </div>

                            {{-- محتوى الطرد المرتجع --}}
                            <div x-show="shipForm.is_order != '0'" x-cloak>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">محتوى الطرد المرتجع <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" name="return_summary" x-model="shipForm.return_summary"
                                    :required="shipForm.is_order != '0'" placeholder="وصف المرتجع"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- مكان التسليم --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">مكان التسليم <span
                                        class="text-rose-500">*</span></label>
                                <select name="delivery_type" x-model="shipForm.delivery_type" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                    <option value="0">توصيل إلى عنوان المستلم</option>
                                    <option value="1">استلام من مكتب الشحن</option>
                                </select>
                            </div>

                            {{-- السماح بفتح الشحنة --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">السماح بفتح الشحنة <span
                                        class="text-rose-500">*</span></label>
                                <select name="can_open" x-model="shipForm.can_open" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                    <option value="1">نعم، يسمح بالفتح</option>
                                    <option value="0">لا يسمح بالفتح</option>
                                </select>
                            </div>

                            {{-- محتوي الطرد --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">محتوي الطرد <span
                                        class="text-rose-500">*</span></label>
                                <input type="text" name="order_summary" x-model="shipForm.order_summary"
                                    placeholder="ملابس" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- عدد القطع --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">عدد القطع <span
                                        class="text-rose-500">*</span></label>
                                <input type="number" name="number_of_items" x-model="shipForm.number_of_items"
                                    min="1" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- الوزن (كجم) --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">الوزن (كجم) <span
                                        class="text-rose-500">*</span></label>
                                <input type="number" step="0.1" name="weight" x-model="shipForm.weight"
                                    min="0.1" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- قابل للكسر --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">قابل للكسر <span
                                        class="text-rose-500">*</span></label>
                                <select name="is_fragile" x-model="shipForm.is_fragile" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                    <option value="0">لا</option>
                                    <option value="1">نعم</option>
                                </select>
                            </div>

                            {{-- رقم الفاتوره --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">رقم الفاتوره</label>
                                <input type="text" name="invoice_number" x-model="shipForm.invoice_number"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- العنوان --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">العنوان <span
                                        class="text-rose-500">*</span></label>
                                <textarea name="address" x-model="shipForm.address" rows="2" placeholder="تفاصيل العنوان" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
                            </div>

                        </div>

                        <input type="hidden" name="remote_order_id" x-model="shipForm.remote_order_id">

                    </div>

                    {{-- Modal Footer --}}
                    <div
                        class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between shrink-0">
                        <button type="button" @click="closeShipModal()"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-xl transition">
                            إلغاء
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                            class="px-5 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white text-xs font-bold rounded-xl transition shadow-md flex items-center gap-2 disabled:opacity-50">
                            <template x-if="!isSubmitting">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>تأكيد الشحن</span>
                                </div>
                            </template>
                            <template x-if="isSubmitting">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                    <span>جاري الإرسال...</span>
                                </div>
                            </template>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 3: Shipment Success Modal (Order Added to Shipments) --}}
        <div x-show="showSuccessModal" x-cloak @keydown.escape.window="if (showSuccessModal) { showSuccessModal = false; window.location.reload(); }"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden transform transition-all my-auto flex flex-col">
                {{-- Header --}}
                <div class="p-5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white text-2xl shadow-inner">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-lg">تم شحن الطلب بنجاح!</h3>
                            <p class="text-xs text-emerald-100">تمت إضافة الطلب لشركة Turbo وجدول الشحنات</p>
                        </div>
                    </div>
                    <button type="button" @click="showSuccessModal = false; window.location.reload();"
                        class="text-emerald-100 hover:text-white p-1 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- Order & Shipment Information --}}
                <div class="p-5 space-y-4 text-xs">
                    <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-3.5 flex items-center justify-between">
                        <div>
                            <span class="text-gray-500 block text-[11px] font-semibold mb-0.5">رقم الطلب</span>
                            <span class="font-black text-sm text-gray-900 font-mono" x-text="'#' + (successData?.order_number || '')"></span>
                        </div>
                        <div class="text-left">
                            <span class="text-gray-500 block text-[11px] font-semibold mb-0.5">كود الشحنة (Turbo ID)</span>
                            <span class="font-black text-sm text-emerald-700 font-mono bg-white px-2.5 py-1 rounded-lg border border-emerald-200" x-text="successData?.turbo_id || 'تم التسجيل'"></span>
                        </div>
                    </div>

                    {{-- تفاصيل المستلم والشحنة --}}
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                        <div>
                            <span class="text-gray-400 block text-[10px] font-semibold">المستلم</span>
                            <span class="font-bold text-gray-800 text-xs truncate block mt-0.5" x-text="successData?.receiver"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px] font-semibold">رقم الهاتف</span>
                            <span class="font-bold text-gray-700 text-xs font-mono block dir-ltr text-right mt-0.5" x-text="successData?.phone"></span>
                        </div>
                        <div class="col-span-2 pt-2.5 border-t border-gray-200/60">
                            <span class="text-gray-400 block text-[10px] font-semibold">المحافظة والعنوان</span>
                            <span class="font-semibold text-gray-700 text-xs block leading-relaxed mt-0.5" x-text="(successData?.government || '') + ' - ' + (successData?.address || '')"></span>
                        </div>
                        <div class="col-span-2 pt-2.5 border-t border-gray-200/60 flex items-center justify-between">
                            <span class="font-bold text-gray-600">المطلوب تحصيله شامل الشحن:</span>
                            <span class="font-black text-amber-700 text-sm font-mono"><span x-text="successData?.amount"></span> ج.م</span>
                        </div>
                        <template x-if="successData?.tracking_number">
                            <div class="col-span-2 pt-2 border-t border-gray-200/60 flex items-center justify-between text-[11px]">
                                <span class="text-gray-500">باركود التتبع:</span>
                                <span class="font-mono font-bold text-gray-800" x-text="successData.tracking_number"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex flex-col sm:flex-row items-center gap-2.5 justify-end">
                    <a :href="successData?.shipment_url"
                        class="w-full sm:w-auto px-4 py-2.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>عرض في قائمة الشحنات</span>
                    </a>
                    <a :href="successData?.order_url"
                        class="w-full sm:w-auto px-4 py-2.5 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>صفحة الطلب</span>
                    </a>
                    <button type="button" @click="showSuccessModal = false; window.location.reload();"
                        class="w-full sm:w-auto px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-xl transition">
                        إغلاق ومتابعة
                    </button>
                </div>
            </div>
        </div>

        {{-- FLOATING BULK ACTIONS BAR (شريط الإجراءات العائم عند تحديد طلبات) --}}
        <div x-show="selectedOrderIds.length > 0" x-cloak
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-16 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-16 opacity-0"
            class="fixed bottom-5 inset-x-3 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 z-40 bg-gray-900/95 backdrop-blur-md text-white p-3.5 sm:px-5 sm:py-3.5 rounded-2xl shadow-2xl border border-gray-700/60 flex flex-wrap items-center justify-between gap-3 max-w-2xl w-full">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center font-black text-sm">
                    <span x-text="selectedOrderIds.length"></span>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-white flex items-center gap-1.5">
                        <span>تم تحديد</span>
                        <span class="text-amber-400 font-extrabold" x-text="selectedOrderIds.length"></span>
                        <span>طلب للشحن الجماعي</span>
                    </h4>
                    <p class="text-[11px] text-gray-400">
                        إجمالي المتبقي: <span class="font-mono font-bold text-amber-300" x-text="selectedTotalRemaining().toLocaleString()"></span> ج.م
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 mr-auto w-full sm:w-auto justify-end">
                <button type="button" @click="clearSelection()"
                    class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-xl text-xs font-semibold transition border border-gray-700">
                    إلغاء التحديد
                </button>
                <button type="button" @click="openBulkShipModal()"
                    class="px-4 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 text-white text-xs font-bold rounded-xl transition shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>شحن الطلبات المحددة لـ Turbo</span>
                    <span class="bg-black/20 px-1.5 py-0.5 rounded-md text-[10px]" x-text="'(' + selectedOrderIds.length + ')'"></span>
                </button>
            </div>
        </div>

        {{-- MODAL 4: BULK SHIPMENT & RESPONSE MODAL (مودال الشحن المتعدد ومتابعة استجابة كل طلب) --}}
        <div x-show="showBulkModal" x-cloak @keydown.escape.window="if (showBulkModal) closeBulkShipModal()"
            class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-2 sm:p-4 bg-gray-900/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="closeBulkShipModal()"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-4xl overflow-hidden transform transition-all my-auto max-h-[88vh] flex flex-col">

                {{-- Modal Header --}}
                <div class="px-4 py-3 sm:px-5 sm:py-3.5 bg-gradient-to-r from-amber-600 via-amber-700 to-amber-800 text-white flex items-center justify-between shrink-0 shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white shrink-0 shadow-inner">
                            <i class="fa-solid fa-boxes-packing text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm sm:text-base flex items-center gap-2 leading-tight">
                                <span>مراجعة وتأكيد شحن الطلبات المحددة لـ Turbo</span>
                                <span class="bg-white/20 text-white text-[10px] px-2 py-0.5 rounded-full font-mono font-bold" x-text="bulkResults.length + ' طلب'"></span>
                            </h3>
                            <p class="text-[10px] sm:text-[11px] text-amber-100 leading-tight mt-0.5"
                                x-text="bulkStage === 'review' ? 'يمكنك توسيع أي طلب لتعديل بيانات الشحن والعنوان قبل الإرسال' : (bulkStage === 'processing' ? 'جاري إرسال الشحنات ومعالجة استجابة كل طلب...' : 'اكتملت معالجة جميع الشحنات')"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeBulkShipModal()" class="text-amber-100 hover:text-white p-1 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- STAGE 1: REVIEW & EDIT (قبل الإرسال) --}}
                <div x-show="bulkStage === 'review'" class="flex flex-col flex-1 overflow-hidden">
                    
                    {{-- Summary Bar & Bulk Expand Actions --}}
                    <div class="px-3.5 py-2 sm:px-4 sm:py-2 bg-gradient-to-r from-amber-500/10 via-amber-50 to-orange-50/20 border-b border-amber-200/60 flex flex-wrap items-center justify-between gap-2 shrink-0 text-xs">
                        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3.5 text-xs">
                            <div class="flex items-center gap-1 font-bold text-gray-800">
                                <span class="text-gray-500 text-[11px]">الطلبات المحددة:</span>
                                <span class="font-mono text-amber-700 font-black text-xs" x-text="bulkResults.length"></span>
                            </div>
                            <div class="h-3.5 w-px bg-gray-300 hidden sm:block"></div>
                            <div class="flex items-center gap-1 font-bold text-gray-800">
                                <span class="text-gray-500 text-[11px]">إجمالي التحصيل:</span>
                                <span class="font-mono text-emerald-700 font-black text-xs" x-text="bulkTotalCollection() + ' ج.م'"></span>
                            </div>
                            <div class="h-3.5 w-px bg-gray-300 hidden sm:block"></div>
                            <div class="flex items-center gap-1 font-bold text-gray-800">
                                <span class="text-gray-500 text-[11px]">إجمالي الشحن:</span>
                                <span class="font-mono text-gray-700 font-bold text-xs" x-text="bulkTotalShipping() + ' ج.م'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="toggleAllBulkOrders(true)"
                                class="px-2.5 py-1 bg-white hover:bg-amber-50 text-amber-900 border border-amber-200 rounded-md text-[11px] font-semibold transition flex items-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-angles-down text-[9px]"></i>
                                <span>توسيع الكل</span>
                            </button>
                            <button type="button" @click="toggleAllBulkOrders(false)"
                                class="px-2.5 py-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-md text-[11px] font-semibold transition flex items-center gap-1 shadow-2xs">
                                <i class="fa-solid fa-angles-up text-[9px]"></i>
                                <span>طي الكل</span>
                            </button>
                        </div>
                    </div>

                    {{-- Review & Edit Table Container --}}
                    <div class="p-3 sm:p-3.5 flex-1 overflow-y-auto custom-scrollbar">
                        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-xs bg-white">
                            <div class="overflow-x-auto max-h-[50vh] custom-scrollbar">
                                <table class="w-full text-right text-xs">
                                    <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200 sticky top-0 z-20 shadow-2xs text-[11px]">
                                        <tr>
                                            <th class="py-2.5 px-3 whitespace-nowrap">رقم الطلب</th>
                                            <th class="py-2.5 px-3 whitespace-nowrap">العميل والهاتف</th>
                                            <th class="py-2.5 px-3 whitespace-nowrap">المحافظة والعنوان</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">تكلفة الشحن</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">إجمالي التحصيل</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">حالة البيانات</th>
                                            <th class="py-2.5 px-3 text-center whitespace-nowrap">تعديل</th>
                                        </tr>
                                    </thead>
                                    <template x-for="item in bulkResults" :key="item.order_id">
                                        <tbody class="border-b border-gray-200 transition-colors"
                                            :class="{'bg-amber-50/30': item.expanded, 'bg-white hover:bg-gray-50/40': !item.expanded}">
                                            
                                            {{-- Summary Row (Clickable) --}}
                                            <tr class="cursor-pointer select-none" @click="item.expanded = !item.expanded">
                                                <td class="py-2 px-3 font-bold text-gray-900 font-mono whitespace-nowrap">
                                                    <div class="flex items-center gap-1.5">
                                                        <button type="button" @click.stop="item.expanded = !item.expanded"
                                                            class="w-5 h-5 rounded bg-gray-100 hover:bg-amber-100 text-gray-600 hover:text-amber-800 flex items-center justify-center text-[9px] transition">
                                                            <i class="fa-solid transition-transform duration-200"
                                                                :class="item.expanded ? 'fa-chevron-up text-amber-700' : 'fa-chevron-down'"></i>
                                                        </button>
                                                        <span class="text-xs font-black text-amber-900" x-text="'#' + item.order_number"></span>
                                                    </div>
                                                </td>
                                                <td class="py-2 px-3">
                                                    <span class="font-bold text-gray-900 block truncate max-w-[130px] text-xs" x-text="item.receiver"></span>
                                                    <span class="text-[10px] text-gray-500 font-mono mt-0.5 block" dir="ltr" x-text="item.phone1"></span>
                                                </td>
                                                <td class="py-2 px-3 max-w-xs">
                                                    <div class="flex items-center gap-1 mb-0.5">
                                                        <span class="inline-block px-1.5 py-0.5 bg-gray-100 text-gray-800 text-[10px] rounded font-bold"
                                                            x-text="item.government || 'المحافظة غير محددة'"></span>
                                                        <template x-if="item.area">
                                                            <span class="text-[10px] text-gray-500 font-medium" x-text="item.area"></span>
                                                        </template>
                                                    </div>
                                                    <p class="text-[10px] text-gray-600 truncate max-w-[160px] leading-tight" x-text="item.address || 'العنوان غير محدد'"></p>
                                                </td>
                                                <td class="py-2 px-3 text-center font-bold whitespace-nowrap">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <span class="font-black text-xs" :class="item.is_shipped_paid ? 'text-emerald-600 font-black' : 'text-gray-800'" x-text="item.shipment_price"></span>
                                                        <span class="text-[9px] text-gray-400 font-normal">ج.م</span>
                                                    </div>
                                                    <template x-if="item.is_shipped_paid">
                                                        <span class="text-[8px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-1 py-0.2 rounded inline-flex items-center gap-0.5">
                                                            <i class="fa-solid fa-truck-fast text-[7px]"></i>
                                                            مدفوع
                                                        </span>
                                                    </template>
                                                    <template x-if="!item.is_shipped_paid && item.original_return_amount !== null && item.original_return_amount !== '' && item.shipment_price == item.original_return_amount">
                                                        <span class="text-[8px] text-emerald-700 font-bold bg-emerald-50 px-1 py-0.5 rounded inline-block">تلقائي</span>
                                                    </template>
                                                </td>
                                                <td class="py-2 px-3 text-center font-black text-amber-800 whitespace-nowrap">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <span class="text-xs font-black text-amber-900" x-text="item.amount_to_be_collected"></span>
                                                        <span class="text-[9px] text-amber-700 font-normal">ج.م</span>
                                                    </div>
                                                </td>
                                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                                    <template x-if="isOrderReady(item)">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold rounded-md shadow-2xs">
                                                            <i class="fa-solid fa-check text-[8px]"></i> جاهز
                                                        </span>
                                                    </template>
                                                    <template x-if="!isOrderReady(item)">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold rounded-md shadow-2xs">
                                                            <i class="fa-solid fa-triangle-exclamation text-[8px]"></i> ينقص بيانات
                                                        </span>
                                                    </template>
                                                </td>
                                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                                    <button type="button" @click.stop="item.expanded = !item.expanded"
                                                        class="px-2 py-0.5 rounded-md text-[11px] font-bold transition flex items-center gap-1 mx-auto shadow-2xs"
                                                        :class="item.expanded ? 'bg-amber-600 text-white' : 'bg-gray-100 hover:bg-amber-50 hover:text-amber-700 text-gray-700'">
                                                        <i class="fa-solid text-[9px]" :class="item.expanded ? 'fa-xmark' : 'fa-pen-to-square'"></i>
                                                        <span x-text="item.expanded ? 'إغلاق' : 'تعديل'"></span>
                                                    </button>
                                                </td>
                                            </tr>

                                            {{-- Expandable Edit Form Row --}}
                                            <tr x-show="item.expanded" x-cloak class="bg-amber-50/40">
                                                <td colspan="7" class="p-3 border-t border-amber-200/70">
                                                    <div class="bg-white rounded-xl p-3 border border-amber-200 shadow-xs space-y-3">
                                                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                                <h5 class="text-xs font-bold text-gray-900">
                                                                    تعديل بيانات شحن الطلب <span class="font-mono text-amber-700" x-text="'#' + item.order_number"></span>
                                                                </h5>
                                                            </div>
                                                            <button type="button" @click="item.expanded = false"
                                                                class="text-[11px] text-amber-700 hover:text-amber-800 font-bold flex items-center gap-1 bg-amber-50 hover:bg-amber-100 px-2 py-0.5 rounded-md transition">
                                                                <i class="fa-solid fa-check text-[10px]"></i>
                                                                <span>تم وحفظ التعديل</span>
                                                            </button>
                                                        </div>

                                                        {{-- الراسل الفرعي --}}
                                                        <div class="bg-gray-100 px-2.5 py-1.5 rounded-lg text-xs font-bold text-gray-700 flex items-center justify-between">
                                                            <span class="text-[11px]">الراسل الفرعي :</span>
                                                            <span class="text-amber-700 font-extrabold text-[11px]">Tagmedix</span>
                                                        </div>

                                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 text-xs">
                                                            {{-- المرسل اليه --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">المرسل اليه <span class="text-rose-500">*</span></label>
                                                                <input type="text" x-model="item.receiver" required placeholder="اسم العميل"
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- تيليفون المتابعه --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">تيليفون المتابعه</label>
                                                                <input type="text" x-model="item.api_followup_phone" placeholder="اختياري"
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-mono" dir="ltr">
                                                            </div>

                                                            {{-- تلفيون المرسل اليه ١ --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">تلفيون المرسل اليه ١ <span class="text-rose-500">*</span></label>
                                                                <input type="text" x-model="item.phone1" required placeholder="01xxxxxxxxx"
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-mono" dir="ltr">
                                                            </div>

                                                            {{-- تيليفون المرسل اليه ٢ --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">تيليفون المرسل اليه ٢</label>
                                                                <input type="text" x-model="item.phone2" placeholder="اختياري"
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-mono" dir="ltr">
                                                            </div>

                                                            {{-- المحافظة --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">المحافظة <span class="text-rose-500">*</span></label>
                                                                <input type="text" x-model="item.government" @input="onOrderLocationChange(item)" placeholder="اسم المحافظة" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- المنطقة --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">المنطقة <span class="text-rose-500">*</span></label>
                                                                <input type="text" x-model="item.area" @input="onOrderLocationChange(item)" placeholder="اسم المنطقة أو الحي" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- تفاصيل التحصيل المالي (كاملة) --}}
                                                            <div class="sm:col-span-2 lg:col-span-3 bg-amber-50/60 p-2.5 rounded-xl border border-amber-200">
                                                                <h6 class="text-[11px] font-bold text-amber-800 mb-2 flex items-center gap-1">
                                                                    <i class="fa-solid fa-calculator text-[10px]"></i>
                                                                    <span>تفاصيل التحصيل المالي</span>
                                                                </h6>
                                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                                                    {{-- المتبقي من الطلب --}}
                                                                    <div>
                                                                        <label class="block text-[9px] font-semibold text-gray-600 mb-0.5">المتبقي من الطلب</label>
                                                                        <div class="px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-700 flex justify-between items-center shadow-xs">
                                                                            <span x-text="item.order_remaining"></span>
                                                                            <span class="text-[9px] text-gray-400">ج.م</span>
                                                                        </div>
                                                                    </div>

                                                                    {{-- تكلفة الشحن --}}
                                                                    <div>
                                                                        <div class="flex items-center justify-between mb-0.5">
                                                                            <label class="block text-[9px] font-semibold text-gray-700">تكلفة الشحن <span class="text-rose-500">*</span></label>
                                                                            <template x-if="item.is_shipped_paid">
                                                                                <span class="text-[8px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded inline-flex items-center gap-0.5">
                                                                                    <i class="fa-solid fa-check text-[7px]"></i>
                                                                                    شحن مدفوع
                                                                                </span>
                                                                            </template>
                                                                            <template x-if="!item.is_shipped_paid && item.original_return_amount !== null && item.original_return_amount !== '' && item.shipment_price == item.original_return_amount">
                                                                                <span class="text-[8px] text-emerald-600 font-bold bg-emerald-50 px-1 py-0.2 rounded">
                                                                                    تلقائي
                                                                                </span>
                                                                            </template>
                                                                        </div>
                                                                        <div class="relative">
                                                                            <input type="number" step="0.01" x-model="item.shipment_price" @input="onShipmentPriceChange(item)" required
                                                                                class="w-full pl-14 pr-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold shadow-xs text-gray-700"
                                                                                :class="{'border-emerald-500/60 bg-emerald-50/20 text-emerald-700': item.is_shipped_paid}">
                                                                            <div class="absolute left-1.5 top-1/2 -translate-y-1/2 flex items-center gap-1">
                                                                                <template x-if="!item.is_shipped_paid && item.original_return_amount !== null && item.original_return_amount !== '' && item.shipment_price != item.original_return_amount">
                                                                                    <button type="button" @click="resetShipmentPrice(item)"
                                                                                        class="text-[8px] bg-gray-100 hover:bg-gray-200 text-gray-600 px-1 py-0.5 rounded font-bold transition"
                                                                                        title="العودة للسعر التلقائي">
                                                                                        <i class="fa-solid fa-rotate-left text-[7px]"></i>
                                                                                    </button>
                                                                                </template>
                                                                                <span class="text-[9px] text-gray-400">ج.م</span>
                                                                            </div>
                                                                        </div>
                                                                        <label class="flex items-center gap-1.5 cursor-pointer mt-1 select-none">
                                                                            <input type="checkbox" x-model="item.is_shipped_paid" @change="onToggleShippedPaid(item)"
                                                                                class="w-3.5 h-3.5 rounded text-emerald-600 border-gray-300 focus:ring-emerald-500 focus:ring-offset-0 cursor-pointer">
                                                                            <span class="text-[9px] font-semibold text-gray-600 hover:text-gray-800">تم سداد الشحن مسبقاً (تكلفة الشحن = 0)</span>
                                                                        </label>
                                                                    </div>

                                                                    {{-- المطلوب تحصيله شامل الشحن --}}
                                                                    <div>
                                                                        <label class="block text-[9px] font-semibold text-gray-700 mb-0.5">المطلوب تحصيله شامل الشحن</label>
                                                                        <div class="px-2.5 py-1.5 bg-gradient-to-l from-amber-100 to-amber-50 border border-amber-200 rounded-lg text-xs font-black text-amber-900 flex justify-between items-center shadow-inner">
                                                                            <span x-text="item.amount_to_be_collected"></span>
                                                                            <span class="text-[9px] text-amber-700 font-normal">ج.م</span>
                                                                        </div>
                                                                    </div>

                                                                    {{-- في حالة الارتجاع يدفع العميل --}}
                                                                    <div>
                                                                        <label class="block text-[9px] font-semibold text-gray-700 mb-0.5">سعر الارتجاع <span class="text-rose-500">*</span></label>
                                                                        <div class="relative">
                                                                            <input type="number" step="0.01" x-model="item.return_amount" required
                                                                                class="w-full pl-7 pr-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-bold shadow-xs text-rose-700">
                                                                            <div class="absolute left-2 top-1/2 -translate-y-1/2">
                                                                                <span class="text-[9px] text-rose-400">ج.م</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- نوع الطلب --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">نوع الطلب <span class="text-rose-500">*</span></label>
                                                                <select x-model="item.is_order" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                    <option value="0">تسليم طرد</option>
                                                                    <option value="1">مرتجع جزئي</option>
                                                                    <option value="2">استبدال</option>
                                                                    <option value="3">استرجاع (مرتجع كامل)</option>
                                                                </select>
                                                            </div>

                                                            {{-- محتوى الطرد المرتجع إذا وجد --}}
                                                            <div x-show="item.is_order != '0'" x-cloak>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">محتوى الطرد المرتجع <span class="text-rose-500">*</span></label>
                                                                <input type="text" x-model="item.return_summary" placeholder="وصف المرتجع"
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- مكان التسليم --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">مكان التسليم <span class="text-rose-500">*</span></label>
                                                                <select x-model="item.delivery_type" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                    <option :value="0">توصيل إلى عنوان المستلم</option>
                                                                    <option :value="1">استلام من فرع Turbo</option>
                                                                </select>
                                                            </div>

                                                            {{-- السماح بفتح الشحنة --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">السماح بفتح الشحنة <span class="text-rose-500">*</span></label>
                                                                <select x-model="item.can_open" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                    <option :value="1">نعم، يسمح بالفتح</option>
                                                                    <option :value="0">لا يسمح بالفتح</option>
                                                                </select>
                                                            </div>

                                                            {{-- محتوي الطرد --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">محتوي الطرد <span class="text-rose-500">*</span></label>
                                                                <input type="text" x-model="item.order_summary" placeholder="ملابس" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- عدد القطع --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">عدد القطع <span class="text-rose-500">*</span></label>
                                                                <input type="number" x-model="item.number_of_items" min="1" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- الوزن (كجم) --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">الوزن (كجم) <span class="text-rose-500">*</span></label>
                                                                <input type="number" step="0.1" x-model="item.weight" min="0.1" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- قابل للكسر --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">قابل للكسر <span class="text-rose-500">*</span></label>
                                                                <select x-model="item.is_fragile" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                    <option :value="0">لا</option>
                                                                    <option :value="1">نعم</option>
                                                                </select>
                                                            </div>

                                                            {{-- رقم الفاتوره --}}
                                                            <div>
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">رقم الفاتوره</label>
                                                                <input type="text" x-model="item.invoice_number"
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            </div>

                                                            {{-- العنوان --}}
                                                            <div class="sm:col-span-2 lg:col-span-3">
                                                                <label class="block font-semibold text-gray-700 mb-0.5 text-[10px]">العنوان <span class="text-rose-500">*</span></label>
                                                                <textarea x-model="item.address" rows="1.5" placeholder="تفاصيل العنوان" required
                                                                    class="w-full px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </template>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Review Footer --}}
                    <div class="px-4 py-2.5 sm:px-5 sm:py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between shrink-0 shadow-inner">
                        <button type="button" @click="closeBulkShipModal()"
                            class="px-3.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition">
                            إلغاء
                        </button>
                        <button type="button" @click="startBulkShipping()" :disabled="bulkResults.length === 0"
                            class="px-4 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white text-xs font-bold rounded-lg transition shadow-sm hover:shadow-md flex items-center gap-1.5 disabled:opacity-50">
                            <i class="fa-solid fa-paper-plane text-[11px]"></i>
                            <span>بدء إرسال الشحنات لشركة Turbo (<span x-text="bulkResults.length"></span>)</span>
                        </button>
                    </div>
                </div>

                {{-- STAGE 2 & 3: LIVE PROGRESS & PER-ORDER RESPONSE HANDLING --}}
                <div x-show="bulkStage === 'processing' || bulkStage === 'done'" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-3 sm:p-4 space-y-3 overflow-y-auto custom-scrollbar flex-1">

                        {{-- Progress Bar & Stats Pill Counter --}}
                        <div class="bg-gray-50 p-3 rounded-xl border border-gray-200/80 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 font-bold text-gray-800">
                                    <template x-if="bulkStage === 'processing'">
                                        <div class="flex items-center gap-2 text-amber-700">
                                            <i class="fa-solid fa-spinner animate-spin text-sm"></i>
                                            <span>جاري إرسال الشحنات إلى Turbo ومتابعة الردود...</span>
                                        </div>
                                    </template>
                                    <template x-if="bulkStage === 'done'">
                                        <div class="flex items-center gap-2 text-emerald-700">
                                            <i class="fa-solid fa-circle-check text-sm"></i>
                                            <span>اكتملت معالجة جميع الطلبات!</span>
                                        </div>
                                    </template>
                                </div>
                                <div class="font-mono font-bold text-gray-600 text-[11px]">
                                    <span x-text="getBulkCounts().completed"></span> / <span x-text="getBulkCounts().total"></span>
                                    (<span x-text="getBulkCounts().percent"></span>%)
                                </div>
                            </div>

                            {{-- Animated Progress Bar --}}
                            <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-500 via-amber-600 to-emerald-600 transition-all duration-300 rounded-full"
                                    :style="'width: ' + getBulkCounts().percent + '%'"></div>
                            </div>

                            {{-- 4 Live KPI Badges --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 pt-0.5 text-center text-xs">
                                <div class="bg-white p-1.5 rounded-lg border border-gray-200 shadow-2xs">
                                    <span class="text-[9px] text-gray-400 block">الإجمالي</span>
                                    <span class="font-black text-xs text-gray-800" x-text="getBulkCounts().total"></span>
                                </div>
                                <div class="bg-amber-50 p-1.5 rounded-lg border border-amber-200 shadow-2xs">
                                    <span class="text-[9px] text-amber-700 block">قيد الإرسال</span>
                                    <span class="font-black text-xs text-amber-700" x-text="getBulkCounts().sending + getBulkCounts().pending"></span>
                                </div>
                                <div class="bg-emerald-50 p-1.5 rounded-lg border border-emerald-200 shadow-2xs">
                                    <span class="text-[9px] text-emerald-700 block">تم الشحن بنجاح</span>
                                    <span class="font-black text-xs text-emerald-700" x-text="getBulkCounts().success"></span>
                                </div>
                                <div class="bg-rose-50 p-1.5 rounded-lg border border-rose-200 shadow-2xs">
                                    <span class="text-[9px] text-rose-700 block">تعذر الشحن</span>
                                    <span class="font-black text-xs text-rose-700" x-text="getBulkCounts().failed"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Per-Order Response Cards List --}}
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold text-gray-800 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-bars-staggered text-amber-600 text-xs"></i>
                                    <span>تفاصيل نتيجة واستجابة كل طلب:</span>
                                </span>
                                <span class="text-[10px] text-gray-400">تحديث فوري مع كل رد</span>
                            </h4>

                            <div class="space-y-1.5 max-h-72 overflow-y-auto custom-scrollbar p-0.5">
                                <template x-for="(item, idx) in bulkResults" :key="item.order_id">
                                    <div class="p-2.5 rounded-xl border transition-all duration-200 shadow-2xs text-xs"
                                        :class="{
                                            'bg-white border-gray-200': item.status === 'pending',
                                            'bg-amber-50/70 border-amber-300 ring-1 ring-amber-400/30': item.status === 'sending',
                                            'bg-emerald-50/70 border-emerald-300 ring-1 ring-emerald-300/40': item.status === 'success',
                                            'bg-rose-50/70 border-rose-300 ring-1 ring-rose-300/40': item.status === 'failed'
                                        }">
                                        
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                            {{-- Info snippet --}}
                                            <div class="flex items-start gap-2.5">
                                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5 text-xs shadow-2xs"
                                                    :class="{
                                                        'bg-gray-100 text-gray-500': item.status === 'pending',
                                                        'bg-amber-100 text-amber-600': item.status === 'sending',
                                                        'bg-emerald-100 text-emerald-700': item.status === 'success',
                                                        'bg-rose-100 text-rose-600': item.status === 'failed'
                                                    }">
                                                    <template x-if="item.status === 'pending'">
                                                        <i class="fa-solid fa-clock text-[11px]"></i>
                                                    </template>
                                                    <template x-if="item.status === 'sending'">
                                                        <i class="fa-solid fa-spinner animate-spin text-[11px]"></i>
                                                    </template>
                                                    <template x-if="item.status === 'success'">
                                                        <i class="fa-solid fa-check text-xs font-bold"></i>
                                                    </template>
                                                    <template x-if="item.status === 'failed'">
                                                        <i class="fa-solid fa-triangle-exclamation text-xs font-bold"></i>
                                                    </template>
                                                </div>

                                                <div>
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-black text-xs text-gray-900 font-mono" x-text="'#' + item.order_number"></span>
                                                        <span class="text-xs font-bold text-gray-800" x-text="item.receiver"></span>
                                                        <span class="text-[10px] text-gray-500 font-mono" dir="ltr" x-text="item.phone1"></span>
                                                    </div>
                                                    <div class="text-[10px] text-gray-500 mt-0.5 flex flex-wrap items-center gap-1.5">
                                                        <span class="bg-gray-100 px-1 py-0.2 rounded text-[9px]" x-text="item.government + (item.area ? ' - ' + item.area : '')"></span>
                                                        <span class="text-gray-400 truncate max-w-[160px]" x-text="item.address"></span>
                                                        <span class="font-bold text-amber-800" x-text="'المطلوب: ' + item.amount_to_be_collected + ' ج.م'"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Status & Quick Action --}}
                                            <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                                <template x-if="item.status === 'pending'">
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-semibold">
                                                        في الانتظار
                                                    </span>
                                                </template>

                                                <template x-if="item.status === 'sending'">
                                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[10px] font-bold flex items-center gap-1 animate-pulse">
                                                        <i class="fa-solid fa-circle-notch animate-spin text-[9px]"></i>
                                                        جاري الإرسال
                                                    </span>
                                                </template>

                                                <template x-if="item.status === 'success'">
                                                    <div class="flex items-center gap-1.5">
                                                        <div class="text-left sm:text-right">
                                                            <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[9px] font-bold block">
                                                                تم الشحن بنجاح
                                                            </span>
                                                            <span class="text-[9px] font-mono text-emerald-700 font-bold block" x-text="'Turbo: ' + item.turbo_id"></span>
                                                        </div>
                                                        <a :href="item.shipment_url" target="_blank"
                                                            class="p-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition flex items-center"
                                                            title="عرض الشحنة">
                                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                        </a>
                                                    </div>
                                                </template>

                                                <template x-if="item.status === 'failed'">
                                                    <div class="flex items-center gap-1.5">
                                                        <button type="button" @click="retrySingleBulkOrder(item)" :disabled="bulkStage === 'processing'"
                                                            class="px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[10px] font-bold transition flex items-center gap-1 shadow-xs disabled:opacity-50">
                                                            <i class="fa-solid fa-rotate-right text-[9px]"></i>
                                                            <span>إعادة</span>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Error Details Card if Failed --}}
                                        <template x-if="item.status === 'failed' && item.error_message">
                                            <div class="mt-2 pt-1.5 border-t border-rose-200 text-[11px] text-rose-800 flex items-start gap-1.5 bg-rose-100/50 p-2 rounded-lg">
                                                <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5 shrink-0 text-xs"></i>
                                                <div class="flex-1">
                                                    <span class="font-bold block text-rose-900 mb-0.5 text-[10px]">سبب تعذر الشحن من Turbo:</span>
                                                    <span class="font-medium leading-relaxed" x-text="item.error_message"></span>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Success details if Success --}}
                                        <template x-if="item.status === 'success' && item.tracking_number">
                                            <div class="mt-1.5 pt-1.5 border-t border-emerald-200 text-[10px] text-emerald-800 flex items-center justify-between">
                                                <span class="text-emerald-700">باركود: <strong class="font-mono font-bold" x-text="item.tracking_number"></strong></span>
                                                <span class="text-emerald-600">المطلوب: <strong class="font-mono font-bold" x-text="item.amount_to_be_collected"></strong> ج.م</span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    {{-- Processing & Done Footer --}}
                    <div class="px-4 py-2.5 sm:px-5 sm:py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 shrink-0">
                        <div>
                            <template x-if="bulkStage === 'processing'">
                                <span class="text-xs text-amber-700 font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-spinner animate-spin text-[11px]"></i>
                                    <span>يرجى الانتظار حتى اكتمال المعالجة...</span>
                                </span>
                            </template>
                            <template x-if="bulkStage === 'done' && getBulkCounts().failed > 0">
                                <span class="text-xs text-rose-700 font-bold">
                                    تعذر شحن <span x-text="getBulkCounts().failed"></span> طلبات.
                                </span>
                            </template>
                            <template x-if="bulkStage === 'done' && getBulkCounts().failed === 0">
                                <span class="text-xs text-emerald-700 font-bold">
                                    تم شحن جميع الطلبات المحددة بنجاح!
                                </span>
                            </template>
                        </div>

                        <div class="flex items-center gap-1.5 mr-auto">
                            <template x-if="bulkStage === 'done' && getBulkCounts().failed > 0">
                                <button type="button" @click="retryFailedBulkOrders()"
                                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-rotate-right text-[10px]"></i>
                                    <span>إعادة الفاشلة (<span x-text="getBulkCounts().failed"></span>)</span>
                                </button>
                            </template>

                            <template x-if="bulkStage === 'done'">
                                <a href="{{ route('shipments.index') }}"
                                    class="px-3.5 py-1.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-truck-fast text-[11px]"></i>
                                    <span>جدول الشحنات</span>
                                </a>
                            </template>

                            <button type="button" @click="closeBulkShipModal()"
                                class="px-3.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition">
                                <span x-text="bulkStage === 'done' ? 'إغلاق وتحديث' : 'إلغاء'"></span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function finishedGoodsApp() {
            const dbReturnAmounts = @json($returnAmounts ?? []);
            const allOrders = @json($orders ?? []);

            return {
                rawOrders: allOrders,
                selectedOrderIds: [],
                showDetails: false,
                showShip: false,
                showSuccessModal: false,
                showBulkModal: false,
                bulkStage: 'review', // 'review' | 'processing' | 'done'
                bulkResults: [],
                bulkSettings: {
                    delivery_type: 0,
                    can_open: 1,
                    is_fragile: 0,
                    order_summary: 'ملابس',
                    weight: 1
                },
                successData: null,
                isSubmitting: false,
                modalError: null,
                activeOrder: null,
                shipForm: {
                    receiver: '',
                    api_followup_phone: '',
                    phone1: '',
                    phone2: '',
                    government: '',
                    area: '',
                    address: '',
                    order_remaining: 0,
                    shipment_price: '',
                    amount_to_be_collected: 0,
                    return_amount: '',
                    original_return_amount: '',
                    delivery_type: 0,
                    is_order: '0',
                    return_summary: '',
                    can_open: 1,
                    number_of_items: 1,
                    weight: 1,
                    is_fragile: 0,
                    order_summary: 'ملابس',
                    invoice_number: '',
                    remote_order_id: ''
                },

                normalizePhone(num) {
                    if (!num) return '';
                    let clean = num.toString().replace(/[^\d]/g, '');
                    if (clean.length === 12 && clean.startsWith('201')) {
                        return '0' + clean.substring(2);
                    }
                    if (clean.length === 14 && clean.startsWith('00201')) {
                        return '0' + clean.substring(4);
                    }
                    if (clean.length === 10 && clean.startsWith('1')) {
                        return '0' + clean;
                    }
                    return clean || num;
                },

                isAllSelected() {
                    return this.rawOrders.length > 0 && this.selectedOrderIds.length === this.rawOrders.length;
                },

                toggleSelectAll() {
                    if (this.isAllSelected()) {
                        this.selectedOrderIds = [];
                    } else {
                        this.selectedOrderIds = this.rawOrders.map(o => o.id);
                    }
                },

                isOrderSelected(id) {
                    return this.selectedOrderIds.includes(id);
                },

                toggleOrderSelection(id) {
                    if (this.selectedOrderIds.includes(id)) {
                        this.selectedOrderIds = this.selectedOrderIds.filter(item => item !== id);
                    } else {
                        this.selectedOrderIds.push(id);
                    }
                },

                clearSelection() {
                    this.selectedOrderIds = [];
                },

                selectedTotalRemaining() {
                    return this.rawOrders
                        .filter(o => this.selectedOrderIds.includes(o.id))
                        .reduce((sum, o) => sum + this.calculateRemaining(o), 0);
                },

                openBulkShipModal() {
                    if (this.selectedOrderIds.length === 0) return;

                    this.bulkStage = 'review';
                    this.bulkResults = this.selectedOrderIds.map(orderId => {
                        const order = this.rawOrders.find(o => o.id === orderId);
                        if (!order) return null;

                        const custName = order.customer ? order.customer.name : (order.customer_name || '');
                        const p1 = this.normalizePhone(order.customer ? order.customer.phone_1 : (order.phone_1 || ''));
                        const p2 = this.normalizePhone(order.customer ? order.customer.phone_2 : (order.phone_2 || ''));
                        const gov = order.address ? order.address.governorate : (order.governorate || '');
                        const area = order.address ? order.address.city : '';
                        const addr = order.address ? order.address.full_address : (order.full_address || '');
                        const remaining = this.calculateRemaining(order);

                        let matchedReturnAmount = 0;
                        const exactMatch = dbReturnAmounts.find(r => r.government_name === gov && r.area_name === area);
                        const govMatch = dbReturnAmounts.find(r => r.government_name === gov && !r.area_name);
                        if (exactMatch) {
                            matchedReturnAmount = parseFloat(exactMatch.return_amount || 0);
                        } else if (govMatch) {
                            matchedReturnAmount = parseFloat(govMatch.return_amount || 0);
                        }

                        const isShippedPaid = Boolean(order.is_shipped_paid);
                        const initialShipmentPrice = isShippedPaid ? 0 : matchedReturnAmount;

                        const summary = order.items && order.items.length
                            ? order.items.map(i => `${i.quantity}x ${i.style ? i.style.name : 'منتج'}`).join(', ')
                            : 'ملابس';

                        const itemCount = order.items && order.items.length
                            ? order.items.reduce((sum, i) => sum + parseInt(i.quantity || 1), 0)
                            : 1;

                        return {
                            order_id: order.id,
                            order_number: order.order_number || ('#' + order.id),
                            expanded: false,
                            receiver: custName,
                            api_followup_phone: '',
                            phone1: p1,
                            phone2: p2,
                            government: gov,
                            area: area,
                            address: addr,
                            is_shipped_paid: isShippedPaid,
                            order_remaining: remaining,
                            shipment_price: initialShipmentPrice,
                            original_return_amount: matchedReturnAmount,
                            amount_to_be_collected: (parseFloat(remaining || 0) + parseFloat(initialShipmentPrice || 0)).toFixed(2),
                            return_amount: matchedReturnAmount,
                            is_order: '0',
                            return_summary: '',
                            delivery_type: 0,
                            can_open: 1,
                            order_summary: summary,
                            number_of_items: itemCount,
                            weight: 1,
                            is_fragile: 0,
                            invoice_number: order.order_number || '',
                            remote_order_id: order.order_number || '',
                            status: 'pending',
                            turbo_id: null,
                            tracking_number: null,
                            shipment_url: null,
                            order_url: null,
                            error_message: null
                        };
                    }).filter(Boolean);

                    this.showBulkModal = true;
                },

                closeBulkShipModal() {
                    if (this.bulkStage === 'processing') {
                        if (!confirm('عملية الإرسال جارية حالياً، هل أنت متأكد من الإغلاق؟')) return;
                    }

                    const anySuccess = this.bulkResults.some(r => r.status === 'success');
                    this.showBulkModal = false;

                    if (anySuccess && this.bulkStage === 'done') {
                        window.location.reload();
                    }
                },

                onOrderLocationChange(item) {
                    const gov = (item.government || '').trim();
                    const area = (item.area || '').trim();
                    let matchedReturn = null;
                    const exactMatch = dbReturnAmounts.find(r => r.government_name === gov && r.area_name === area);
                    const govMatch = dbReturnAmounts.find(r => r.government_name === gov && !r.area_name);
                    if (exactMatch) {
                        matchedReturn = parseFloat(exactMatch.return_amount || 0);
                    } else if (govMatch) {
                        matchedReturn = parseFloat(govMatch.return_amount || 0);
                    }
                    if (matchedReturn !== null) {
                        item.original_return_amount = matchedReturn;
                        if (item.is_shipped_paid) {
                            item.shipment_price = 0;
                        } else {
                            item.shipment_price = matchedReturn;
                        }
                        item.return_amount = matchedReturn;
                    }
                    this.recalculateOrderTotal(item);
                },

                onShipmentPriceChange(item) {
                    item.return_amount = item.shipment_price;
                    this.recalculateOrderTotal(item);
                },

                onToggleShippedPaid(item) {
                    if (item.is_shipped_paid) {
                        item.shipment_price = 0;
                    } else {
                        item.shipment_price = (item.original_return_amount !== null && item.original_return_amount !== '')
                            ? item.original_return_amount
                            : 0;
                    }
                    this.onShipmentPriceChange(item);
                },

                resetShipmentPrice(item) {
                    if (item.is_shipped_paid) {
                        item.shipment_price = 0;
                        this.recalculateOrderTotal(item);
                        return;
                    }
                    if (item.original_return_amount !== null && item.original_return_amount !== '') {
                        item.shipment_price = item.original_return_amount;
                        item.return_amount = item.original_return_amount;
                        this.recalculateOrderTotal(item);
                    }
                },

                recalculateOrderTotal(item) {
                    const remaining = parseFloat(item.order_remaining || 0);
                    const shipping = parseFloat(item.shipment_price || 0);
                    item.amount_to_be_collected = (remaining + shipping).toFixed(2);
                },

                isOrderReady(item) {
                    return Boolean(
                        item.receiver && item.receiver.trim() &&
                        item.phone1 && item.phone1.trim() &&
                        item.government && item.government.trim() &&
                        item.address && item.address.trim() &&
                        item.order_summary && item.order_summary.trim()
                    );
                },

                toggleAllBulkOrders(expand) {
                    this.bulkResults.forEach(item => item.expanded = expand);
                },

                bulkTotalCollection() {
                    return this.bulkResults.reduce((sum, item) => sum + parseFloat(item.amount_to_be_collected || 0), 0).toFixed(2);
                },

                bulkTotalShipping() {
                    return this.bulkResults.reduce((sum, item) => sum + parseFloat(item.shipment_price || 0), 0).toFixed(2);
                },

                getBulkCounts() {
                    const total = this.bulkResults.length;
                    const success = this.bulkResults.filter(r => r.status === 'success').length;
                    const failed = this.bulkResults.filter(r => r.status === 'failed').length;
                    const sending = this.bulkResults.filter(r => r.status === 'sending').length;
                    const pending = this.bulkResults.filter(r => r.status === 'pending').length;
                    const completed = success + failed;
                    const percent = total > 0 ? Math.round((completed / total) * 100) : 0;

                    return { total, success, failed, sending, pending, completed, percent };
                },

                async startBulkShipping() {
                    const invalidOrders = this.bulkResults.filter(item => !this.isOrderReady(item));
                    if (invalidOrders.length > 0) {
                        alert(`تنبيه: يوجد ${invalidOrders.length} طلب بحاجة لاستكمال البيانات الأساسية (الاسم، الهاتف، المحافظة، أو العنوان). تم فتح الطلبات الناقصة لتعديلها.`);
                        invalidOrders.forEach(o => o.expanded = true);
                        return;
                    }

                    this.bulkStage = 'processing';

                    for (const item of this.bulkResults) {
                        if (item.status === 'success') continue;

                        item.status = 'sending';
                        item.error_message = null;

                        try {
                            const formData = new FormData();
                            formData.append('_token', '{{ csrf_token() }}');
                            formData.append('order_id', item.order_id);
                            formData.append('receiver', item.receiver);
                            formData.append('phone1', item.phone1);
                            formData.append('phone2', item.phone2 || '');
                            formData.append('api_followup_phone', item.api_followup_phone || '');
                            formData.append('government', item.government);
                            formData.append('area', item.area || '');
                            formData.append('address', item.address);
                            formData.append('order_summary', item.order_summary || 'ملابس');
                            formData.append('number_of_items', item.number_of_items || 1);
                            formData.append('weight', item.weight || 1);
                            formData.append('delivery_type', item.delivery_type !== undefined ? item.delivery_type : 0);
                            formData.append('can_open', item.can_open !== undefined ? item.can_open : 1);
                            formData.append('is_fragile', item.is_fragile !== undefined ? item.is_fragile : 0);
                            formData.append('is_order', item.is_order !== undefined ? item.is_order : '0');
                            formData.append('return_summary', item.return_summary || '');
                            formData.append('return_amount', item.return_amount !== undefined ? item.return_amount : 0);
                            formData.append('amount_to_be_collected', item.amount_to_be_collected !== undefined ? item.amount_to_be_collected : (parseFloat(item.order_remaining || 0) + parseFloat(item.shipment_price || 0)));
                            formData.append('remote_order_id', item.remote_order_id || item.order_number);
                            formData.append('invoice_number', item.invoice_number || item.order_number);
                            formData.append('is_shipped_paid', item.is_shipped_paid ? 1 : 0);

                            const url = `{{ url('finished-goods') }}/${item.order_id}/ship`;
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: formData
                            });

                            const data = await response.json().catch(() => null);

                            if (response.ok && data && data.success) {
                                item.status = 'success';
                                item.turbo_id = data.turbo_id || 'تم';
                                item.tracking_number = data.tracking_number || data.turbo_id || '';
                                item.shipment_url = data.shipment_url || (`{{ route('shipments.index') }}?q=` + encodeURIComponent(data.turbo_id || item.order_number));
                                item.order_url = data.order_url || (`{{ url('orders') }}/${item.order_id}`);
                                item.error_message = null;

                                this.selectedOrderIds = this.selectedOrderIds.filter(id => id !== item.order_id);
                            } else {
                                item.status = 'failed';
                                let errMsg = 'فشل شحن الطلب لدى Turbo';
                                if (data) {
                                    if (data.errors && typeof data.errors === 'object') {
                                        const errorList = Object.values(data.errors).flat();
                                        if (errorList.length > 0) errMsg = errorList.join(' | ');
                                        else if (data.message) errMsg = data.message;
                                    } else if (data.message) {
                                        errMsg = data.message;
                                    }
                                } else if (response && response.status) {
                                    errMsg = `خطأ في الخادم (${response.status})`;
                                }
                                item.error_message = errMsg;
                            }
                        } catch (err) {
                            console.error('Bulk ship error:', err);
                            item.status = 'failed';
                            item.error_message = err.message || 'حدث خطأ في الشبكة أو الخادم';
                        }
                    }

                    this.bulkStage = 'done';
                },

                async retrySingleBulkOrder(item) {
                    item.status = 'sending';
                    item.error_message = null;

                    try {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('order_id', item.order_id);
                        formData.append('receiver', item.receiver);
                        formData.append('phone1', item.phone1);
                        formData.append('phone2', item.phone2 || '');
                        formData.append('api_followup_phone', item.api_followup_phone || '');
                        formData.append('government', item.government);
                        formData.append('area', item.area || '');
                        formData.append('address', item.address);
                        formData.append('order_summary', item.order_summary || 'ملابس');
                        formData.append('number_of_items', item.number_of_items || 1);
                        formData.append('weight', item.weight || 1);
                        formData.append('delivery_type', item.delivery_type !== undefined ? item.delivery_type : 0);
                        formData.append('can_open', item.can_open !== undefined ? item.can_open : 1);
                        formData.append('is_fragile', item.is_fragile !== undefined ? item.is_fragile : 0);
                        formData.append('is_order', item.is_order !== undefined ? item.is_order : '0');
                        formData.append('return_summary', item.return_summary || '');
                        formData.append('return_amount', item.return_amount !== undefined ? item.return_amount : 0);
                        formData.append('amount_to_be_collected', item.amount_to_be_collected !== undefined ? item.amount_to_be_collected : (parseFloat(item.order_remaining || 0) + parseFloat(item.shipment_price || 0)));
                        formData.append('remote_order_id', item.remote_order_id || item.order_number);
                        formData.append('invoice_number', item.invoice_number || item.order_number);
                        formData.append('is_shipped_paid', item.is_shipped_paid ? 1 : 0);

                        const url = `{{ url('finished-goods') }}/${item.order_id}/ship`;
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json().catch(() => null);

                        if (response.ok && data && data.success) {
                            item.status = 'success';
                            item.turbo_id = data.turbo_id || 'تم';
                            item.tracking_number = data.tracking_number || data.turbo_id || '';
                            item.shipment_url = data.shipment_url || (`{{ route('shipments.index') }}?q=` + encodeURIComponent(data.turbo_id || item.order_number));
                            item.order_url = data.order_url || (`{{ url('orders') }}/${item.order_id}`);
                            item.error_message = null;

                            this.selectedOrderIds = this.selectedOrderIds.filter(id => id !== item.order_id);
                        } else {
                            item.status = 'failed';
                            let errMsg = 'فشل شحن الطلب لدى Turbo';
                            if (data) {
                                if (data.errors && typeof data.errors === 'object') {
                                    const errorList = Object.values(data.errors).flat();
                                    if (errorList.length > 0) errMsg = errorList.join(' | ');
                                    else if (data.message) errMsg = data.message;
                                } else if (data.message) {
                                    errMsg = data.message;
                                }
                            } else if (response && response.status) {
                                errMsg = `خطأ في الخادم (${response.status})`;
                            }
                            item.error_message = errMsg;
                        }
                    } catch (err) {
                        item.status = 'failed';
                        item.error_message = err.message || 'حدث خطأ غير متوقع';
                    }
                },

                retryFailedBulkOrders() {
                    this.startBulkShipping();
                },

                // Old single modal methods (100% UNTOUCHED)
                openDetailsModal(order) {
                    this.activeOrder = order;
                    this.showDetails = true;
                },

                closeShipModal() {
                    this.showShip = false;
                    this.modalError = null;
                    this.isSubmitting = false;
                },

                openShipModal(order) {
                    this.modalError = null;
                    this.isSubmitting = false;
                    this.activeOrder = order;
                    const remaining = this.calculateRemaining(order);
                    const summary = order.items && order.items.length ? order.items.map(i =>
                        `${i.quantity}x ${i.style ? i.style.name : 'منتج'}`).join(', ') : 'ملابس';

                    const custName = order.customer ? order.customer.name : (order.customer_name || '');
                    const p1 = this.normalizePhone(order.customer ? order.customer.phone_1 : (order.phone_1 || ''));
                    const p2 = this.normalizePhone(order.customer ? order.customer.phone_2 : (order.phone_2 || ''));
                    const gov = order.address ? order.address.governorate : (order.governorate || '');
                    const area = order.address ? order.address.city : '';
                    const addr = order.address ? order.address.full_address : (order.full_address || '');

                    let matchedReturnAmount = '';
                    const exactMatch = dbReturnAmounts.find(r => r.government_name === gov && r.area_name === area);
                    const govMatch = dbReturnAmounts.find(r => r.government_name === gov && !r.area_name);

                    if (exactMatch) {
                        matchedReturnAmount = exactMatch.return_amount;
                    } else if (govMatch) {
                        matchedReturnAmount = govMatch.return_amount;
                    }

                    const isShippedPaid = Boolean(order.is_shipped_paid);
                    const initialShipmentPrice = isShippedPaid ? 0 : matchedReturnAmount;

                    this.shipForm = {
                        receiver: custName,
                        api_followup_phone: '',
                        phone1: p1,
                        phone2: p2,
                        government: gov,
                        area: area,
                        address: addr,
                        is_shipped_paid: isShippedPaid,
                        order_remaining: remaining,
                        shipment_price: initialShipmentPrice,
                        amount_to_be_collected: (parseFloat(remaining || 0) + parseFloat(initialShipmentPrice || 0)).toFixed(2),
                        return_amount: matchedReturnAmount,
                        original_return_amount: matchedReturnAmount,
                        delivery_type: 0,
                        is_order: '0',
                        return_summary: '',
                        can_open: 1,
                        number_of_items: order.items ? order.items.reduce((sum, i) => sum + parseInt(i.quantity || 1), 0) : 1,
                        weight: 1,
                        is_fragile: 0,
                        order_summary: summary,
                        invoice_number: '',
                        remote_order_id: order.order_number || ''
                    };

                    this.showShip = true;
                },

                async submitShip(e) {
                    e.preventDefault();
                    if (!this.activeOrder) return;

                    this.isSubmitting = true;
                    this.modalError = null;

                    try {
                        const form = e.target;
                        const formData = new FormData(form);
                        const url = form.action;

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json().catch(() => null);

                        if (response.ok && data && data.success) {
                            this.showShip = false;
                            this.isSubmitting = false;
                            this.modalError = null;

                            const orderNum = data.order_number || (this.activeOrder ? this.activeOrder.order_number : '');
                            const orderId = data.order_id || (this.activeOrder ? this.activeOrder.id : '');
                            const turboId = data.turbo_id || '';

                            this.successData = {
                                order_id: orderId,
                                order_number: orderNum,
                                turbo_id: turboId,
                                tracking_number: data.tracking_number || '',
                                receiver: data.receiver || this.shipForm.receiver,
                                phone: data.phone || this.shipForm.phone1,
                                government: data.government || this.shipForm.government,
                                address: data.address || this.shipForm.address,
                                amount: data.amount_to_be_collected !== undefined ? data.amount_to_be_collected : (parseFloat(this.shipForm.order_remaining || 0) + parseFloat(this.shipForm.shipment_price || 0)).toFixed(2),
                                return_amount: data.return_amount || this.shipForm.return_amount,
                                shipment_url: data.shipment_url || ('{{ route("shipments.index") }}?q=' + encodeURIComponent(turboId || orderNum)),
                                order_url: data.order_url || ('{{ url("orders") }}/' + orderId)
                            };

                            this.showSuccessModal = true;
                        } else {
                            let errorMsg = 'فشل إرسال الشحنة لشركة Turbo';
                            if (data) {
                                if (data.errors && typeof data.errors === 'object') {
                                    const errorList = Object.values(data.errors).flat();
                                    if (errorList.length > 0) {
                                        errorMsg = errorList.join(' | ');
                                    } else if (data.message) {
                                        errorMsg = data.message;
                                    }
                                } else if (data.message) {
                                    errorMsg = data.message;
                                }
                            } else if (response && response.status) {
                                errorMsg = 'حدث خطأ في الخادم أثناء معالجة الطلب (كود ' + response.status + ')';
                            }
                            this.modalError = errorMsg;
                            this.isSubmitting = false;
                        }
                    } catch (err) {
                        console.error('Ship error:', err);
                        this.modalError = err.message || 'حدث خطأ غير متوقع أثناء الاتصال بشركة الشحن';
                        this.isSubmitting = false;
                    }
                },

                calculateRemaining(order) {
                    if (!order) return 0;
                    const paid = order.payments_sum_amount || (order.payments ? order.payments.reduce((acc, p) => acc +
                        parseFloat(p.amount || 0), 0) : 0);
                    return Math.max(0, parseFloat(order.total_price || 0) - paid);
                }
            };
        }
    </script>
@endsection
