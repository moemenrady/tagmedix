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

        {{-- Notifications --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms
                class="bg-emerald-50 border-r-4 border-emerald-500 p-4 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <i class="fa-solid fa-circle-check text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-emerald-900 text-sm">عملية ناجحة</h4>
                        <p class="text-emerald-700 text-xs mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms
                class="bg-rose-50 border-r-4 border-rose-500 p-4 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-rose-900 text-sm">حدث خطأ</h4>
                        <p class="text-rose-700 text-xs mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        {{-- Top KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">إجمالي الطلبات التامة</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-box-open text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">إجمالي قيمة المنتجات التامة</p>
                    <h3 class="text-2xl font-black text-gray-800">
                        {{ number_format($stats['total_value'] ?? 0, 0) }}
                        <span class="text-sm text-gray-400 font-normal">ج.م</span>
                    </h3>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-wallet text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1">المتبقي للتحصيل عند الشحن</p>
                    <h3 class="text-2xl font-black text-gray-800">
                        {{ number_format($stats['total_remaining'] ?? 0, 0) }}
                        <span class="text-sm text-gray-400 font-normal">ج.م</span>
                    </h3>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-hand-holding-dollar text-2xl"></i>
                </div>
            </div>
        </div>

        {{-- Filter & Search Header --}}
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-boxes-packing text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">الطلبات التامة الجاهزة للشحن</h2>
                    <p class="text-xs text-gray-400">قائمة بالطلبات المكتملة للتسليم أو الإرسال عبر شركة Turbo</p>
                </div>
            </div>

            <form method="GET" action="{{ route('finished-goods.index') }}" class="w-full md:w-80 flex items-center gap-2">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="ابحث برقم الطلب، العميل، أو الهاتف..."
                        class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                    <div class="absolute right-3 top-3 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                </div>
                @if (!empty($search))
                    <a href="{{ route('finished-goods.index') }}"
                        class="p-2.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition"
                        title="إلغاء البحث">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- Orders Table Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-right border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
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
                                    <div class="font-semibold text-gray-800">{{ $order->customer?->name ?? $order->customer_name }}</div>
                                    <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                        <span><i class="fa-solid fa-phone text-gray-400 ml-1"></i>{{ $order->customer?->phone_1 ?? $order->phone_1 }}</span>
                                        @if ($order->customer?->phone_2 || $order->phone_2)
                                            <span class="text-gray-300">|</span>
                                            <span>{{ $order->customer?->phone_2 ?? $order->phone_2 }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Address --}}
                                <td class="py-4 px-6 max-w-xs">
                                    <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-medium mb-1">
                                        {{ $order->address?->governorate ?? $order->governorate }}
                                    </span>
                                    <p class="text-xs text-gray-500 truncate" title="{{ $order->address?->full_address ?? $order->full_address }}">
                                        {{ $order->address?->full_address ?? $order->full_address }}
                                    </p>
                                </td>

                                {{-- Pricing --}}
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-800">
                                        {{ number_format($order->total_price, 0) }} <span class="text-xs text-gray-400 font-normal">ج.م</span>
                                    </div>
                                    <div class="text-xs text-amber-700 font-medium mt-0.5">
                                        المتبقي: {{ number_format($order->getRemainingAmount(), 0) }} ج.م
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-semibold rounded-full">
                                        <i class="fa-solid fa-circle text-[8px] text-amber-500 animate-pulse"></i>
                                        منتج تام
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Button 1: Details --}}
                                        <button type="button"
                                            @click="openDetailsModal({{ json_encode($order) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-xl transition shadow-xs">
                                            <i class="fa-solid fa-eye text-gray-500"></i>
                                            <span>عرض التفاصيل</span>
                                        </button>

                                        {{-- Button 2: Ship Order (شحن الطلب) --}}
                                        <button type="button"
                                            @click="openShipModal({{ json_encode($order) }})"
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
                                    <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3 text-gray-300">
                                        <i class="fa-solid fa-box-open text-2xl"></i>
                                    </div>
                                    <p class="text-base font-semibold text-gray-600">لا توجد طلبات تامة جاهزة للشحن حالياً</p>
                                    <p class="text-xs text-gray-400 mt-1">ستظهر الطلبات هنا فور اكتمال مرحلة التشطيب والإنتاج</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL 1: Details Modal --}}
        <div x-show="showDetails" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div @click.away="showDetails = false"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-2xl overflow-hidden transform transition-all">

                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-file-invoice text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-base" x-text="'تفاصيل الطلب: ' + (activeOrder ? activeOrder.order_number : '')"></h3>
                            <p class="text-xs text-gray-400">معلومات العميل والمنتجات التامة بالطلب</p>
                        </div>
                    </div>
                    <button @click="showDetails = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto custom-scrollbar">

                    {{-- Customer Details --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">اسم العميل</p>
                            <p class="font-bold text-gray-800" x-text="activeOrder ? (activeOrder.customer ? activeOrder.customer.name : (activeOrder.customer_name || '')) : ''"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">أرقام التواصل</p>
                            <p class="font-medium text-gray-700 text-xs" x-text="activeOrder ? ((activeOrder.customer ? activeOrder.customer.phone_1 : activeOrder.phone_1) + ((activeOrder.customer ? activeOrder.customer.phone_2 : activeOrder.phone_2) ? ' / ' + (activeOrder.customer ? activeOrder.customer.phone_2 : activeOrder.phone_2) : '')) : ''"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">المحافظة</p>
                            <p class="font-semibold text-gray-800 text-xs" x-text="activeOrder ? (activeOrder.address ? activeOrder.address.governorate : (activeOrder.governorate || '')) : ''"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">العنوان التفصيلي</p>
                            <p class="font-normal text-gray-700 text-xs" x-text="activeOrder ? (activeOrder.address ? activeOrder.address.full_address : (activeOrder.full_address || '')) : ''"></p>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-amber-600"></i>
                            <span>المنتجات بالطلب</span>
                        </h4>
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
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
                                                <td class="py-3 px-4 font-medium text-gray-800" x-text="item.style ? item.style.name : 'منتج'"></td>
                                                <td class="py-3 px-4 text-gray-600">
                                                    <span x-text="(item.type ? item.type.name : '')"></span>
                                                    <span x-show="item.color" x-text="' | ' + (item.color ? item.color.name : '')"></span>
                                                    <span x-show="item.size" x-text="' | ' + (item.size ? item.size.name : '')"></span>
                                                </td>
                                                <td class="py-3 px-4 text-center font-bold text-gray-800" x-text="item.quantity"></td>
                                                <td class="py-3 px-4 text-left text-gray-600" x-text="item.price + ' ج.م'"></td>
                                                <td class="py-3 px-4 text-left font-bold text-gray-900" x-text="item.total + ' ج.م'"></td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="flex items-center justify-between p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                        <div>
                            <span class="text-xs text-gray-500 block">إجمالي سعر الطلب</span>
                            <span class="text-lg font-black text-gray-800" x-text="activeOrder ? activeOrder.total_price + ' ج.م' : ''"></span>
                        </div>
                        <div class="text-left">
                            <span class="text-xs text-gray-500 block">المبلغ المتبقي للتحصيل</span>
                            <span class="text-lg font-black text-amber-700" x-text="activeOrder ? calculateRemaining(activeOrder) + ' ج.م' : ''"></span>
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <button @click="showDetails = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-xl transition">
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

        {{-- MODAL 2: Ship Order Form Modal --}}
        <div x-show="showShip" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div @click.away="showShip = false"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden transform transition-all my-8">

                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-amber-700 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-white">
                            <i class="fa-solid fa-paper-plane text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg" x-text="'شحن الطلب (' + (shipForm.remote_order_id || '') + ')'"></h3>
                            <p class="text-xs text-amber-100">استكمال بيانات الشحنة للتحويل لشركة الشحن</p>
                        </div>
                    </div>
                    <button @click="showShip = false" class="text-amber-100 hover:text-white p-2 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- Form Content --}}
                <form :action="'{{ url('finished-goods') }}/' + (activeOrder ? activeOrder.id : '') + '/ship'" method="POST" @submit="isSubmitting = true">
                    @csrf

                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">

                        {{-- الراسل الفرعي (تثبيت دون إمكانية التعديل) --}}
                        <div class="bg-gray-100 p-3 rounded-xl text-xs font-bold text-gray-700 flex items-center justify-between">
                            <span>الراسل الفرعي :</span>
                            <span class="text-amber-700 font-extrabold">Tagmedix</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- المرسل اليه --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">المرسل اليه <span class="text-rose-500">*</span></label>
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
                                <label class="block text-xs font-semibold text-gray-700 mb-1">تلفيون المرسل اليه ١ <span class="text-rose-500">*</span></label>
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
                                <label class="block text-xs font-semibold text-gray-700 mb-1">الموقع <span class="text-rose-500">*</span></label>
                                <input type="text" name="government" x-model="shipForm.government" placeholder="مثال: مصر-المنوفيه-الباجور" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- المطلوب تحصيله شامل الشحن --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">المطلوب تحصيله شامل الشحن <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="amount_to_be_collected" x-model="shipForm.amount_to_be_collected" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-amber-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- في حاله الارتجاع نطالب المستلم ب --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">في حاله الارتجاع نطالب المستلم ب <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="return_amount" x-model="shipForm.return_amount" placeholder="سعر الشحن" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- هل سيستلم الكابتن طرد من المرسل اليه --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">هل سيستلم الكابتن طرد من المرسل اليه <span class="text-rose-500">*</span></label>
                                <select name="delivery_type" x-model="shipForm.delivery_type" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                    <option value="0">تسليم</option>
                                    <option value="1">استبدال</option>
                                    <option value="2">استرجاع</option>
                                    <option value="3">مرتجع-جزئي</option>
                                </select>
                            </div>

                            {{-- محتوي الطرد --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">محتوي الطرد <span class="text-rose-500">*</span></label>
                                <input type="text" name="order_summary" x-model="shipForm.order_summary" placeholder="ملابس" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- رقم الفاتوره --}}
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">رقم الفاتوره</label>
                                <input type="text" name="invoice_number" x-model="shipForm.invoice_number"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            </div>

                            {{-- العنوان --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">العنوان <span class="text-rose-500">*</span></label>
                                <textarea name="address" x-model="shipForm.address" rows="2" placeholder="تفاصيل العنوان" required
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
                            </div>

                        </div>

                        {{-- Hidden inputs to maintain payload compatibility --}}
                        <input type="hidden" name="area" x-model="shipForm.area">
                        <input type="hidden" name="remote_order_id" x-model="shipForm.remote_order_id">
                        <input type="hidden" name="weight" value="1">
                        <input type="hidden" name="is_order" value="0">
                        <input type="hidden" name="is_fragile" value="0">

                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <button type="button" @click="showShip = false"
                            class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-xl transition">
                            إلغاء
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                            class="px-6 py-2.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white text-xs font-bold rounded-xl transition shadow-md hover:shadow-lg flex items-center gap-2 disabled:opacity-50">
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
                    amount_to_be_collected: 0,
                    return_amount: 0,
                    delivery_type: 0,
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
                    const summary = order.items && order.items.length ? order.items.map(i => `${i.quantity}x ${i.style ? i.style.name : 'منتج'}`).join(', ') : 'ملابس';

                    const custName = order.customer ? order.customer.name : (order.customer_name || '');
                    const p1 = order.customer ? order.customer.phone_1 : (order.phone_1 || '');
                    const p2 = order.customer ? order.customer.phone_2 : (order.phone_2 || '');
                    const gov = order.address ? order.address.governorate : (order.governorate || '');
                    const government = order.address
                        ? (order.address.turbo_government || (gov ? `مصر-${gov}` : ''))
                        : (gov ? `مصر-${gov}` : '');
                    const area = order.address ? (order.address.turbo_area || '') : '';
                    const addr = order.address ? order.address.full_address : (order.full_address || '');

                    this.shipForm = {
                        receiver: custName,
                        api_followup_phone: '',
                        phone1: p1,
                        phone2: p2,
                        government: government,
                        area: area,
                        address: addr,
                        amount_to_be_collected: remaining,
                        return_amount: 0,
                        delivery_type: 0,
                        order_summary: summary,
                        invoice_number: '',
                        remote_order_id: order.order_number || ''
                    };

                    this.showShip = true;
                },

                calculateRemaining(order) {
                    if (!order) return 0;
                    const paid = order.payments_sum_amount || (order.payments ? order.payments.reduce((acc, p) => acc + parseFloat(p.amount || 0), 0) : 0);
                    return Math.max(0, parseFloat(order.total_price || 0) - paid);
                }
            };
        }
    </script>
@endsection