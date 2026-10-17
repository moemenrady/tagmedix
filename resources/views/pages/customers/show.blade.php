@extends('layouts.app')

@section('title', 'ملف العميل | ' . $customer->name)

@section('content')
    <div class="space-y-6 animate-fade-in pb-20 md:pb-8 font-sans" dir="rtl">

        {{-- 1. شريط التنقل العلوي ومعلومات العميل الأساسية --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream p-5 md:p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                
                {{-- تفاصيل العميل والرمز الشخصي --}}
                <div class="flex items-start sm:items-center gap-4">
                    <a href="{{ route('customers.index') }}" 
                        class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition shrink-0"
                        title="رجوع لقائمة العملاء">
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>

                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#8A704C] to-[#b3956b] text-white flex items-center justify-center font-black text-xl shadow-md shrink-0">
                        {{ mb_substr($customer->name, 0, 1) }}
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-xl md:text-2xl font-black text-darkText">{{ $customer->name }}</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                عميل #{{ $customer->id }}
                            </span>
                        </div>
                        
                        <p class="text-xs text-mutedBrown mt-1 flex items-center gap-2">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>تاريخ التسجيل: {{ $customer->created_at ? $customer->created_at->format('Y-m-d') : 'غير مسجل' }}</span>
                        </p>
                    </div>
                </div>

                {{-- أزرار التواصل وإضافة طلب --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    @php 
                        $phone1Clean = preg_replace('/[^0-9]/', '', $customer->phone_1);
                        $phone2Clean = preg_replace('/[^0-9]/', '', $customer->phone_2);
                    @endphp

                    @if ($customer->phone_1)
                        <a href="tel:{{ $customer->phone_1 }}" 
                            class="px-3.5 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-blue-200 shadow-2xs">
                            <i class="fa-solid fa-phone"></i>
                            <span dir="ltr">{{ $customer->phone_1 }}</span>
                        </a>

                        <a href="https://wa.me/{{ str_starts_with($phone1Clean, '01') ? '2' . $phone1Clean : $phone1Clean }}" 
                            target="_blank"
                            class="px-3.5 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-emerald-200 shadow-2xs">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>واتساب</span>
                        </a>
                    @endif

                    @if ($customer->phone_2)
                        <a href="tel:{{ $customer->phone_2 }}" 
                            class="px-3 py-2 bg-gray-50 text-gray-700 hover:bg-gray-100 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-gray-200 shadow-2xs"
                            title="هاتف بديل">
                            <i class="fa-solid fa-phone-flip text-[11px] text-gray-400"></i>
                            <span dir="ltr">{{ $customer->phone_2 }}</span>
                        </a>
                    @endif

                    @if ($customer->instagram_profile)
                        <a href="{{ str_starts_with($customer->instagram_profile, 'http') ? $customer->instagram_profile : 'https://instagram.com/' . ltrim($customer->instagram_profile, '@') }}" 
                            target="_blank"
                            class="px-3 py-2 bg-pink-50 text-pink-700 hover:bg-pink-100 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-pink-200 shadow-2xs">
                            <i class="fa-brands fa-instagram text-sm"></i>
                            <span>انستجرام</span>
                        </a>
                    @endif

                    <a href="{{ route('orders.create') }}" 
                        class="px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i>
                        <span>طلب جديد</span>
                    </a>
                </div>

            </div>
        </div>

        {{-- 2. كروت التحليلات المالية وسجل المشتريات (Customer KPI Cards) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            {{-- إجمالي الطلبات --}}
            <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">إجمالي الطلبات</p>
                        <h3 class="text-2xl font-bold text-darkText">{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                        <p class="text-[10px] text-mutedBrown mt-1">
                            <span>{{ $stats['shipped_orders'] ?? 0 }} مشحون</span> • <span>{{ $stats['delivered_orders'] ?? 0 }} مسلم</span>
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primaryBg flex items-center justify-center text-primaryBrown shadow-sm">
                        <i class="fa-solid fa-box-open text-lg"></i>
                    </div>
                </div>
            </div>

            {{-- إجمالي المشتريات --}}
            <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">إجمالي المشتريات</p>
                        <h3 class="text-2xl font-bold text-darkText">
                            {{ number_format($stats['total_spent'] ?? 0, 2) }}
                            <span class="text-xs font-normal">ج.م</span>
                        </h3>
                        <p class="text-[10px] text-mutedBrown mt-1">القيمة الإجمالية للطلبات</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm">
                        <i class="fa-solid fa-wallet text-lg"></i>
                    </div>
                </div>
            </div>

            {{-- إجمالي المدفوع --}}
            <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">إجمالي المدفوع</p>
                        <h3 class="text-2xl font-bold text-emerald-700">
                            {{ number_format($stats['total_paid'] ?? 0, 2) }}
                            <span class="text-xs font-normal">ج.م</span>
                        </h3>
                        <p class="text-[10px] text-emerald-600 mt-1"><i class="fa-solid fa-circle-check mr-0.5"></i> المبالغ المحصلة</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                        <i class="fa-solid fa-money-bill-wave text-lg"></i>
                    </div>
                </div>
            </div>

            {{-- المتبقي للتحصيل --}}
            <div class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">المتبقي للتحصيل</p>
                        <h3 class="text-2xl font-bold {{ ($stats['total_remaining'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-700' }}">
                            {{ number_format($stats['total_remaining'] ?? 0, 2) }}
                            <span class="text-xs font-normal">ج.م</span>
                        </h3>
                        <p class="text-[10px] {{ ($stats['total_remaining'] ?? 0) > 0 ? 'text-rose-500 font-bold' : 'text-mutedBrown' }} mt-1">
                            {{ ($stats['total_remaining'] ?? 0) > 0 ? 'مستحقات معلقة' : 'تم تسوية الحساب' }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-xl {{ ($stats['total_remaining'] ?? 0) > 0 ? 'bg-rose-50 text-rose-600' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                    </div>
                </div>
            </div>

        </div>

        {{-- 3. شبكة تفاصيل العميل والعناوين المسجلة --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- كارت البيانات الشخصية --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream p-5">
                <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <h3 class="font-bold text-darkText text-sm">البيانات الشخصية</h3>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div class="flex justify-between items-center py-1 border-b border-gray-50">
                        <span class="text-mutedBrown font-bold">الاسم:</span>
                        <span class="font-bold text-darkText">{{ $customer->name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 border-b border-gray-50">
                        <span class="text-mutedBrown font-bold">رقم الهاتف الأساسي:</span>
                        <span class="font-bold text-darkText font-mono" dir="ltr">{{ $customer->phone_1 ?: 'غير متوفر' }}</span>
                    </div>
                    @if ($customer->phone_2)
                        <div class="flex justify-between items-center py-1 border-b border-gray-50">
                            <span class="text-mutedBrown font-bold">رقم الهاتف البديل:</span>
                            <span class="font-bold text-darkText font-mono" dir="ltr">{{ $customer->phone_2 }}</span>
                        </div>
                    @endif
                    @if ($customer->instagram_profile)
                        <div class="flex justify-between items-center py-1 border-b border-gray-50">
                            <span class="text-mutedBrown font-bold">انستجرام:</span>
                            <a href="{{ str_starts_with($customer->instagram_profile, 'http') ? $customer->instagram_profile : 'https://instagram.com/' . ltrim($customer->instagram_profile, '@') }}" 
                                target="_blank" class="font-bold text-pink-600 hover:underline">
                                {{ $customer->instagram_profile }}
                            </a>
                        </div>
                    @endif
                    <div class="flex justify-between items-center py-1">
                        <span class="text-mutedBrown font-bold">تاريخ الإضافة:</span>
                        <span class="font-bold text-darkText">{{ $customer->created_at ? $customer->created_at->format('Y-m-d h:i A') : '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- كارت العناوين المسجلة للعميل --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream p-5">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-darkText text-sm">سجل العناوين المسجلة</h3>
                            <p class="text-[11px] text-mutedBrown">العناوين المستخدمة في طلبات العميل</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                        {{ $customer->orderAddresses->count() }} عنوان
                    </span>
                </div>

                @if ($customer->orderAddresses->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($customer->orderAddresses as $index => $addr)
                            <div class="p-3.5 rounded-xl border {{ $index === 0 ? 'bg-amber-50/40 border-amber-200 shadow-2xs' : 'bg-gray-50 border-gray-100' }} text-xs">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="font-black text-darkText flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-amber-600 text-[11px]"></i>
                                        <span>{{ $addr->governorate ?: 'غير محدد' }}</span>
                                        @if ($addr->city)
                                            <span class="text-gray-400 font-normal">({{ $addr->city }})</span>
                                        @endif
                                    </span>
                                    @if ($index === 0)
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">
                                            آخر عنوان
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-600 leading-relaxed text-[11px]">
                                    {{ $addr->full_address ?: 'لا توجد تفاصيل إضافية للعنوان' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-400 text-xs">
                        <i class="fa-solid fa-location-crosshairs text-2xl mb-2 text-gray-300"></i>
                        <p>لم يتم تسجيل أي عناوين لهذا العميل حتى الآن</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- 4. سجل طلبات العميل (Customer Orders List) --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream overflow-hidden">
            
            <div class="p-4 md:p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primaryBg text-primaryBrown flex items-center justify-center">
                        <i class="fa-solid fa-receipt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-darkText text-base">سجل طلبات العميل</h3>
                        <p class="text-xs text-mutedBrown">كافة الطلبات المسجلة باسم هذا العميل وحالتها وتفاصيل الشحن</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-white border border-gray-200 text-gray-700 shadow-2xs">
                    {{ $customer->orders->count() }} طلب
                </span>
            </div>

            @php
                $statusMap = [
                    'new' => ['label' => 'طلب جديد', 'class' => 'bg-gray-100 text-gray-700 border-gray-200'],
                    'confirmed' => ['label' => 'مؤكد', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                    'cutting' => ['label' => 'قص', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                    'sewing' => ['label' => 'خياطة', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
                    'ironing' => ['label' => 'مكواة', 'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
                    'finishing' => ['label' => 'فينش', 'class' => 'bg-teal-100 text-teal-700 border-teal-200'],
                    'ready-to-ship' => ['label' => 'منتج تام (جاهز للشحن)', 'class' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
                    'shipped' => ['label' => 'تم الشحن', 'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                    'delivered' => ['label' => 'تم التسليم', 'class' => 'bg-green-100 text-green-700 border-green-200'],
                    'canceled' => ['label' => 'ملغي', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                ];
            @endphp

            @if ($customer->orders->isNotEmpty())
                {{-- عرض الجدول للشاشات الكبيرة --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50/80 text-mutedBrown border-b border-gray-100 font-bold">
                                <th class="p-3.5">رقم الطلب</th>
                                <th class="p-3.5">التاريخ</th>
                                <th class="p-3.5">الحالة</th>
                                <th class="p-3.5">الشحنة (Turbo)</th>
                                <th class="p-3.5">المحتويات</th>
                                <th class="p-3.5">الإجمالي</th>
                                <th class="p-3.5">المدفوع / المتبقي</th>
                                <th class="p-3.5 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($customer->orders as $order)
                                @php
                                    $st = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'bg-gray-100 text-gray-700 border-gray-200'];
                                    $shipment = $order->shipments ? $order->shipments->sortByDesc('id')->first() : null;
                                @endphp
                                <tr class="hover:bg-primaryBg/30 transition-colors group">
                                    {{-- رقم الطلب --}}
                                    <td class="p-3.5 font-bold">
                                        <a href="{{ route('orders.show', $order->id) }}" class="font-black text-gray-900 group-hover:text-blue-600 transition flex items-center gap-1">
                                            <span class="text-gray-400">#</span>{{ $order->order_number }}
                                        </a>
                                    </td>

                                    {{-- التاريخ --}}
                                    <td class="p-3.5 text-gray-600">
                                        {{ $order->created_at ? $order->created_at->format('Y-m-d h:i A') : '-' }}
                                    </td>

                                    {{-- الحالة --}}
                                    <td class="p-3.5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $st['class'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                            {{ $st['label'] }}
                                        </span>
                                    </td>

                                    {{-- الشحنة --}}
                                    <td class="p-3.5">
                                        @if ($shipment)
                                            <a href="{{ route('shipments.index', ['q' => $shipment->turbo_id ?: $order->order_number]) }}"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition"
                                                title="عرض في الشحنات">
                                                <i class="fa-solid fa-truck-fast text-amber-600"></i>
                                                <span>{{ $shipment->turbo_id ?: 'شحنة Turbo' }}</span>
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-[11px]">-</span>
                                        @endif
                                    </td>

                                    {{-- المحتويات --}}
                                    <td class="p-3.5 text-gray-700">
                                        @if ($order->items && $order->items->isNotEmpty())
                                            <span class="font-medium text-[11px]">
                                                {{ $order->items->count() }} صنف ({{ $order->items->sum('quantity') }} قطعة)
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-[11px]">{{ $order->order_contents ?: '-' }}</span>
                                        @endif
                                    </td>

                                    {{-- الإجمالي --}}
                                    <td class="p-3.5 font-black text-gray-900 font-mono">
                                        {{ number_format($order->total_price, 2) }} <span class="text-[10px] text-gray-400 font-normal">ج.م</span>
                                    </td>

                                    {{-- المدفوع / المتبقي --}}
                                    <td class="p-3.5">
                                        <div class="flex flex-col text-[11px] font-bold gap-0.5">
                                            <span class="text-emerald-700">مدفوع: {{ number_format($order->getTotalPaid(), 0) }}</span>
                                            @if ($order->getRemainingAmount() > 0)
                                                <span class="text-rose-600 font-bold">متبقي: {{ number_format($order->getRemainingAmount(), 0) }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- الإجراءات --}}
                                    <td class="p-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('orders.show', $order->id) }}" 
                                                class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition flex items-center gap-1"
                                                title="عرض تفاصيل الطلب">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                                <span>عرض</span>
                                            </a>
                                            <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                                                class="w-7 h-7 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg flex items-center justify-center transition"
                                                title="طباعة الفاتورة">
                                                <i class="fa-solid fa-print text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- عرض الموبايل (Mobile Cards) --}}
                <div class="block md:hidden p-3 space-y-3 bg-gray-50">
                    @foreach ($customer->orders as $order)
                        @php
                            $st = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'bg-gray-100 text-gray-700 border-gray-200'];
                            $shipment = $order->shipments ? $order->shipments->sortByDesc('id')->first() : null;
                        @endphp
                        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs p-3.5 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('orders.show', $order->id) }}" class="font-black text-sm text-gray-900 flex items-center gap-1">
                                    <span class="text-gray-400">#</span>{{ $order->order_number }}
                                </a>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold border {{ $st['class'] }}">
                                    {{ $st['label'] }}
                                </span>
                            </div>

                            @if ($shipment)
                                <div class="flex items-center justify-between bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg text-[10px]">
                                    <span class="text-amber-800 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-truck-fast text-amber-600"></i>
                                        <span>شحنة: {{ $shipment->turbo_id ?: 'Turbo' }}</span>
                                    </span>
                                    <a href="{{ route('shipments.index', ['q' => $shipment->turbo_id ?: $order->order_number]) }}" class="text-blue-600 font-bold hover:underline">عرض</a>
                                </div>
                            @endif

                            <div class="flex justify-between items-center text-gray-500 text-[11px] pt-1 border-t border-gray-100">
                                <span>{{ $order->created_at ? $order->created_at->format('Y-m-d') : '-' }}</span>
                                <span class="font-black text-darkText font-mono text-xs">{{ number_format($order->total_price, 2) }} ج.م</span>
                            </div>

                            <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                <div class="flex gap-2 text-[10px]">
                                    <span class="text-emerald-700 font-bold">مدفوع: {{ number_format($order->getTotalPaid(), 0) }}</span>
                                    @if ($order->getRemainingAmount() > 0)
                                        <span class="text-rose-600 font-bold">متبقي: {{ number_format($order->getRemainingAmount(), 0) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('orders.show', $order->id) }}" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs">
                                    التفاصيل
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300"></i>
                    <p class="font-bold text-gray-600 text-sm">لا توجد طلبات مسجلة لهذا العميل بعد</p>
                    <a href="{{ route('orders.create') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-xl text-xs font-bold hover:bg-black transition">
                        <i class="fa-solid fa-plus"></i> إنشـاء طلب جديد
                    </a>
                </div>
            @endif

        </div>

    </div>
@endsection
