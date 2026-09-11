@extends('layouts.app')

@section('title', 'تفاصيل الطلب | Order Details')

@section('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @php
        $customer = $order->customer;
        $address = $order->address;

        $initialData = [
            'orderId' => $order->id,
            'totalPrice' => $order->total_price,
            'paidAmount' => (float) $order->getTotalPaid(),
            'remainingAmount' => (float) $order->getRemainingAmount(),
            'status' => $order->status,
            'customer' => [
                'name' => $customer?->name ?? ($order->customer_name ?? 'غير محدد'),
                'phone1' => $customer?->phone_1 ?? ($order->phone_1 ?? ''),
                'phone2' => $customer?->phone_2 ?? ($order->phone_2 ?? ''),
                'gov' => $address?->governorate ?? ($order->governorate ?? 'مصر'),
                'address' => $address?->full_address ?? ($order->full_address ?? ''),
                'insta' => $customer?->instagram_profile ?? ($order->instagram_profile ?? ''),
            ],
            'items' => $order->items->toArray(),
            'dictionaries' => [
                'colors' => $colors ?? [],
                'sizes' => $sizes ?? [],
                'styles' => $styles ?? [],
                'types' => $types ?? [],
                'pantTypes' => $pantTypes ?? [],
                'sleeveTypes' => $sleeveTypes ?? [],
            ],
        ];
    @endphp

    <div class="font-sans antialiased text-gray-900 pb-12 space-y-6" x-data="orderDashboard(@js($initialData))">

        {{-- إشعار التحديث الذكي --}}
        <div x-show="notification.show" x-transition.opacity.duration.300ms x-cloak
            class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[100] bg-gray-900/95 backdrop-blur-sm text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 border"
            :class="notification.type === 'success' ? 'border-emerald-500' : 'border-red-500'">
            <i class="fa-solid text-lg"
                :class="notification.type === 'success' ? 'fa-circle-check text-emerald-400' :
                    'fa-triangle-exclamation text-red-400'"></i>
            <span x-text="notification.message" class="text-sm font-bold"></span>
        </div>

        {{-- شريط العنوان والإجراءات --}}
        <div
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row items-center justify-between gap-4 transition-all hover:shadow-md">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <a href="{{ route('orders.index') }}"
                    class="w-10 h-10 shrink-0 rounded-xl bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-900 transition-all rtl:rotate-0 ltr:rotate-180">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $order->order_number }}</h1>

                        {{-- تعديل الحالة --}}
                        <div class="relative" @click.away="statusOpen = false">
                            <button @click="statusOpen = !statusOpen"
                                class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-bold border transition-all hover:shadow-md cursor-pointer"
                                :class="currentStatusObj.color">
                                <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                <span x-text="currentStatusObj.labelAr"></span>
                                <i class="fa-solid fa-chevron-down text-[10px] ml-1 opacity-70"></i>
                            </button>
                            <div x-show="statusOpen" x-transition x-cloak
                                class="absolute top-full rtl:right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-30 overflow-hidden">
                                <template x-for="st in statuses" :key="st.value">
                                    <button @click="updateStatus(st.value)"
                                        class="w-full text-start px-4 py-2.5 text-xs font-bold hover:bg-gray-50 transition-colors border-b border-gray-50 flex items-center gap-2"
                                        :class="status === st.value ? 'bg-amber-50 text-amber-700' : 'text-gray-600'">
                                        <div class="w-2 h-2 rounded-full" :class="st.color.split(' ')[0]"></div>
                                        <span x-text="st.labelAr"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                        <i class="fa-regular fa-clock"></i>
                        <span>تاريخ الطلب: {{ $order->created_at->format('Y-m-d h:i A') }}</span>
                    </p>
                </div>
            </div>
            <div class="flex w-full md:w-auto gap-2">
                <button
                    class="w-full md:w-auto bg-gray-50 hover:bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 border border-gray-200 shadow-sm">
                    <i class="fa-solid fa-print"></i> <span>طباعة الفاتورة</span>
                </button>
            </div>
        </div>

        {{-- كارت بيانات الشحن لشركة Turbo --}}
        @php
            $latestShipment = $order->shipments ? $order->shipments->sortByDesc('id')->first() : null;
        @endphp

        @if ($latestShipment || $order->status === 'shipped')
            <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-white border border-amber-200 rounded-2xl p-5 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-700 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-black text-gray-900 text-base">تمت إضافة الطلب للشحنات (شركة Turbo)</h3>
                                <span id="order_turbo_status_badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check text-xs"></i> {{ $latestShipment?->status_text ?? 'تم شحن الطلب' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                <i class="fa-regular fa-clock"></i>
                                <span>تاريخ الشحن: {{ $latestShipment?->shipped_at ? \Carbon\Carbon::parse($latestShipment->shipped_at)->format('Y-m-d h:i A') : $order->updated_at->format('Y-m-d h:i A') }}</span>
                            </p>
                        </div>
                    </div>

                    @if ($latestShipment)
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="bg-white px-3.5 py-2 rounded-xl border border-gray-200 text-xs shadow-xs">
                                <span class="text-gray-400 block text-[10px] font-bold">كود الشحنة (Turbo ID)</span>
                                <span class="font-black font-mono text-gray-900 text-sm">{{ $latestShipment->turbo_id ?: 'تم التسجيل' }}</span>
                            </div>
                            @if ($latestShipment->tracking_number)
                                <div class="bg-white px-3.5 py-2 rounded-xl border border-gray-200 text-xs shadow-xs">
                                    <span class="text-gray-400 block text-[10px] font-bold">باركود الشحنة</span>
                                    <span class="font-black font-mono text-gray-700 text-sm">{{ $latestShipment->tracking_number }}</span>
                                </div>
                            @endif
                            <div class="bg-white px-3.5 py-2 rounded-xl border border-amber-200 text-xs shadow-xs">
                                <span class="text-amber-700 block text-[10px] font-bold">المطلوب تحصيله</span>
                                <span class="font-black font-mono text-amber-900 text-sm">{{ number_format($latestShipment->amount, 2) }} ج.م</span>
                            </div>
                            @if ($latestShipment->return_amount)
                                <div class="bg-white px-3.5 py-2 rounded-xl border border-rose-100 text-xs shadow-xs">
                                    <span class="text-rose-600 block text-[10px] font-bold">سعر الارتجاع</span>
                                    <span class="font-black font-mono text-rose-700 text-sm">{{ number_format($latestShipment->return_amount, 2) }} ج.م</span>
                                </div>
                            @endif
                            <button type="button" onclick="syncOrderShipment({{ $latestShipment->id }}, this)"
                                class="px-3.5 py-2.5 bg-purple-50 hover:bg-purple-100 text-[#31006f] text-xs font-bold rounded-xl border border-purple-200 transition shadow-2xs flex items-center gap-2 cursor-pointer"
                                title="تحديث فوري من Turbo">
                                <i class="fa-solid fa-rotate text-xs"></i>
                                <span>تحديث من Turbo</span>
                            </button>
                            <a href="{{ route('shipments.index', ['q' => $latestShipment->turbo_id ?: $order->order_number]) }}"
                                class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-2">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                <span>عرض في الشحنات</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- شبكة المعلومات --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 1. كارت بيانات العميل --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 relative group">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-user-tie text-lg"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">بيانات العميل</h3>
                    </div>
                    <button @click="editMode.customer = !editMode.customer"
                        class="text-gray-400 hover:text-[#8A704C] transition p-2 bg-gray-50 rounded-lg">
                        <i class="fa-solid" :class="editMode.customer ? 'fa-xmark text-red-500' : 'fa-pen'"></i>
                    </button>
                </div>

                {{-- وضع العرض --}}
                <div x-show="!editMode.customer" class="space-y-4">
                    <div>
                        <p class="text-[11px] text-gray-400 font-bold mb-1">الاسم</p>
                        <p class="text-sm font-black text-gray-800" x-text="customer.name"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-bold mb-1">الهاتف الأساسي</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-gray-700 font-mono" x-text="customer.phone1"
                                    dir="ltr"></p>
                                <a x-show="customer.phone1" :href="'https://wa.me/' + customer.phone1.replace(/\D/g, '')"
                                    target="_blank" class="text-green-500 hover:text-green-600"><i
                                        class="fa-brands fa-whatsapp text-lg"></i></a>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100" x-show="customer.phone2">
                            <p class="text-[10px] text-gray-400 font-bold mb-1">الهاتف البديل</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-gray-700 font-mono" x-text="customer.phone2"
                                    dir="ltr"></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <i class="fa-solid fa-location-dot mt-0.5 text-gray-400"></i>
                        <div>
                            <p class="text-[11px] text-gray-400 font-bold mb-0.5">العنوان</p>
                            <p class="text-xs font-bold text-gray-700 leading-relaxed">
                                <span x-text="customer.gov" class="text-[#8A704C]"></span> - <span
                                    x-text="customer.address"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- وضع التعديل --}}
                <div x-show="editMode.customer" x-cloak class="space-y-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500">الاسم</label>
                        <input type="text" x-model="customer.name"
                            class="w-full text-sm border-gray-200 rounded-xl focus:ring-[#8A704C]">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500">هاتف 1</label>
                            <input type="text" x-model="customer.phone1"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-[#8A704C]">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500">هاتف 2</label>
                            <input type="text" x-model="customer.phone2"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-[#8A704C]">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-1">
                            <label class="text-[10px] font-bold text-gray-500">المحافظة</label>
                            <input type="text" x-model="customer.gov"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-[#8A704C]">
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] font-bold text-gray-500">العنوان التفصيلي</label>
                            <input type="text" x-model="customer.address"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-[#8A704C]">
                        </div>
                    </div>
                    <button @click="saveCustomerInfo()"
                        class="w-full bg-[#8A704C] text-white rounded-xl py-2.5 text-sm font-bold hover:bg-[#725c3e] transition shadow-md flex items-center justify-center gap-2 mt-2">
                        <i class="fa-solid fa-floppy-disk"></i> <span>حفظ التعديلات</span>
                    </button>
                </div>
            </div>

            {{-- 2. كارت الحسابات والمالية --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-wallet text-lg"></i>
                            </div>
                            <h3 class="font-bold text-gray-800">الملخص المالي</h3>
                        </div>
                        <button @click="editMode.financials = !editMode.financials"
                            class="text-gray-400 hover:text-emerald-600 transition p-2 bg-gray-50 rounded-lg">
                            <i class="fa-solid" :class="editMode.financials ? 'fa-xmark text-red-500' : 'fa-pen'"></i>
                        </button>
                    </div>

                    <div class="space-y-3" x-show="!editMode.financials">
                        <div class="flex justify-between items-center p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-xs font-bold text-gray-500">إجمالي الفاتورة</span>
                            <span class="text-lg font-black text-gray-900 font-mono"
                                x-text="formatMoney(totalPrice) + ' ج.م'"></span>
                        </div>
                        <div
                            class="flex justify-between items-center p-3.5 bg-green-50 rounded-xl border border-green-200">
                            <span class="text-xs font-bold text-green-700"><i
                                    class="fa-solid fa-money-bill-wave ml-1"></i> إجمالي المدفوع</span>
                            <span class="text-lg font-black text-green-700 font-mono"
                                x-text="formatMoney(paidAmount) + ' ج.م'"></span>
                        </div>
                    </div>

                    {{-- وضع تعديل الماليات --}}
                    <div x-show="editMode.financials" x-cloak
                        class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500">تعديل الإجمالي المدفوع (جنيه)</label>
                            <input type="number" x-model="paidAmountEdit"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-emerald-500 mt-1 font-mono">
                        </div>
                        <button @click="saveFinancials()"
                            class="w-full bg-emerald-600 text-white rounded-xl py-2.5 text-sm font-bold hover:bg-emerald-700 transition">
                            حفظ المدفوعات
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex justify-between items-center p-4 rounded-xl border transition-colors"
                    :class="remainingAmount > 0 ? 'bg-rose-50 border-rose-200' : 'bg-gray-50 border-gray-200'">
                    <span class="text-sm font-black"
                        :class="remainingAmount > 0 ? 'text-rose-700' : 'text-gray-500'">المتبقي للتحصيل</span>
                    <span class="text-xl font-black font-mono"
                        :class="remainingAmount > 0 ? 'text-rose-700' : 'text-gray-900'"
                        x-text="formatMoney(remainingAmount) + ' ج.م'"></span>
                </div>
            </div>

            {{-- 3. معلومات الطلب --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fa-solid fa-circle-info text-lg"></i>
                    </div>
                    <h3 class="font-bold text-gray-800">معلومات الطلب</h3>
                </div>
                <div class="space-y-6">
                    <div>
                        <p class="text-xs text-gray-400 font-bold mb-2">مصدر الطلب</p>
                        @php
                            $sourceConfig = [
                                'new' => [
                                    'color' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'ar' => 'طلب جديد / تفصيل',
                                ],
                                'from_stock' => [
                                    'color' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'ar' => 'من المخزون مباشر',
                                ],
                                'replacement' => [
                                    'color' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'ar' => 'استبدال',
                                ],
                            ];
                            $config = $sourceConfig[$order->source] ?? [
                                'color' => 'bg-gray-100 text-gray-800',
                                'ar' => $order->source,
                            ];
                        @endphp
                        <span
                            class="px-3 py-1.5 rounded-lg text-xs font-bold border {{ $config['color'] }}">{{ $config['ar'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                <i class="fa-solid fa-boxes-stacked text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-600">إجمالي القطع</span>
                        </div>
                        <span class="text-2xl font-black text-gray-900"
                            x-text="items.reduce((sum, i) => sum + Number(i.quantity), 0)"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- محتويات الطلب (المنتجات) - التصميم الجديد مع زر التعديل الشامل --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative">
            <div
                class="p-5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center shadow-inner">
                        <i class="fa-solid fa-shirt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">محتويات الطلب</h3>
                        <p class="text-xs text-gray-400">المنتجات المسجلة في هذا الطلب وتفاصيلها</p>
                    </div>
                </div>

                {{-- الزر السحري لفتح الموديل --}}
                <button @click="openItemsModal()"
                    class="w-full sm:w-auto bg-[#8A704C] hover:bg-[#725c3e] text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition-all flex items-center justify-center gap-2 shadow-sm transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-pen-ruler"></i> تعديل تفصيلي للمنتجات
                </button>
            </div>

            <div class="p-5 grid grid-cols-1 gap-5">

                <template x-for="(item, index) in items" :key="index">

                    <div
                        class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-all">

                        <!-- Header -->
                        <div
                            class="px-5 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-12 h-12 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-[#8A704C] shadow-sm">

                                    <i class="fa-solid fa-shirt text-lg"></i>

                                </div>

                                <div>

                                    <p class="text-base font-black text-gray-800">
                                        المنتج رقم
                                        <span x-text="index + 1"></span>
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        تفاصيل المنتج والطلب
                                    </p>

                                </div>

                            </div>

                            <!-- Total -->
                            <div class="text-right sm:text-left">

                                <p class="text-xs text-gray-400 font-bold mb-1">
                                    إجمالي العنصر
                                </p>

                                <p class="text-lg font-black text-emerald-600 font-mono"
                                    x-text="formatMoney(item.quantity * item.price)">
                                </p>

                            </div>

                        </div>


                        <!-- Product Details -->
                        <div class="p-5">

                            <div class="flex items-center gap-2 mb-4">

                                <div
                                    class="w-8 h-8 rounded-lg bg-[#8A704C]/10 flex items-center justify-center text-[#8A704C]">

                                    <i class="fa-solid fa-sliders"></i>

                                </div>

                                <h3 class="text-base font-black text-gray-800">
                                    تفاصيل المنتج
                                </h3>

                            </div>


                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">


                                <!-- النوع -->
                                <div x-show="item.type || item.type_id"
                                    class="rounded-xl border border-violet-100 bg-violet-50/40 p-4">

                                    <div class="flex items-center gap-2 mb-2">

                                        <i class="fa-solid fa-layer-group text-violet-600"></i>

                                        <span class="text-xs font-bold text-violet-600">
                                            النوع
                                        </span>

                                    </div>

                                    <p class="text-base font-black text-gray-800"
                                        x-text="item.type?.name || getLabel('types', item.type_id) || '-'">
                                    </p>

                                </div>


                                <!-- الاستايل -->
                                <div x-show="item.style || item.style_id"
                                    class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">

                                    <div class="flex items-center gap-2 mb-2">

                                        <i class="fa-solid fa-shirt text-indigo-600"></i>

                                        <span class="text-xs font-bold text-indigo-600">
                                            الاستايل
                                        </span>

                                    </div>

                                    <p class="text-base font-black text-gray-800"
                                        x-text="item.style?.name || getLabel('styles', item.style_id) || '-'">
                                    </p>

                                </div>


                                <!-- اللون -->
                                <div x-show="item.color || item.color_id"
                                    class="rounded-xl border border-rose-100 bg-rose-50/40 p-4">

                                    <div class="flex items-center gap-2 mb-2">

                                        <i class="fa-solid fa-palette text-rose-600"></i>

                                        <span class="text-xs font-bold text-rose-600">
                                            اللون
                                        </span>

                                    </div>

                                    <p class="text-base font-black text-gray-800"
                                        x-text="item.color?.name || getLabel('colors', item.color_id) || '-'">
                                    </p>

                                </div>


                                <!-- المقاس -->
                                <div x-show="item.size || item.size_id"
                                    class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">

                                    <div class="flex items-center gap-2 mb-2">

                                        <i class="fa-solid fa-ruler-combined text-blue-600"></i>

                                        <span class="text-xs font-bold text-blue-600">
                                            المقاس
                                        </span>

                                    </div>

                                    <p class="text-base font-black text-gray-800"
                                        x-text="item.size?.name || getLabel('sizes', item.size_id) || '-'">
                                    </p>

                                </div>


                                <!-- نوع البنطلون -->
                                <div x-show="item.pant_type || item.pant_type_id"
                                    class="rounded-xl border border-cyan-100 bg-cyan-50/40 p-4">

                                    <div class="flex items-center gap-2 mb-2">

                                        <i class="fa-solid fa-person text-cyan-600"></i>

                                        <span class="text-xs font-bold text-cyan-600">
                                            نوع البنطلون
                                        </span>

                                    </div>

                                    <p class="text-base font-black text-gray-800"
                                        x-text="item.pant_type?.name || getLabel('pantTypes', item.pant_type_id) || '-'">
                                    </p>

                                </div>


                                <!-- نوع الكم -->
                                <div x-show="item.sleeve_type || item.sleeve_type_id"
                                    class="rounded-xl border border-orange-100 bg-orange-50/40 p-4">

                                    <div class="flex items-center gap-2 mb-2">

                                        <i class="fa-solid fa-arrows-up-down text-orange-600"></i>

                                        <span class="text-xs font-bold text-orange-600">
                                            نوع الكم
                                        </span>

                                    </div>

                                    <p class="text-base font-black text-gray-800"
                                        x-text="item.sleeve_type?.name || getLabel('sleeveTypes', item.sleeve_type_id) || '-'">
                                    </p>

                                </div>

                            </div>


                            <!-- Quantity / Price -->
                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">


                                <!-- الكمية -->
                                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">

                                    <p class="text-xs text-gray-400 font-bold mb-2">
                                        الكمية
                                    </p>

                                    <div class="flex items-center gap-2">

                                        <i class="fa-solid fa-boxes-stacked text-gray-500"></i>

                                        <span class="text-xl font-black text-gray-800" x-text="item.quantity">
                                        </span>

                                        <span class="text-xs text-gray-400">
                                            قطعة
                                        </span>

                                    </div>

                                </div>


                                <!-- سعر الوحدة -->
                                <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">

                                    <p class="text-xs text-gray-400 font-bold mb-2">
                                        سعر الوحدة
                                    </p>

                                    <div class="flex items-center gap-2">

                                        <i class="fa-solid fa-tag text-gray-500"></i>

                                        <span class="text-lg font-black text-gray-800 font-mono"
                                            x-text="formatMoney(item.price)">
                                        </span>

                                    </div>

                                </div>


                                <!-- الإجمالي -->
                                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4">

                                    <p class="text-xs text-emerald-600 font-bold mb-2">
                                        إجمالي المنتج
                                    </p>

                                    <div class="flex items-center gap-2">

                                        <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>

                                        <span class="text-lg font-black text-emerald-700 font-mono"
                                            x-text="formatMoney(item.quantity * item.price)">
                                        </span>

                                    </div>

                                </div>

                            </div>


                            <!-- Notes -->
                            <div x-show="item.notes" class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-4">

                                <div class="flex items-start gap-3">

                                    <div class="w-9 h-9 shrink-0 rounded-lg bg-amber-100 flex items-center justify-center">

                                        <i class="fa-solid fa-note-sticky text-amber-600"></i>

                                    </div>

                                    <div>

                                        <p class="text-xs font-black text-amber-700 mb-1">
                                            ملاحظات المنتج
                                        </p>

                                        <p class="text-sm font-bold text-gray-700 leading-6" x-text="item.notes">
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </template>


                <!-- Empty -->
                <div x-show="items.length === 0" class="p-10 text-center text-gray-400">

                    <i class="fa-solid fa-box-open text-5xl mb-4 opacity-20"></i>

                    <p class="text-sm font-bold">
                        لا توجد منتجات.
                        اضغط على تعديل تفصيلي للإضافة.
                    </p>

                </div>

            </div>
        </div>

        {{-- الموديل (Modal) للتعديل التفصيلي مثل صفحة الإنشاء --}}
        <div x-show="isItemsModalOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- الخلفية الضبابية --}}
            <div x-show="isItemsModalOpen" x-transition.opacity
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                @click="isItemsModalOpen = false"></div>

            {{-- محتوى الموديل --}}
            <div x-show="isItemsModalOpen" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-gray-50 rounded-2xl shadow-2xl overflow-hidden w-full max-w-5xl max-h-[90vh] flex flex-col transform transition-all">

                {{-- هيدر الموديل --}}
                <div class="px-6 py-4 bg-white border-b border-gray-100 flex items-center justify-between z-10 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center">
                            <i class="fa-solid fa-sliders text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">تعديل منتجات ومحتويات الطلب</h3>
                            <p class="text-xs text-gray-500">تحكم كامل في العناصر (إضافة، حذف، تعديل)</p>
                        </div>
                    </div>
                    <button @click="isItemsModalOpen = false"
                        class="text-gray-400 hover:text-red-500 bg-gray-50 hover:bg-red-50 p-2.5 rounded-xl transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- جسم الموديل (قابل للتمرير) --}}
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5 custom-scrollbar">
                    <template x-for="(item, index) in editItems" :key="index">
                        <div
                            class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm relative transition-all group">

                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-50">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-8 h-8 rounded-lg bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-sm font-bold shadow-inner"
                                        x-text="index + 1"></span>
                                    <h4 class="text-base font-bold text-gray-800">تفاصيل القطعة</h4>
                                </div>
                                <button @click="removeEditItem(index)" type="button"
                                    class="text-gray-400 hover:text-red-600 hover:bg-red-50 w-8 h-8 rounded-xl flex items-center justify-center transition-all"
                                    title="إلغاء المنتج">
                                    <i class="fa-solid fa-trash-can text-lg"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                {{-- نوع البنطلون --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع البنطلون <span
                                            class="text-red-500">*</span></label>
                                    <select x-model="item.pant_type_id"
                                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] text-sm font-medium cursor-pointer">
                                        <option value="">اختر نوع البنطلون</option>
                                        <template x-for="opt in dictionaries.pantTypes" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                </div>
                                {{-- نوع المنتج --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع المنتج <span
                                            class="text-red-500">*</span></label>
                                    <select x-model="item.type_id"
                                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] text-sm font-medium cursor-pointer">
                                        <option value="">اختر نوع المنتج</option>
                                        <template x-for="opt in dictionaries.types" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                </div>
                                {{-- الستايل --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-2">الستايل</label>
                                    <select x-model="item.style_id"
                                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] text-sm font-medium cursor-pointer">
                                        <option value="">اختر الستايل</option>
                                        <template x-for="opt in dictionaries.styles" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                </div>
                                {{-- اللون --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-2">اللون</label>
                                    <select x-model="item.color_id"
                                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] text-sm font-medium cursor-pointer">
                                        <option value="">اختر اللون</option>
                                        <template x-for="opt in dictionaries.colors" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                </div>
                                {{-- المقاس --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-2">المقاس</label>
                                    <select x-model="item.size_id"
                                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] text-sm font-medium cursor-pointer">
                                        <option value="">اختر المقاس</option>
                                        <template x-for="opt in dictionaries.sizes" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                </div>
                                {{-- نوع الكم --}}
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع الكم</label>
                                    <select x-model="item.sleeve_type_id"
                                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] text-sm font-medium cursor-pointer">
                                        <option value="">اختر نوع الكم</option>
                                        <template x-for="opt in dictionaries.sleeveTypes" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- الحسابات --}}
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label class="block text-xs font-bold text-gray-600 mb-3">الحسابات (الكمية - السعر -
                                        الإجمالي)</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-500 mb-2">الكمية</label>
                                            <input type="number" x-model="item.quantity" min="1"
                                                class="w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-500 mb-2">سعر
                                                القطعة</label>
                                            <input type="number" step="0.01" x-model="item.price" min="0"
                                                class="w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                                                dir="ltr">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-emerald-600 mb-2">الإجمالي</label>
                                            <div
                                                class="w-full h-[52px] flex items-center justify-center bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-lg font-black font-mono">
                                                <span x-text="formatMoney(item.quantity * item.price)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ملاحظات --}}
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label class="block text-xs font-bold text-gray-500 mb-2">ملاحظات خاصة ومقاسات مخصصة
                                        لهذه القطعة (إن وجد)</label>
                                    <input type="text" x-model="item.notes"
                                        placeholder="أدخل أي ملاحظات للتفصيل أو التعديل هنا..."
                                        class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- زر إضافة قطعة داخل الموديل --}}
                    <div class="flex justify-center mt-4 pb-4">
                        <button @click="addNewEditItem()" type="button"
                            class="bg-gray-900 hover:bg-gray-800 text-white font-semibold py-3 px-6 rounded-xl text-sm transition-all flex items-center justify-center gap-2 shadow-md transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-circle-plus text-base"></i> إضافة قطعة جديدة للطلب
                        </button>
                    </div>
                </div>

                {{-- فوتر الموديل (حفظ الإجماليات) --}}
                <div
                    class="px-6 py-4 bg-white border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 shrink-0">
                    <div class="text-right w-full sm:w-auto">
                        <p class="text-[11px] text-gray-500 font-bold mb-1">إجمالي الفاتورة بعد التعديل</p>
                        <p class="text-2xl font-black text-[#8A704C] font-mono"
                            x-text="formatMoney(editItemsTotal) + ' ج.م'"></p>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button @click="isItemsModalOpen = false"
                            class="w-full sm:w-auto px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-all">
                            إلغاء
                        </button>
                        <button @click="saveItemsSync()"
                            class="w-full sm:w-auto px-8 py-3 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-md transform hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check"></i> اعتماد وحفظ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php $stageTranslations = [ 'cutting' => 'القص', 'sewing' => 'الخياطة', 'ironing' => 'الكي', 'ready-to-ship' => 'جاهز للشحن', ]; @endphp
    {{-- سيكشن الإدارة والإنتاج (يظهر فقط للأدمن) --}}
    {{-- يمكنك تغيير الشرط هنا بناءً على نظام الصلاحيات لديك --}}
    @if (auth()->user() && auth()->user()->role == 'admin') <!-- استبدل true بالشرط الفعلي -->
        <div class="mt-12 border-t-2 border-dashed border-gray-200 pt-8">
            <h2 class="text-xl font-black text-gray-800 mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gray-900 text-white flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </div>
                <span data-ar="سجل الإدارة وعمليات الإنتاج" data-en="Admin & Production Logs">سجل الإدارة وعمليات
                    الإنتاج</span>
                <span class="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full font-bold ml-2"
                    data-ar="سري (للإدارة)" data-en="Confidential (Admin)">سري (للإدارة)</span>
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- مهام الإنتاج الموكلة --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-100 p-4">
                        <h3 class="font-bold text-gray-700 text-sm" data-ar="المهام الموكلة للموظفين"
                            data-en="Assigned Tasks">المهام الموكلة للموظفين</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        @forelse($order->assignments as $assignment)
                            <div
                                class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                                        <i class="fa-solid fa-user-gear text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">
                                            {{ $assignment->employee->name ?? 'موظف' }}</p>
                                        <p class="text-[10px] text-gray-400"> مرحلة: <span
                                                class="text-blue-600 font-bold">
                                                {{ $stageTranslations[$assignment->stage] ?? $assignment->stage }} </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-left rtl:text-left text-[10px] text-gray-400">
                                    {{ $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d H:i') : '' }}
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4" data-ar="لم يتم تعيين مهام بعد."
                                data-en="No assignments yet.">لم يتم تعيين مهام بعد.</p>
                        @endforelse
                    </div>
                </div>

                {{-- سجل التعديلات (History) --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-100 p-4">
                        <h3 class="font-bold text-gray-700 text-sm" data-ar="سجل التعديلات الشامل"
                            data-en="Comprehensive History Log">سجل التعديلات الشامل</h3>
                    </div>
                    <div class="p-4 space-y-3 max-h-[300px] overflow-y-auto">
                        @forelse($order->histories as $history)
                            <div
                                class="border-r-2 border-activeGold rtl:border-r-2 rtl:border-l-0 ltr:border-l-2 ltr:border-r-0 pl-3 pr-3 py-1 mb-3">
                                <p class="text-xs font-bold text-gray-800">{{ $history->action }}</p>
                                <p class="text-[10px] text-gray-500 mt-1">
                                    بواسطة: {{ $history->user->name ?? 'النظام' }} |
                                    <span
                                        class="font-mono text-gray-400">{{ $history->executed_at ? $history->executed_at->format('Y-m-d H:i') : '' }}</span>
                                </p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4" data-ar="لا توجد سجلات."
                                data-en="No history logs.">لا توجد سجلات.</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    @endif
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #8A704C;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderDashboard', (initData) => ({
                orderId: initData.orderId,
                totalPrice: initData.totalPrice,
                paidAmount: initData.paidAmount,
                remainingAmount: initData.remainingAmount,
                status: initData.status,
                customer: initData.customer,
                items: initData.items,
                dictionaries: initData.dictionaries,

                // Modal States
                isItemsModalOpen: false,
                editItems: [], // نسخة للعمل عليها داخل الموديل

                statusOpen: false,
                editMode: {
                    customer: false,
                    financials: false,
                },
                paidAmountEdit: initData.paidAmount,

                statuses: [{
                        value: 'new',
                        labelAr: 'جديد',
                        color: 'bg-blue-100 text-blue-800'
                    },
                    {
                        value: 'confirmed',
                        labelAr: 'مؤكد',
                        color: 'bg-indigo-100 text-indigo-800'
                    },
                    {
                        value: 'cutting',
                        labelAr: 'قص',
                        color: 'bg-purple-100 text-purple-800'
                    },
                    {
                        value: 'sewing',
                        labelAr: 'خياطة',
                        color: 'bg-pink-100 text-pink-800'
                    },
                    {
                        value: 'finishing',
                        labelAr: 'تشطيب',
                        color: 'bg-orange-100 text-orange-800'
                    },
                    {
                        value: 'ready-to-ship',
                        labelAr: 'جاهز للشحن',
                        color: 'bg-teal-100 text-teal-800'
                    },
                    {
                        value: 'shipped',
                        labelAr: 'تم الشحن',
                        color: 'bg-amber-100 text-amber-800'
                    },
                    {
                        value: 'delivered',
                        labelAr: 'تم التسليم',
                        color: 'bg-green-100 text-green-800'
                    },
                    {
                        value: 'canceled',
                        labelAr: 'ملغي',
                        color: 'bg-red-100 text-red-800'
                    }
                ],

                notification: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                get currentStatusObj() {
                    return this.statuses.find(s => s.value === this.status) || {
                        labelAr: this.status,
                        color: 'bg-gray-100 text-gray-800'
                    };
                },

                get editItemsTotal() {
                    return this.editItems.reduce((sum, item) => sum + (Number(item.quantity) *
                        Number(item.price)), 0);
                },

                formatMoney(value) {
                    return Number(value).toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                },

                getLabel(dictionaryName, id) {
                    if (!id || !this.dictionaries[dictionaryName]) return null;
                    const found = this.dictionaries[dictionaryName].find(x => x.id == id);
                    return found ? found.name : null;
                },

                showToast(msg, type = 'success') {
                    this.notification.message = msg;
                    this.notification.type = type;
                    this.notification.show = true;
                    setTimeout(() => this.notification.show = false, 3000);
                },

                async apiCall(payload) {
                    try {
                        const response = await fetch(`/orders/${this.orderId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'حدث خطأ أثناء الحفظ');

                        this.showToast(data.message || 'تم حفظ التعديلات بنجاح!', 'success');

                        if (data.order) {
                            this.totalPrice = data.order.total_price;
                            this.paidAmount = data.paid_amount ?? this.paidAmount;
                            this.remainingAmount = data.order.remaining_amount;

                            // تحديث المنتجات في الواجهة لو رجعت من الباك إند
                            if (data.order.items) {
                                this.items = data.order.items;
                            }
                        }
                    } catch (error) {
                        this.showToast(error.message, 'error');
                    }
                },

                updateStatus(newStatus) {
                    this.status = newStatus;
                    this.statusOpen = false;
                    this.apiCall({
                        status: newStatus
                    });
                },

                saveCustomerInfo() {
                    this.editMode.customer = false;
                    this.apiCall({
                        customer_info: this.customer
                    });
                },

                saveFinancials() {
                    this.editMode.financials = false;
                    this.paidAmount = parseFloat(this.paidAmountEdit);
                    this.remainingAmount = this.totalPrice - this.paidAmount;
                    this.apiCall({
                        update_financials: true,
                        paid_amount: this.paidAmount
                    });
                },

                // --- دوال الموديل السحري ---
                openItemsModal() {
                    // نأخذ نسخة عميقة من المنتجات حتى لا تتأثر الصفحة إذا أغلقنا بدون حفظ
                    this.editItems = JSON.parse(JSON.stringify(this.items));
                    this.isItemsModalOpen = true;
                },

                addNewEditItem() {
                    this.editItems.push({
                        type_id: '',
                        pant_type_id: '',
                        style_id: '',
                        color_id: '',
                        size_id: '',
                        sleeve_type_id: '',
                        quantity: 1,
                        price: 0,
                        notes: ''
                    });

                    // تمرير الشاشة لأسفل لرؤية العنصر الجديد بسلاسة
                    setTimeout(() => {
                        const modalBody = document.querySelector('.custom-scrollbar');
                        if (modalBody) modalBody.scrollTop = modalBody.scrollHeight;
                    }, 100);
                },

                removeEditItem(index) {
                    this.editItems.splice(index, 1);
                },

                saveItemsSync() {
                    if (this.editItems.length === 0) {
                        this.showToast('لا يمكن حفظ طلب بدون منتجات!', 'error');
                        return;
                    }
                    // إرسال البيانات المحدثة عبر AJAX
                    this.apiCall({
                        sync_items: this.editItems
                    });
                    this.isItemsModalOpen = false;
                }
            }));
        });

        window.syncOrderShipment = async function(shipmentId, btn) {
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>جاري التحديث...</span>';

            try {
                const res = await fetch(`/shipments/${shipmentId}/sync`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const badge = document.getElementById('order_turbo_status_badge');
                    if (badge && data.shipment) {
                        badge.innerHTML = `<i class="fa-solid fa-circle-check text-xs"></i> ${data.shipment.status_text || data.shipment.status || 'تم التحديث'}`;
                    }
                    btn.innerHTML = '<i class="fa-solid fa-check text-emerald-600 text-xs"></i> <span class="text-emerald-700">تم التحديث</span>';
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }, 2000);
                } else {
                    alert(data.message || 'فشلت المزامنة');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('حدث خطأ أثناء الاتصال بالخادم');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        };
    </script>
@endpush
