@extends('layouts.app')

@section('title', 'قائمة العملاء')

@section('content')
    <div class="space-y-6 animate-fade-in pb-20 md:pb-6">

        <!-- 1. شريط الإحصائيات والتحليلات (Customer Analytics KPI Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <!-- إجمالي العملاء -->
            <div
                class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-primaryBrown opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">إجمالي العملاء</p>
                        <h3 class="text-2xl font-bold text-darkText">{{ number_format($statistics['total_customers'] ?? 0) }}
                        </h3>
                        <p class="text-[10px] text-mutedBrown mt-1">مسجلين في القاعدة</p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-xl bg-primaryBg flex items-center justify-center text-primaryBrown shadow-sm">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- العملاء الجدد في الفترة -->
            <div
                class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-green-500 opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">عملاء جدد بالفترة</p>
                        <h3 class="text-2xl font-bold text-green-700">{{ number_format($statistics['new_customers'] ?? 0) }}
                        </h3>
                        <p class="text-[10px] text-green-600 mt-1"><i class="fa-solid fa-user-plus mr-0.5"></i> تمت إضافتهم
                            مؤخراً</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600 shadow-sm">
                        <i class="fa-solid fa-user-check text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- إجمالي مبيعات العملاء -->
            <div
                class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-roseAccent opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">إجمالي مبيعات العملاء</p>
                        <h3 class="text-2xl font-bold text-darkText">
                            {{ number_format($statistics['total_spent'] ?? 0, 0) }}
                            <span class="text-xs font-normal">ج.م</span>
                        </h3>
                        <p class="text-[10px] text-mutedBrown mt-1">قيمة كافة الطلبات</p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-xl bg-primaryBg flex items-center justify-center text-primaryBrown shadow-sm">
                        <i class="fa-solid fa-wallet text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- متوسط الطلبات لجميع العملاء -->
            <div
                class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div
                    class="absolute top-0 right-0 w-24 h-24 bg-amber-500 opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-xs text-mutedBrown mb-1 font-bold">متوسط طلبات العميل</p>
                        <h3 class="text-2xl font-bold text-amber-800">
                            {{ number_format($statistics['avg_orders_per_customer'] ?? 0, 1) }}</h3>
                        <p class="text-[10px] text-mutedBrown mt-1">طلب / لكل عميل</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-700 shadow-sm">
                        <i class="fa-solid fa-chart-line text-lg"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2. الفلاتر والبحث الشامل (Search & Filters) -->
        <div
            class="bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream p-4 md:p-5">
            <form id="filterForm" method="GET" action="{{ route('customers.index') }}" class="space-y-4">

                <!-- شريط البحث الشامل (Search Field) -->
                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute top-1/2 rtl:right-4 ltr:left-4 -translate-y-1/2 text-mutedBrown"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                        class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl py-3.5 rtl:pr-11 rtl:pl-10 ltr:pl-11 ltr:pr-10 focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all text-sm font-bold text-darkText placeholder-mutedBrown/70"
                        placeholder="ابحث باسم العميل، أرقام الهاتف، يوزر إنستجرام، المحافظة، العنوان أو رقم الطلب...">
                    @if (request('q'))
                        <a href="{{ route('customers.index') }}"
                            class="absolute top-1/2 rtl:left-4 ltr:right-4 -translate-y-1/2 text-mutedBrown hover:text-red-500 transition-colors">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </a>
                    @endif
                </div>

                <!-- أزرار الفترات الزمنيّة السريعة (Quick Dates Tabs) -->
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-mutedBrown flex items-center gap-1.5">
                            <i class="fa-solid fa-calendar-alt text-primaryBrown"></i> فلترة العملاء حسب تاريخ التسجيل:
                        </span>
                        @if (request('from') || request('to') || request('date_preset'))
                            <button type="button" onclick="clearDateFilter()"
                                class="text-[11px] font-bold text-roseAccent hover:underline flex items-center gap-1">
                                <i class="fa-solid fa-rotate-left"></i> إلغاء فلتر التاريخ
                            </button>
                        @endif
                    </div>

                    <div class="flex flex-nowrap overflow-x-auto gap-2 pb-2 scrollbar-hide items-center">
                        <button type="button" onclick="applyQuickDate('today')"
                            class="quick-date-btn {{ request('date_preset') == 'today' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-sun text-amber-500"></i> العملاء اليوم
                        </button>

                        <button type="button" onclick="applyQuickDate('yesterday')"
                            class="quick-date-btn {{ request('date_preset') == 'yesterday' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-moon text-indigo-400"></i> العملاء أمس
                        </button>

                        <button type="button" onclick="applyQuickDate('last2days')"
                            class="quick-date-btn {{ request('date_preset') == 'last2days' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-calendar-day text-blue-500"></i> آخر يومين
                        </button>

                        <button type="button" onclick="applyQuickDate('thisWeek')"
                            class="quick-date-btn {{ request('date_preset') == 'thisWeek' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-calendar-week text-emerald-500"></i> هذا الأسبوع
                        </button>

                        <button type="button" onclick="applyQuickDate('thisMonth')"
                            class="quick-date-btn {{ request('date_preset') == 'thisMonth' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-calendar-days text-primaryBrown"></i> هذا الشهر
                        </button>

                        <button type="button" onclick="applyQuickDate('lastMonth')"
                            class="quick-date-btn {{ request('date_preset') == 'lastMonth' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition-all flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-calendar-minus text-rose-500"></i> الشهر السابق
                        </button>
                    </div>
                </div>

                <!-- حقول الفترة المخصصة والفلاتر المتقدمة (Custom Range & Filters Grid) -->
                <div class="grid grid-cols-2 md:grid-cols-6 gap-3 pt-2 border-t border-secondaryCream/60">

                    <!-- من تاريخ -->
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-mutedBrown mb-1">من تاريخ</label>
                        <input type="date" name="from" id="dateFrom" value="{{ request('from') }}"
                            class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2 text-xs text-darkText font-bold focus:outline-none focus:border-primaryBrown">
                    </div>

                    <!-- إلى تاريخ -->
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-mutedBrown mb-1">إلى تاريخ</label>
                        <input type="date" name="to" id="dateTo" value="{{ request('to') }}"
                            class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2 text-xs text-darkText font-bold focus:outline-none focus:border-primaryBrown">
                    </div>

                    <!-- المحافظة -->
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-mutedBrown mb-1">المحافظة</label>
                        <select name="governorate" onchange="this.form.submit()"
                            class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-primaryBrown text-darkText font-medium">
                            <option value="">كل المحافظات</option>
                            @if (isset($governorates))
                                @foreach ($governorates as $gov)
                                    <option value="{{ $gov }}"
                                        {{ request('governorate') == $gov ? 'selected' : '' }}>
                                        {{ $gov }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- فلتر إنفاق العميل (الفلوس) -->
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-mutedBrown mb-1">حجم المشتريات</label>
                        <select name="spending" onchange="this.form.submit()"
                            class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-primaryBrown text-darkText font-medium">
                            <option value="">كافة الفئات المادية</option>
                            <option value="vip" {{ request('spending') == 'vip' ? 'selected' : '' }}>VIP (أكثر من 5,000
                                ج.م)</option>
                            <option value="high" {{ request('spending') == 'high' ? 'selected' : '' }}>عالي (2,000 -
                                5,000 ج.م)</option>
                            <option value="medium" {{ request('spending') == 'medium' ? 'selected' : '' }}>متوسط (500 -
                                2,000 ج.م)</option>
                            <option value="low" {{ request('spending') == 'low' ? 'selected' : '' }}>أقل من 500 ج.م
                            </option>
                            <option value="zero" {{ request('spending') == 'zero' ? 'selected' : '' }}>بدون مشتريات
                            </option>
                        </select>
                    </div>

                    <!-- ترتيب حسب -->
                    <div class="col-span-1">
                        <label class="block text-[10px] font-bold text-mutedBrown mb-1">ترتيب النتائج</label>
                        <select name="sort_by" onchange="this.form.submit()"
                            class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-primaryBrown text-darkText font-medium">
                            <option value="latest" {{ request('sort_by', 'latest') == 'latest' ? 'selected' : '' }}>الأحدث
                                تسجيلاً</option>
                            <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>الأقدم تسجيلاً
                            </option>
                            <option value="most_orders" {{ request('sort_by') == 'most_orders' ? 'selected' : '' }}>الأكثر
                                طلباً</option>
                            <option value="highest_spent" {{ request('sort_by') == 'highest_spent' ? 'selected' : '' }}>
                                الأعلى إنفاقاً</option>
                        </select>
                    </div>

                    <!-- زر الفلترة والإعادة -->
                    <div class="col-span-2 md:col-span-1 flex items-end gap-2">
                        <input type="hidden" name="date_preset" id="datePreset" value="{{ request('date_preset') }}">
                        <button type="submit"
                            class="w-full bg-primaryBrown hover:bg-mutedBrown text-white rounded-xl py-2 text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-md">
                            <i class="fa-solid fa-filter"></i> تطبيق
                        </button>
                        <a href="{{ route('customers.index') }}" title="إعادة ضبط الفلاتر"
                            class="bg-primaryBg hover:bg-secondaryCream text-darkText rounded-xl px-3 py-2 text-xs font-bold transition-all flex items-center justify-center border border-secondaryCream">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>

                </div>
            </form>
        </div>

        <!-- 3. عرض البيانات (Data Display Section) -->
        @if ($customers->count() > 0)

            <!-- أ. العرض على الديسكتوب (Desktop Table View) -->
            <div
                class="hidden md:block bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream overflow-hidden">
                <table class="w-full text-right border-collapse">
                    <thead class="bg-primaryBg border-b border-secondaryCream">
                        <tr>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">العميل</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">أرقام التواصل</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">المحافظة / العنوان</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown text-center">عدد الطلبات</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">إجمالي الإنفاق</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">تاريخ التسجيل</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondaryCream">
                        @foreach ($customers as $customer)
                            @php
                                $lastAddress = $customer->orderAddresses->first();
                                $ordersCount = $customer->orders_count ?? $customer->orders->count();
                                $totalSpent =
                                    $customer->orders_sum_total_price ?? $customer->orders->sum('total_price');
                            @endphp
                            <tr class="hover:bg-primaryBg/40 cursor-pointer transition-colors group"
                                onclick="window.location.href='{{ route('customers.show', $customer->id) }}'">

                                <!-- بيانات العميل والرمز الشخصي -->
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-secondaryCream/60 border border-secondaryCream text-primaryBrown flex items-center justify-center font-bold text-sm shrink-0 group-hover:bg-primaryBrown group-hover:text-white transition-colors">
                                            {{ mb_substr($customer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-darkText group-hover:text-primaryBrown transition-colors">
                                                {{ $customer->name }}
                                            </p>
                                            @if ($customer->instagram_profile)
                                                <a href="https://instagram.com/{{ ltrim($customer->instagram_profile, '@') }}"
                                                    target="_blank" onclick="event.stopPropagation()"
                                                    class="inline-flex items-center gap-1 text-[11px] text-pink-600 hover:underline mt-0.5 font-medium">
                                                    <i class="fa-brands fa-instagram"></i>
                                                    {{ $customer->instagram_profile }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- الهاتف وتطبيق واتساب -->
                                <td class="py-3.5 px-5" dir="ltr">
                                    <div class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-xs font-bold text-darkText">{{ $customer->phone_1 }}</span>
                                            <a href="https://wa.me/2{{ preg_replace('/[^0-9]/', '', $customer->phone_1) }}"
                                                target="_blank" onclick="event.stopPropagation()"
                                                class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center text-[10px] transition-colors"
                                                title="مراسلة واتساب">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </a>
                                        </div>
                                        @if ($customer->phone_2)
                                            <p class="text-[11px] text-mutedBrown mt-0.5">{{ $customer->phone_2 }}</p>
                                        @endif
                                    </div>
                                </td>

                                <!-- العنوان والأقاليم -->
                                <td class="py-3.5 px-5">
                                    <p class="text-xs font-bold text-darkText">
                                        {{ $lastAddress?->governorate ?? 'غير محدد' }}
                                    </p>

                                    <p class="text-[11px] text-mutedBrown mt-0.5 truncate max-w-[180px]"
                                        title="{{ $lastAddress?->full_address ?? '' }}">

                                        @if ($lastAddress?->city)
                                            {{ $lastAddress->city }} -
                                        @endif

                                        {{ $lastAddress?->full_address ?? 'لا يوجد عنوان مسجل' }}
                                    </p>
                                </td>

                                <!-- عدد الطلبات -->
                                <td class="py-3.5 px-5 text-center">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-primaryBg text-primaryBrown border border-secondaryCream">
                                        <i class="fa-solid fa-bag-shopping ml-1 text-[10px]"></i>
                                        {{ $ordersCount }}
                                    </span>
                                </td>

                                <!-- إجمالي الإنفاق -->
                                <td class="py-3.5 px-5">
                                    <p class="text-sm font-bold text-darkText">
                                        {{ number_format($totalSpent, 2) }} <span
                                            class="text-[10px] font-normal">ج.م</span>
                                    </p>
                                    @if ($totalSpent >= 5000)
                                        <span
                                            class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">
                                            <i class="fa-solid fa-crown mr-0.5 text-amber-600"></i> VIP
                                        </span>
                                    @endif
                                </td>

                                <!-- تاريخ التسجيل -->
                                <td class="py-3.5 px-5">
                                    <p class="text-xs font-medium text-darkText">
                                        {{ $customer->created_at->format('Y-m-d') }}</p>
                                    <p class="text-[10px] text-mutedBrown mt-0.5">
                                        {{ $customer->created_at->diffForHumans() }}</p>
                                </td>

                                <!-- الزر السريع لتفاصيل العميل -->
                                <td class="py-3.5 px-5 text-center">
                                    <a href="{{ route('customers.show', $customer->id) }}"
                                        onclick="event.stopPropagation()"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl
                                              bg-primaryBg text-primaryBrown border border-secondaryCream
                                              hover:bg-primaryBrown hover:text-white
                                              transition-all duration-200 text-xs font-bold shadow-sm"
                                        title="فتح كارت العميل">
                                        <i class="fa-solid fa-user-gear"></i>
                                        <span>التفاصيل</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ب. العرض على الموبايل (Mobile Cards View) -->
            <div class="md:hidden space-y-3.5">
                @foreach ($customers as $customer)
                    @php
                        $lastAddress = $customer->orderAddresses->first();
                        $ordersCount = $customer->orders_count ?? $customer->orders->count();
                        $totalSpent = $customer->orders_sum_total_price ?? $customer->orders->sum('total_price');
                    @endphp

                    <div class="bg-white rounded-2xl p-4 shadow-[0_4px_12px_rgba(202,188,176,0.12)] border border-secondaryCream cursor-pointer hover:border-primaryBrown transition-all active:scale-[0.99]"
                        onclick="window.location.href='{{ route('customers.show', $customer->id) }}'">

                        <!-- Header البطاقة -->
                        <div class="flex justify-between items-start gap-2 mb-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-primaryBg border border-secondaryCream text-primaryBrown font-bold flex items-center justify-center text-sm shrink-0">
                                    {{ mb_substr($customer->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-darkText">{{ $customer->name }}</h4>
                                    @if ($customer->instagram_profile)
                                        <p class="text-[11px] text-pink-600 font-medium"><i
                                                class="fa-brands fa-instagram"></i> {{ $customer->instagram_profile }}</p>
                                    @else
                                        <p class="text-[10px] text-mutedBrown">تاريخ التسجيل:
                                            {{ $customer->created_at->format('Y-m-d') }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- بادج عدد الطلبات -->
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-primaryBg text-primaryBrown border border-secondaryCream shrink-0">
                                <i class="fa-solid fa-bag-shopping ml-1"></i> {{ $ordersCount }} طلبات
                            </span>
                        </div>

                        <!-- تفاصيل العنوان والإنفاق -->
                        <div
                            class="grid grid-cols-2 gap-2 my-3 p-2.5 rounded-xl bg-primaryBg/40 border border-secondaryCream/60">
                            <div>
                                <p class="text-[10px] font-bold text-mutedBrown"><i
                                        class="fa-solid fa-location-dot text-roseAccent"></i> المحافظة</p>
                                <p class="text-xs font-bold text-darkText mt-0.5 truncate">
                                    {{ $lastAddress->governorate ?? 'غير محدد' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-mutedBrown"><i
                                        class="fa-solid fa-money-bill-wave text-emerald-600"></i> إجمالي الشراء</p>
                                <p class="text-xs font-bold text-darkText mt-0.5">{{ number_format($totalSpent, 0) }} ج.م
                                </p>
                            </div>
                        </div>

                        <!-- أزرار الإتصال المباشر والملف الشخصي -->
                        <div class="flex items-center justify-between pt-2 border-t border-secondaryCream/50 gap-2">
                            <!-- الاتصال بالهاتف والواتساب -->
                            <div class="flex items-center gap-1.5" dir="ltr" onclick="event.stopPropagation()">
                                <a href="tel:{{ $customer->phone_1 }}"
                                    class="px-2.5 py-1.5 rounded-lg bg-primaryBg text-darkText text-[11px] font-bold border border-secondaryCream flex items-center gap-1 hover:bg-secondaryCream">
                                    <i class="fa-solid fa-phone text-primaryBrown text-[10px]"></i>
                                    {{ $customer->phone_1 }}
                                </a>
                                <a href="https://wa.me/2{{ preg_replace('/[^0-9]/', '', $customer->phone_1) }}"
                                    target="_blank"
                                    class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xs">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            </div>

                            <!-- زر الانتقال للتفاصيل -->
                            <a href="{{ route('customers.show', $customer->id) }}" onclick="event.stopPropagation()"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primaryBrown text-white text-[11px] font-bold shadow-sm">
                                <span>عرض الكارت</span>
                                <i class="fa-solid fa-arrow-left text-[9px]"></i>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- الترقيم (Pagination) بنفس تنسيقات shipments.blade.php -->
            <div class="mt-6 flex justify-center w-full pagination-wrapper">
                {{ $customers->links() }}
            </div>
        @else
            <!-- 4. حالة لا توجد نتائج (Empty State) -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-secondaryCream p-12 flex flex-col items-center justify-center text-center">
                <div
                    class="w-20 h-20 bg-primaryBg rounded-full flex items-center justify-center text-primaryBrown mb-4 shadow-inner">
                    <i class="fa-solid fa-users-slash text-3xl"></i>
                </div>
                <h4 class="text-lg font-bold text-darkText">لم يتم العثور على أي عملاء</h4>
                <p class="text-sm text-mutedBrown mt-1 max-w-md">لا يوجد عملاء يطابقون معايير البحث أو التاريخ المحددة. جرب
                    تغيير كلمة البحث أو إعادة ضبط الفلاتر.</p>
                <a href="{{ route('customers.index') }}"
                    class="mt-5 px-5 py-2.5 rounded-xl bg-primaryBrown text-white text-xs font-bold shadow-md hover:bg-mutedBrown transition-colors">
                    <i class="fa-solid fa-rotate-right mr-1"></i> إعادة عرض كل العملاء
                </a>
            </div>
        @endif

    </div>

    <!-- التنسيقات المخصصة للشكل العام ومظهر الترقيم (CSS Customization) -->
    <style>
        /* شريط الترقيم البصري (Pagination) */
        .pagination-wrapper nav div.hidden.sm\:flex-1 {
            display: flex !important;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .pagination-wrapper span.relative.z-0.inline-flex {
            box-shadow: none;
            gap: 0.25rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-wrapper span.relative.z-0.inline-flex>* {
            border-radius: 0.75rem !important;
            border: 1px solid #DBD0C3;
            margin: 0;
            padding: 0.5rem 0.85rem;
            color: #503E39;
            background: white;
            font-weight: 700;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .pagination-wrapper span.relative.z-0.inline-flex>*:hover {
            background: #EAE9DF;
        }

        .pagination-wrapper span[aria-current="page"]>span {
            background-color: #926857 !important;
            color: white !important;
            border-color: #926857;
        }

        /* تنسيقات علامات التبويب السريعة النشطة */
        .active-tab {
            background-color: #926857 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(146, 104, 87, 0.25);
        }

        /* إخفاء شريط التمرير الأفقي مع بقاء الفلترة قابلة للسحب */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

@endsection

@push('scripts')
    <script>
        /**
         * تطبيق الفلاتر الزمنيّة السريعة للعملاء الجدد
         */
        function applyQuickDate(preset) {
            const fromInput = document.getElementById('dateFrom');
            const toInput = document.getElementById('dateTo');
            const presetInput = document.getElementById('datePreset');

            let fromDate = new Date();
            let toDate = new Date();

            fromDate.setHours(0, 0, 0, 0);
            toDate.setHours(23, 59, 59, 999);

            if (preset === 'today') {
                // today
            } else if (preset === 'yesterday') {
                fromDate.setDate(fromDate.getDate() - 1);
                toDate.setDate(toDate.getDate() - 1);
            } else if (preset === 'last2days') {
                fromDate.setDate(fromDate.getDate() - 2);
            } else if (preset === 'thisWeek') {
                // بداية الأسبوع
                const day = fromDate.getDay();
                const diff = fromDate.getDate() - day + (day === 0 ? -6 : 1);
                fromDate.setDate(diff);
            } else if (preset === 'thisMonth') {
                fromDate.setDate(1); // أول الشهر
            } else if (preset === 'lastMonth') {
                fromDate.setMonth(fromDate.getMonth() - 1);
                fromDate.setDate(1);

                toDate = new Date(fromDate.getFullYear(), fromDate.getMonth() + 1, 0, 23, 59, 59);
            }

            const formatDate = (d) => {
                let month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            };

            fromInput.value = formatDate(fromDate);
            toInput.value = formatDate(toDate);
            presetInput.value = preset;

            document.getElementById('filterForm').submit();
        }

        /**
         * تفريغ فلتر التاريخ فقط
         */
        function clearDateFilter() {
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            document.getElementById('datePreset').value = '';
            document.getElementById('filterForm').submit();
        }
    </script>
@endpush
