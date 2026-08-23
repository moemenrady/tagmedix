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

    <div dir="rtl" class="space-y-6 pb-12 font-sans" x-data="finishedGoodsApp()">

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
        <div class="grid grid-cols-1 gap-4 md:hidden">
            @forelse ($orders as $order)
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-3 relative">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="font-bold text-gray-900 text-sm">{{ $order->order_number }}</span>
                        </div>
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-semibold rounded-full">
                            <i class="fa-solid fa-circle text-[6px] text-amber-500 animate-pulse"></i>
                            منتج تام
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-start">
                            <span class="text-gray-400 shrink-0">العميل:</span>
                            <span
                                class="font-bold text-gray-800 text-left">{{ $order->customer?->name ?? $order->customer_name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">الهاتف:</span>
                            <span
                                class="text-gray-700 dir-ltr font-medium">{{ $order->customer?->phone_1 ?? $order->phone_1 }}</span>
                        </div>
                        <div class="flex justify-between items-start">
                            <span class="text-gray-400 shrink-0">العنوان:</span>
                            <div class="text-left max-w-[200px]">
                                <span
                                    class="inline-block px-1.5 py-0.5 bg-gray-100 text-gray-700 text-[10px] rounded font-medium mb-0.5">
                                    {{ $order->address?->governorate ?? $order->governorate }}
                                </span>
                                <p class="text-gray-500 truncate text-[11px]">
                                    {{ $order->address?->full_address ?? $order->full_address }}</p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-gray-50">
                            <span class="text-gray-400">إجمالي الطلب:</span>
                            <span class="font-bold text-gray-900">{{ number_format($order->total_price, 0) }} ج.م</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">المتبقي للتحصيل:</span>
                            <span class="font-bold text-amber-700">{{ number_format($order->getRemainingAmount(), 0) }}
                                ج.م</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="openDetailsModal({{ json_encode($order) }})"
                            class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-eye text-gray-500"></i>
                            <span>عرض التفاصيل</span>
                        </button>
                        <button type="button" @click="openShipModal({{ json_encode($order) }})"
                            class="w-full py-2 bg-gradient-to-r from-amber-600 to-amber-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-truck-fast"></i>
                            <span>شحن الطلب</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl border border-gray-100 text-center text-gray-400">
                    <i class="fa-solid fa-box-open text-3xl mb-2 text-gray-300"></i>
                    <p class="text-sm font-semibold text-gray-600">لا توجد طلبات تامة جاهزة للشحن حالياً</p>
                </div>
            @endforelse
        </div>

        {{-- Desktop View: Orders Table Card (شاشة الديسكتوب) --}}
        <div class="hidden md:block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-right border-collapse min-w-[900px]">
                    <thead>
                        <tr
                            class="bg-gray-50/80 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
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
                            <tr class="hover:bg-amber-50/30 transition duration-150">
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
                                    <div class="font-semibold text-gray-800">
                                        {{ $order->customer?->name ?? $order->customer_name }}</div>
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
                                <td colspan="6" class="py-12 text-center text-gray-400">
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
        <div x-show="showShip" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-2 sm:p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="showShip = false"
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
                    <button @click="showShip = false" class="text-amber-100 hover:text-white p-1 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- Form Content --}}
                <form :action="'{{ url('finished-goods') }}/' + (activeOrder ? activeOrder.id : '') + '/ship'"
                    method="POST" @submit="isSubmitting = true" class="flex flex-col flex-1 overflow-hidden">
                    @csrf

                    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto custom-scrollbar flex-1">

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
                                            <template x-if="shipForm.original_return_amount !== '' && shipForm.shipment_price == shipForm.original_return_amount">
                                                <span class="text-[9px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i>
                                                    تلقائي
                                                </span>
                                            </template>
                                        </div>
                                        <div class="relative">
                                            <input type="number" step="0.01" x-model="shipForm.shipment_price" @input="shipForm.return_amount = shipForm.shipment_price" placeholder="تكلفة الشحن" required
                                                class="w-full pl-20 pr-3 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold shadow-sm transition-all text-gray-700"
                                                :class="{'border-emerald-500 ring-1 ring-emerald-500': shipForm.original_return_amount !== '' && shipForm.shipment_price == shipForm.original_return_amount}">
                                            <div class="absolute left-2 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                                <template x-if="shipForm.original_return_amount !== '' && shipForm.shipment_price != shipForm.original_return_amount">
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
                        <button type="button" @click="showShip = false"
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

    </div>

    <script>
        function finishedGoodsApp() {
            const dbReturnAmounts = @json($returnAmounts ?? []);

            return {
                showDetails: false,
                showShip: false,
                isSubmitting: false,
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

                openDetailsModal(order) {
                    this.activeOrder = order;
                    this.showDetails = true;
                },

                openShipModal(order) {
                    this.activeOrder = order;
                    const remaining = this.calculateRemaining(order);
                    const summary = order.items && order.items.length ? order.items.map(i =>
                        `${i.quantity}x ${i.style ? i.style.name : 'منتج'}`).join(', ') : 'ملابس';

                    const custName = order.customer ? order.customer.name : (order.customer_name || '');
                    const p1 = order.customer ? order.customer.phone_1 : (order.phone_1 || '');
                    const p2 = order.customer ? order.customer.phone_2 : (order.phone_2 || '');
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

                    this.shipForm = {
                        receiver: custName,
                        api_followup_phone: '',
                        phone1: p1,
                        phone2: p2,
                        government: gov,
                        area: area,
                        address: addr,
                        order_remaining: remaining,
                        shipment_price: matchedReturnAmount,
                        amount_to_be_collected: remaining,
                        return_amount: matchedReturnAmount,
                        original_return_amount: matchedReturnAmount,
                        delivery_type: 0,
                        is_order: '0',
                        return_summary: '',
                        can_open: 1,
                        number_of_items: order.items ? order.items.reduce((sum, i) => sum + parseInt(i.quantity || 1),
                            0) : 1,
                        weight: 1,
                        is_fragile: 0,
                        order_summary: summary,
                        invoice_number: '',
                        remote_order_id: order.order_number || ''
                    };

                    this.showShip = true;
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
