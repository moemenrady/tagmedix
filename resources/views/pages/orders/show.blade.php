@extends('layouts.app')

@section('title', 'تفاصيل الطلب | Order Details')

@section('content')
    <!-- تضمين Alpine.js للتفاعلية -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="font-sans antialiased text-gray-900 pb-12 space-y-6" x-data="orderDashboard(
        {{ $order->id }},
        {{ $order->total_price }},
        {{ $order->payments_sum_amount ?? 0 }},
        '{{ $order->status }}'
    )">

        {{-- إشعار التحديث الذكي (Toast) --}}
        <div x-show="notification.show" x-transition.opacity.duration.300ms
            class="fixed top-5 left-1/2 transform -translate-x-1/2 z-50 bg-gray-900 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 transition-all"
            :class="notification.type === 'success' ? 'border-b-4 border-green-500' : 'border-b-4 border-red-500'"
            style="display: none;">
            <i class="fa-solid"
                :class="notification.type === 'success' ? 'fa-circle-check text-green-400' :
                    'fa-triangle-exclamation text-red-400'"></i>
            <span x-text="notification.message" class="text-sm font-bold"></span>
        </div>

        {{-- شريط العنوان والإجراءات السريعة والحالة --}}
        <div
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row items-center justify-between gap-4 transition-all hover:shadow-md">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <a href="{{ route('orders.index') }}"
                    class="w-10 h-10 shrink-0 rounded-xl bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-800 transition-colors rtl:rotate-0 ltr:rotate-180">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $order->order_number }}</h1>

                        {{-- تعديل الحالة الذكي --}}
                        <div class="relative" @click.away="statusOpen = false">
                            <button @click="statusOpen = !statusOpen"
                                class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-bold border transition-all hover:shadow-md cursor-pointer"
                                :class="currentStatusObj.color">
                                <span class="w-2.5 h-2.5 rounded-full bg-current animate-pulse"></span>
                                <span data-ar x-text="currentStatusObj.labelAr"></span>
                                <span data-en x-text="currentStatusObj.labelEn" style="display:none;"></span>
                                <i class="fa-solid fa-chevron-down text-[10px] ml-1 opacity-70"></i>
                            </button>

                            <div x-show="statusOpen" x-transition.opacity.scale.95
                                class="absolute top-full rtl:right-0 ltr:left-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-30 overflow-hidden">
                                <template x-for="st in statuses" :key="st.value">
                                    <button @click="updateStatus(st.value)"
                                        class="w-full text-start px-4 py-3 text-xs font-bold hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0 flex items-center gap-2"
                                        :class="currentStatus === st.value ? 'bg-amber-50/50 text-activeGold' : 'text-gray-600'">
                                        <div class="w-2 h-2 rounded-full" :class="st.color.split(' ')[0]"></div>
                                        <span class="rtl:block ltr:hidden" x-text="st.labelAr"></span>
                                        <span class="rtl:hidden ltr:block" x-text="st.labelEn"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                        <i class="fa-regular fa-calendar-plus"></i>
                        <span data-ar="تاريخ الإنشاء:" data-en="Created At:">تاريخ الإنشاء:</span>
                        {{ $order->created_at->format('Y-m-d h:i A') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button
                    class="w-full md:w-auto bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print text-gray-500"></i>
                    <span data-ar="طباعة الفاتورة" data-en="Print Invoice">طباعة الفاتورة</span>
                </button>
            </div>
        </div>

        {{-- شبكة المعلومات العلوية --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 1. كارت بيانات العميل (مع أزرار تواصل) --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 relative group" x-data="{ editCustomer: false }">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center">
                            <i class="fa-solid fa-user-tie text-lg"></i>
                        </div>
                        <h3 class="font-bold text-gray-800" data-ar="بيانات العميل" data-en="Customer Details">بيانات العميل
                        </h3>
                    </div>
                    <button @click="editCustomer = !editCustomer"
                        class="text-gray-400 hover:text-activeGold transition-colors p-2 bg-gray-50 rounded-lg">
                        <i class="fa-solid" :class="editCustomer ? 'fa-xmark text-red-400' : 'fa-pen'"></i>
                    </button>
                </div>

                {{-- وضع العرض --}}
                <div x-show="!editCustomer" class="space-y-4">
                    <div>
                        <p class="text-[11px] text-gray-400 font-bold mb-1" data-ar="الاسم" data-en="Name">الاسم</p>
                        <p class="text-sm font-black text-gray-800" x-text="customer.name"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-bold mb-1" data-ar="الهاتف الأساسي"
                                data-en="Primary Phone">الهاتف الأساسي</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-gray-700 font-mono" x-text="customer.phone1"
                                    dir="ltr"></p>
                                <div class="flex gap-1">
                                    <a :href="'tel:' + customer.phone1"
                                        class="w-6 h-6 flex items-center justify-center rounded bg-green-100 text-green-600 hover:bg-green-200 transition">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                    </a>
                                    <a :href="'https://wa.me/' + customer.phone1.replace(/\D/g, '')" target="_blank"
                                        class="w-6 h-6 flex items-center justify-center rounded bg-green-500 text-white hover:bg-green-600 transition">
                                        <i class="fa-brands fa-whatsapp text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100" x-show="customer.phone2">
                            <p class="text-[10px] text-gray-400 font-bold mb-1" data-ar="الهاتف البديل"
                                data-en="Secondary Phone">الهاتف البديل</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-gray-700 font-mono" x-text="customer.phone2"
                                    dir="ltr"></p>
                                <div class="flex gap-1">
                                    <a :href="'tel:' + customer.phone2"
                                        class="w-6 h-6 flex items-center justify-center rounded bg-green-100 text-green-600 hover:bg-green-200 transition">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                    </a>
                                    <a :href="'https://wa.me/' + customer.phone2.replace(/\D/g, '')" target="_blank"
                                        class="w-6 h-6 flex items-center justify-center rounded bg-green-500 text-white hover:bg-green-600 transition">
                                        <i class="fa-brands fa-whatsapp text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start">
                        <i class="fa-solid fa-location-dot mt-1 text-gray-400"></i>
                        <div>
                            <p class="text-[11px] text-gray-400 font-bold mb-0.5" data-ar="العنوان" data-en="Address">
                                العنوان</p>
                            <p class="text-sm font-bold text-gray-700">
                                <span x-text="customer.gov" class="text-activeGold"></span> - <span
                                    x-text="customer.address"></span>
                            </p>
                        </div>
                    </div>
                    <div x-show="customer.insta">
                        <a :href="customer.insta" target="_blank"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-pink-50 text-pink-600 rounded-lg text-xs font-bold hover:bg-pink-100 transition">
                            <i class="fa-brands fa-instagram text-base"></i>
                            <span data-ar="زيارة حساب انستجرام" data-en="Visit Instagram">زيارة حساب انستجرام</span>
                        </a>
                    </div>
                </div>

                {{-- وضع التعديل --}}
                <div x-show="editCustomer" x-cloak class="space-y-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500" data-ar="الاسم" data-en="Name">الاسم</label>
                        <input type="text" x-model="customer.name"
                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500" data-ar="هاتف 1" data-en="Phone 1">هاتف
                                1</label>
                            <input type="text" x-model="customer.phone1"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500" data-ar="هاتف 2" data-en="Phone 2">هاتف
                                2</label>
                            <input type="text" x-model="customer.phone2"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-1">
                            <label class="text-[10px] font-bold text-gray-500" data-ar="المحافظة"
                                data-en="Governorate">المحافظة</label>
                            <input type="text" x-model="customer.gov"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] font-bold text-gray-500" data-ar="العنوان التفصيلي"
                                data-en="Full Address">العنوان التفصيلي</label>
                            <input type="text" x-model="customer.address"
                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-500" data-ar="رابط انستجرام"
                            data-en="Instagram URL">رابط انستجرام</label>
                        <input type="text" x-model="customer.insta"
                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                    </div>
                    <button @click="saveCustomerInfo(); editCustomer = false"
                        class="w-full bg-gray-900 text-white rounded-lg py-2.5 text-sm font-bold hover:bg-gray-800 transition shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> <span data-ar="حفظ التعديلات" data-en="Save Changes">حفظ
                            التعديلات</span>
                    </button>
                </div>
            </div>

            {{-- 2. كارت الحسابات والمالية --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-wallet text-lg"></i>
                        </div>
                        <h3 class="font-bold text-gray-800" data-ar="الملخص المالي" data-en="Financial Summary">الملخص
                            المالي</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-xs font-bold text-gray-500" data-ar="إجمالي الفاتورة"
                                data-en="Total Price">إجمالي الفاتورة</span>
                            <span class="text-lg font-black text-gray-900 font-mono">
                                <span x-text="formatMoney(totalPrice)"></span>
                                <span class="text-[10px] text-gray-400 font-sans" data-ar="ج.م"
                                    data-en="EGP">ج.م</span>
                            </span>
                        </div>

                        <div
                            class="flex justify-between items-center p-3.5 bg-green-50 rounded-xl border border-green-200">
                            <span class="text-xs font-bold text-green-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span data-ar="إجمالي المدفوع" data-en="Total Paid">
                                    إجمالي المدفوع
                                </span>
                            </span>

                            <span class="text-lg font-black text-green-700 font-mono">
                                {{ number_format($order->getTotalPaid(), 0) }}
                                <span class="text-[10px] text-green-600 font-bold">ج.م</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-4 flex justify-between items-center p-4 rounded-xl border
    {{ $order->getRemainingAmount() > 0 ? 'bg-rose-50 border-rose-200' : 'bg-gray-50 border-gray-200' }}">

                    <span
                        class="text-sm font-black
        {{ $order->getRemainingAmount() > 0 ? 'text-rose-700' : 'text-gray-500' }}">
                        المتبقي للتحصيل
                    </span>

                    <span
                        class="text-xl font-black font-mono
        {{ $order->getRemainingAmount() > 0 ? 'text-rose-700' : 'text-gray-900' }}">

                        {{ number_format($order->getRemainingAmount(), 0) }}
                        <span class="text-xs opacity-70">ج.م</span>
                    </span>
                </div>
            </div>

            {{-- 3. المصدر والبيانات الإضافية --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fa-solid fa-circle-info text-lg"></i>
                    </div>
                    <h3 class="font-bold text-gray-800" data-ar="معلومات الطلب" data-en="Order Info">معلومات الطلب</h3>
                </div>

                <div class="space-y-6">
                    <div>
                        <p class="text-xs text-gray-400 font-bold mb-2" data-ar="مصدر الطلب" data-en="Order Source">مصدر
                            الطلب</p>
                        @php
                            $sourceConfig = [
                                'new' => ['color' => 'bg-amber-100 text-amber-800', 'ar' => 'طلب جديد / تفصيل'],
                                'from_stock' => ['color' => 'bg-blue-100 text-blue-800', 'ar' => 'من المخزون مباشر'],
                                'replacement' => ['color' => 'bg-purple-100 text-purple-800', 'ar' => 'استبدال'],
                            ];
                            $config = $sourceConfig[$order->source] ?? [
                                'color' => 'bg-gray-100 text-gray-800',
                                'ar' => $order->source,
                            ];
                        @endphp
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $config['color'] }}">
                            <span>{{ $config['ar'] }}</span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                <i class="fa-solid fa-boxes-stacked text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-600" data-ar="إجمالي القطع"
                                data-en="Total Items">إجمالي القطع</span>
                        </div>
                        <span class="text-2xl font-black text-gray-900">{{ $order->items->sum('quantity') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- محتويات الطلب (Interactive List / Cards) --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div
                class="p-5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white rtl:from-white rtl:to-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-activeGold text-white flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-shirt text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg" data-ar="محتويات الطلب" data-en="Order Items">محتويات
                            الطلب</h3>
                        <p class="text-xs text-gray-400" data-ar="اضغط على أي منتج لعرض التفاصيل أو التعديل"
                            data-en="Click on any item to view details or edit">اضغط على أي منتج لعرض التفاصيل أو التعديل
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 space-y-3 bg-gray-50/50">
                @forelse ($order->items as $item)
                    {{-- بطاقة المنتج الذكية --}}
                    <div x-data="{
                        expanded: false,
                        isEditingItem: false,
                        itemData: {
                            productName: '{{ addslashes($item->product_name) }}',
                            qty: {{ $item->quantity }},
                            price: {{ $item->price }},
                            notes: '{{ addslashes($item->notes ?? '') }}'
                        }
                    }"
                        class="bg-white border border-gray-200 rounded-xl shadow-sm transition-all duration-300 hover:border-activeGold/50">

                        {{-- الواجهة المغلقة (Summary) --}}
                        <div @click="expanded = !expanded"
                            class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer hover:bg-gray-50/50 transition">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xl font-bold shrink-0">
                                    {{ mb_substr($item->product_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                        @if ($item->type)
                                            <span
                                                class="inline-flex items-center gap-1 bg-violet-50 text-violet-700 px-2.5 py-1 rounded-full text-[10px] font-bold border border-violet-200">
                                                <i class="fa-solid fa-tags"></i>
                                                {{ $item->type->name }}
                                            </span>
                                        @endif
                                        @if ($item->pantType)
                                            <span
                                                class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-[10px] font-bold border border-emerald-200">
                                                <i class="fa-solid fa-person-walking"></i>
                                                {{ $item->pantType->name }}
                                            </span>
                                        @endif
                                        @if ($item->style)
                                            <span
                                                class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold"><i
                                                    class="fa-solid fa-palette mr-1"></i>
                                                {{ $item->style->name }}</span>
                                        @endif
                                        @if ($item->color)
                                            <span
                                                class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded text-[10px] font-bold"><i
                                                    class="fa-solid fa-droplet mr-1"></i>
                                                {{ $item->color->name }}</span>
                                        @endif
                                        @if ($item->size)
                                            <span
                                                class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold"><i
                                                    class="fa-solid fa-ruler mr-1"></i> {{ $item->size->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between md:justify-end gap-6 border-t md:border-0 pt-3 md:pt-0">
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold" data-ar="الكمية" data-en="Qty">الكمية
                                    </p>
                                    <p class="font-black text-gray-800" x-text="itemData.qty"></p>
                                </div>
                                <div class="text-center hidden sm:block">
                                    <p class="text-[10px] text-gray-400 font-bold" data-ar="سعر الوحدة"
                                        data-en="Unit Price">سعر الوحدة</p>
                                    <p class="font-bold text-gray-600 font-mono"><span x-text="itemData.price"></span>
                                        <span class="text-[9px]">EGP</span>
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold" data-ar="الإجمالي" data-en="Total">
                                        الإجمالي</p>
                                    <p class="font-black text-green-600 font-mono text-lg">
                                        <span x-text="(itemData.qty * itemData.price).toFixed(2)"></span>
                                        <span class="text-[10px] text-gray-500 font-sans" data-ar="ج.م"
                                            data-en="EGP">ج.م</span>
                                    </p>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fa-solid fa-chevron-down transition-transform duration-300"
                                        :class="expanded ? 'rotate-180' : ''"></i>
                                </div>
                            </div>
                            <div class="p-5 border-t border-gray-100 bg-gray-50">
                                {{-- عرض التفاصيل --}}
                                <div x-show="!isEditingItem">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-white p-3 rounded-xl border border-gray-100">
                                            <p class="text-xs text-gray-400 font-bold mb-1" data-ar="ملاحظات التصنيع"
                                                data-en="Manufacturing Notes">ملاحظات التصنيع</p>
                                            <p class="text-sm text-gray-800"
                                                x-text="itemData.notes || 'لا توجد ملاحظات.'"></p>
                                        </div>
                                        <div class="flex items-center justify-end">
                                            <button @click="isEditingItem = true"
                                                class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-lg hover:bg-gray-800 transition flex items-center gap-2">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                <span data-ar="تعديل تفاصيل المنتج" data-en="Edit Item Details">تعديل
                                                    تفاصيل المنتج</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- نموذج التعديل --}}
                                <div x-show="isEditingItem" class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="sm:col-span-3">
                                            <label class="text-[10px] font-bold text-gray-500" data-ar="اسم المنتج"
                                                data-en="Product Name">اسم المنتج</label>
                                            <input type="text" x-model="itemData.productName"
                                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500" data-ar="الكمية"
                                                data-en="Quantity">الكمية</label>
                                            <input type="number" x-model="itemData.qty"
                                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500" data-ar="السعر"
                                                data-en="Price">السعر</label>
                                            <input type="number" step="0.01" x-model="itemData.price"
                                                class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-gray-500" data-ar="ملاحظات"
                                            data-en="Notes">ملاحظات</label>
                                        <textarea x-model="itemData.notes" rows="2"
                                            class="w-full text-sm border-gray-200 rounded-lg focus:ring-activeGold"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-2 mt-3">
                                        <button @click="isEditingItem = false"
                                            class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-300 transition"
                                            data-ar="إلغاء" data-en="Cancel">إلغاء</button>
                                        <button @click="updateItem({{ $item->id }}, itemData); isEditingItem = false"
                                            class="px-4 py-2 bg-activeGold text-white text-xs font-bold rounded-lg hover:bg-yellow-700 transition"
                                            data-ar="حفظ المنتج" data-en="Save Item">حفظ المنتج</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        <i class="fa-solid fa-box-open text-4xl mb-3 opacity-20"></i>
                        <p data-ar="لا توجد منتجات مسجلة في هذا الطلب." data-en="No items found in this order.">لا توجد
                            منتجات مسجلة في هذا الطلب.</p>
                    </div>
                @endforelse
            </div>
        </div>
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
                                            @php
                                                $stageLabels = [
                                                    'cutting' => 'قص',
                                                    'sewing' => 'خياطة',
                                                    'ironing' => 'كي',
                                                    'ready-to-ship' => 'تقفيل وجاهز للشحن',
                                                ];

                                                $stageLabel = $stageLabels[$assignment->stage] ?? $assignment->stage;
                                            @endphp

                                            <p class="text-[10px] text-gray-400 font-mono">
                                                مرحلة:
                                                <span class="text-blue-600 font-bold">
                                                    {{ $stageLabel }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-left rtl:text-left text-[10px] text-gray-400">
                                        {{ $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d h:i A') : '' }}
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
                                            class="font-mono text-gray-400">{{ $history->executed_at ? $history->executed_at->format('Y-m-d h:i A') : '' }}</span>
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
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderDashboard', (orderId, initialTotal, initialPaid, initStatus) => ({
                orderId: orderId,
                totalPrice: initialTotal,
                currentStatus: initStatus,
                statusOpen: false,

                customer: {
                    name: '{{ addslashes($order->customer?->name ?? $order->customer_name) }}',
                    phone1: '{{ $order->customer?->phone_1 ?? $order->phone_1 }}',
                    phone2: '{{ $order->customer?->phone_2 ?? $order->phone_2 }}',
                    gov: '{{ addslashes($order->address?->governorate ?? $order->governorate) }}',
                    address: '{{ addslashes($order->address?->full_address ?? $order->full_address) }}',
                    insta: '{{ $order->customer?->instagram_profile ?? $order->instagram_profile }}'
                },

                statuses: [{
                        value: 'new',
                        labelAr: 'جديد',
                        labelEn: 'New',
                        color: 'bg-blue-100 text-blue-800 border-blue-200'
                    },
                    {
                        value: 'confirmed',
                        labelAr: 'مؤكد',
                        labelEn: 'Confirmed',
                        color: 'bg-indigo-100 text-indigo-800 border-indigo-200'
                    },
                    {
                        value: 'cutting',
                        labelAr: 'قص',
                        labelEn: 'Cutting',
                        color: 'bg-purple-100 text-purple-800 border-purple-200'
                    },
                    {
                        value: 'sewing',
                        labelAr: 'خياطة',
                        labelEn: 'Sewing',
                        color: 'bg-pink-100 text-pink-800 border-pink-200'
                    },
                    {
                        value: 'finishing',
                        labelAr: 'تشطيب',
                        labelEn: 'Finishing',
                        color: 'bg-orange-100 text-orange-800 border-orange-200'
                    },
                    {
                        value: 'ready-to-ship',
                        labelAr: 'جاهز للشحن',
                        labelEn: 'Ready to Ship',
                        color: 'bg-teal-100 text-teal-800 border-teal-200'
                    },
                    {
                        value: 'shipped',
                        labelAr: 'تم الشحن',
                        labelEn: 'Shipped',
                        color: 'bg-amber-100 text-amber-800 border-amber-200'
                    },
                    {
                        value: 'delivered',
                        labelAr: 'تم التسليم',
                        labelEn: 'Delivered',
                        color: 'bg-green-100 text-green-800 border-green-200'
                    },
                    {
                        value: 'canceled',
                        labelAr: 'ملغي',
                        labelEn: 'Canceled',
                        color: 'bg-red-100 text-red-800 border-red-200'
                    }
                ],

                notification: {
                    show: false,
                    message: '',
                    type: 'success'
                },



                get currentStatusObj() {
                    return this.statuses.find(s => s.value === this.currentStatus) || {
                        labelAr: this.currentStatus,
                        color: 'bg-gray-100 text-gray-800'
                    };
                },

                formatMoney(value) {
                    return Number(value).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                showToast(msg, type = 'success') {
                    this.notification.message = msg;
                    this.notification.type = type;
                    this.notification.show = true;
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 3000);
                },

                // الاتصال الحقيقي بالسيرفر والتحديث
                async apiCall(payload) {
                    try {
                        const response = await fetch(`/orders/${this.orderId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // تأكد من تمرير التوكن بشكل صحيح
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (!response.ok) throw new Error(data.message || 'حدث خطأ أثناء الحفظ');

                        const lang = document.documentElement.lang || 'ar';
                        this.showToast(lang === 'ar' ? 'تم الحفظ بنجاح!' : 'Saved successfully!',
                            'success');

                        // إذا تم التعديل وتأثرت الإجماليات، نقوم بمزامنتها من الداتا العائدة من الكنترولر
                        if (data.order) {
                            this.totalPrice = data.order.total_price;
                        }

                    } catch (error) {
                        this.showToast(error.message, 'error');
                    }
                },

                // أوامر التحديث التي تستخدم دالة الـ apiCall الشاملة
                updateStatus(newStatus) {
                    this.currentStatus = newStatus;
                    this.statusOpen = false;
                    this.apiCall({
                        status: newStatus
                    });
                },



                saveCustomerInfo() {
                    this.apiCall({
                        customer_info: this.customer
                    });
                },

                updateItem(itemId, data) {
                    this.apiCall({
                        order_item: {
                            id: itemId,
                            ...data
                        }
                    });
                }
            }));
        });
    </script>
@endpush
