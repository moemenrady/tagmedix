@extends('layouts.app')

@section('title', 'الاوردرات المشحونة')

@section('content')
    <div class="space-y-6 animate-fade-in pb-20 md:pb-6">
        @if (auth()->user() && auth()->user()->role == 'admin')
            <!-- 1. شريط الإحصائيات (Statistics) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- إجمالي الطلبات -->
                <div
                    class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-roseAccent opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold" data-ar="إجمالي الطلبات" data-en="Total Orders">
                                إجمالي الطلبات</p>
                            <h3 class="text-2xl font-bold text-darkText">{{ number_format($statistics['total']) }}</h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-primaryBg flex items-center justify-center text-primaryBrown">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                    </div>
                </div>
                {{-- إحصائيات الأدمن --}}
                <div
                    class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-primaryBrown opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold" data-ar="قيمة الطلبات" data-en="Total Amount">
                                قيمة
                                الطلبات</p>
                            <h3 class="text-2xl font-bold text-darkText">{{ number_format($statistics['total_amount'], 0) }}
                                <span class="text-sm font-normal">ج.م</span>
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-primaryBg flex items-center justify-center text-primaryBrown">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>

                <!-- الإجمالي المحصل -->
                <div
                    class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-green-500 opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold" data-ar="المبلغ المحصل"
                                data-en="Collected Amount">المبلغ المحصل</p>
                            <h3 class="text-2xl font-bold text-green-700">
                                {{ number_format($statistics['total_collected_amount'], 0) }} <span
                                    class="text-sm font-normal">ج.م</span></h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                </div>

                <!-- المرتجعات -->
                <div
                    class="bg-white rounded-2xl p-4 md:p-5 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-red-500 opacity-10 rounded-full -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-500">
                    </div>
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold" data-ar="قيمة المرتجعات"
                                data-en="Return Amount">
                                قيمة المرتجعات</p>
                            <h3 class="text-2xl font-bold text-red-700">
                                {{ number_format($statistics['total_return_amount'], 0) }} <span
                                    class="text-sm font-normal">ج.م</span></h3>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. الفلاتر والبحث (Filters & Search) -->
        <div
            class="bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream p-4 md:p-5">
            <form id="filterForm" method="GET" action="{{ route('shipments.index') }}" class="space-y-4">

                <!-- البحث السريع والذكاء -->
                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute top-1/2 rtl:right-4 ltr:left-4 -translate-y-1/2 text-mutedBrown"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                        class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl py-3.5 rtl:pr-11 rtl:pl-4 ltr:pl-11 ltr:pr-4 focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all text-sm font-bold"
                        placeholder="ابحث بالاسم، الرقم، رقم التتبع، الفاتورة..."
                        data-ar="ابحث بالاسم، الرقم، رقم التتبع، الفاتورة..."
                        data-en="Search by name, phone, tracking, invoice...">
                </div>

                <!-- أزرار الفترات السريعة (Quick Dates) -->
                <div class="flex flex-nowrap overflow-x-auto gap-2 pb-2 scrollbar-hide items-center">
                    <span class="text-xs font-bold text-mutedBrown whitespace-nowrap ml-2"><i
                            class="fa-solid fa-filter mr-1"></i> الفترات:</span>

                    <button type="button" onclick="applyQuickDate('today')"
                        class="quick-date-btn {{ request('date_preset') == 'today' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-sun"></i> اليوم
                    </button>
                    <button type="button" onclick="applyQuickDate('yesterday')"
                        class="quick-date-btn {{ request('date_preset') == 'yesterday' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-moon"></i> أمس
                    </button>
                    <button type="button" onclick="applyQuickDate('last2days')"
                        class="quick-date-btn {{ request('date_preset') == 'last2days' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-day"></i> آخر يومين
                    </button>
                    <button type="button" onclick="applyQuickDate('last7days')"
                        class="quick-date-btn {{ request('date_preset') == 'last7days' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-week"></i> آخر 7 أيام
                    </button>
                    <button type="button" onclick="applyQuickDate('thisMonth')"
                        class="quick-date-btn {{ request('date_preset') == 'thisMonth' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-days"></i> هذا الشهر
                    </button>

                    <input type="hidden" name="from" id="dateFrom" value="{{ request('from') }}">
                    <input type="hidden" name="to" id="dateTo" value="{{ request('to') }}">
                    <input type="hidden" name="date_preset" id="datePreset" value="{{ request('date_preset') }}">
                </div>

                <!-- الفلاتر المنسدلة -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <select name="status" onchange="this.form.submit()"
                        class="bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs md:text-sm focus:outline-none focus:border-primaryBrown text-darkText font-medium">
                        <option value="">حالة الشحن (الكل)</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ $status }}</option>
                        @endforeach
                    </select>

                    <select name="internal_status" onchange="this.form.submit()"
                        class="bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs md:text-sm focus:outline-none focus:border-primaryBrown text-darkText font-medium">
                        <option value="">الحالة الداخلية (الكل)</option>
                        @foreach ($internalStatuses as $internal)
                            <option value="{{ $internal }}"
                                {{ request('internal_status') == $internal ? 'selected' : '' }}>{{ $internal }}
                            </option>
                        @endforeach
                    </select>

                    <select name="government" onchange="this.form.submit()"
                        class="bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs md:text-sm focus:outline-none focus:border-primaryBrown text-darkText font-medium">
                        <option value="">المحافظة (الكل)</option>
                        @foreach ($governments as $gov)
                            <option value="{{ $gov }}" {{ request('government') == $gov ? 'selected' : '' }}>
                                {{ $gov }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="bg-primaryBrown hover:bg-mutedBrown text-white rounded-xl px-4 py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-md">
                        <i class="fa-solid fa-filter"></i> تطبيق الفلاتر
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. عرض البيانات (Data View) -->
        @if ($shipments->count() > 0)
            <!-- Desktop View (Table) -->
            <div
                class="hidden md:block bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream overflow-hidden">
                <table class="w-full text-right">
                    <thead class="bg-primaryBg border-b border-secondaryCream">
                        <tr>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">رقم الطلب / البوليصة</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">العميل</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">العنوان</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">المبلغ</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">الحالة</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">التاريخ</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown text-center">
                                الإجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondaryCream">
                        @foreach ($shipments as $shipment)
                            <tr class="hover:bg-primaryBg/40 cursor-pointer transition-colors group"
                                onclick='openModal(@json($shipment))'>
                                <td class="py-3 px-5">
                                    <p class="text-sm font-bold text-primaryBrown">{{ $shipment->tracking_number ?? '-' }}
                                    </p>
                                    @if ($shipment->order)
                                        <p class="text-xs text-mutedBrown mt-0.5">طلب:
                                            #{{ $shipment->order->order_number }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-5">
                                    <p class="text-sm font-bold text-darkText">{{ $shipment->receiver }}</p>
                                    <p class="text-xs text-mutedBrown mt-0.5" dir="ltr">{{ $shipment->phone }}</p>
                                </td>
                                <td class="py-3 px-5">
                                    <p class="text-sm text-darkText truncate max-w-[150px]">{{ $shipment->government }}
                                    </p>
                                    <p class="text-xs text-mutedBrown mt-0.5 truncate max-w-[150px]">{{ $shipment->area }}
                                    </p>
                                </td>
                                <td class="py-3 px-5">
                                    <p class="text-sm font-bold text-darkText">{{ number_format($shipment->amount, 2) }}
                                        ج.م</p>
                                    @if ($shipment->collected_amount > 0)
                                        <p class="text-xs text-green-600 mt-0.5">حصل:
                                            {{ number_format($shipment->collected_amount, 0) }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-5">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border 
                                    {{ $shipment->status == 'Delivered'
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : ($shipment->status == 'Returned'
                                            ? 'bg-red-50 text-red-700 border-red-200'
                                            : 'bg-orange-50 text-orange-700 border-orange-200') }}">
                                        {{ $shipment->status ?? 'جارِ الشحن' }}
                                    </span>
                                </td>
                                <td class="py-3 px-5">
                                    <p class="text-xs text-darkText">{{ $shipment->created_at->format('Y-m-d') }}</p>
                                    <p class="text-[10px] text-mutedBrown mt-0.5">
                                        {{ $shipment->created_at->format('h:i A') }}</p>
                                </td>
                                <td class="py-3 px-5 text-center">
                                    @if ($shipment->order)
                                        <a href="{{ route('orders.show', $shipment->order->id) }}"
                                            onclick="event.stopPropagation()"
                                            class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl
                   bg-primaryBg text-primaryBrown border border-secondaryCream
                   hover:bg-primaryBrown hover:text-white
                   transition-all duration-200 text-xs font-bold shadow-sm"
                                            title="فتح تفاصيل الطلب">
                                            <i class="fa-solid fa-file-lines"></i>
                                            <span>تفاصيل الطلب</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-mutedBrown">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (Cards) -->
            <div class="md:hidden space-y-3">
                @foreach ($shipments as $shipment)
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-secondaryCream cursor-pointer hover:border-primaryBrown transition-colors active:scale-95"
                        onclick='openModal(@json($shipment))'>
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-sm font-bold text-primaryBrown">
                                    {{ $shipment->tracking_number ?? 'لا يوجد بوليصة' }}</p>
                                <p class="text-xs text-mutedBrown mt-0.5">{{ $shipment->receiver }}</p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold border 
                            {{ $shipment->status == 'Delivered'
                                ? 'bg-green-50 text-green-700 border-green-200'
                                : ($shipment->status == 'Returned'
                                    ? 'bg-red-50 text-red-700 border-red-200'
                                    : 'bg-orange-50 text-orange-700 border-orange-200') }}">
                                {{ $shipment->status ?? 'جارِ' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-end mt-3 pt-3 border-t border-secondaryCream/50">
                            <div>
                                <p class="text-[10px] text-mutedBrown"><i class="fa-solid fa-location-dot mr-1"></i>
                                    {{ $shipment->government }} - {{ $shipment->area }}</p>
                                <p class="text-[10px] text-mutedBrown mt-1" dir="ltr"><i
                                        class="fa-solid fa-phone mr-1"></i> {{ $shipment->phone }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-bold text-darkText">{{ number_format($shipment->amount, 0) }} ج.م
                                </p>
                                <p class="text-[9px] text-mutedBrown">{{ $shipment->created_at->format('Y-m-d') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
                @if ($shipment->order)
                    <div class="mt-3 pt-3 border-t border-secondaryCream/50">
                        <a href="{{ route('orders.show', $shipment->order->id) }}" onclick="event.stopPropagation()"
                            class="w-full inline-flex items-center justify-center gap-2
                   bg-primaryBrown hover:bg-mutedBrown text-white
                   rounded-xl py-2.5 text-xs font-bold
                   transition-all duration-200 shadow-sm">
                            <i class="fa-solid fa-file-lines"></i>
                            فتح تفاصيل الطلب
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center w-full pagination-wrapper">
                {{ $shipments->links() }}
            </div>
        @else
            <div
                class="bg-white rounded-2xl shadow-sm border border-secondaryCream p-10 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-primaryBg rounded-full flex items-center justify-center text-softBeige mb-4">
                    <i class="fa-solid fa-box-open text-3xl"></i>
                </div>
                <h4 class="text-lg font-bold text-darkText">لا توجد طلبات مشحونة</h4>
                <p class="text-sm text-mutedBrown mt-1">لم يتم العثور على أي نتائج تطابق الفلاتر المحددة.</p>
            </div>
        @endif

    </div>

    <!-- 4. المودال (Modal) لتفاصيل الأوردر -->
    <div id="shipmentModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <!-- خلفية شفافة للإغلاق -->
        <div class="absolute inset-0 bg-darkText/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <!-- محتوى المودال -->
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden relative z-10 flex flex-col transform scale-95 opacity-0 transition-all duration-300"
            id="modalContent">

            <!-- Header -->
            <div class="bg-primaryBg/50 border-b border-secondaryCream p-5 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-darkText flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-primaryBrown"></i>
                        تفاصيل الشحنة <span id="m_tracking" class="text-primaryBrown"></span>
                    </h3>
                    <p id="m_order_num" class="text-xs text-mutedBrown mt-1"></p>
                </div>
                <button onclick="closeModal()"
                    class="w-8 h-8 rounded-full bg-white border border-secondaryCream flex items-center justify-center text-mutedBrown hover:bg-roseAccent hover:text-white hover:border-roseAccent transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 overflow-y-auto space-y-5 custom-scrollbar">

                <!-- معلومات العميل -->
                <div class="bg-white border border-secondaryCream rounded-2xl p-4 shadow-sm relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-primaryBrown"></div>
                    <h4 class="text-xs font-bold text-mutedBrown mb-3"><i class="fa-regular fa-user mr-1"></i> معلومات
                        العميل</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] text-softBeige font-bold">الاسم</p>
                            <p id="m_receiver" class="text-sm font-bold text-darkText mt-0.5"></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-softBeige font-bold">الهاتف</p>
                            <p id="m_phone" class="text-sm font-bold text-darkText mt-0.5" dir="ltr"></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] text-softBeige font-bold">العنوان</p>
                            <p id="m_address" class="text-sm font-medium text-darkText mt-0.5"></p>
                        </div>
                    </div>
                </div>

                <!-- تفاصيل الشحن والمالية -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white border border-secondaryCream rounded-2xl p-4 shadow-sm relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-roseAccent"></div>
                        <h4 class="text-xs font-bold text-mutedBrown mb-3"><i class="fa-solid fa-truck-fast mr-1"></i>
                            حالة الشحن</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b border-primaryBg">
                                <span class="text-xs text-mutedBrown">حالة شركة الشحن</span>
                                <span id="m_status" class="text-xs font-bold px-2 py-1 rounded-md bg-primaryBg"></span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-primaryBg">
                                <span class="text-xs text-mutedBrown">الحالة الداخلية</span>
                                <span id="m_internal_status" class="text-xs font-bold text-darkText"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-mutedBrown">نوع التوصيل</span>
                                <span id="m_delivery_type" class="text-xs font-bold text-darkText"></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-secondaryCream rounded-2xl p-4 shadow-sm relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-green-500"></div>
                        <h4 class="text-xs font-bold text-mutedBrown mb-3"><i
                                class="fa-solid fa-money-bill-wave mr-1"></i> المالية</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-2 border-b border-primaryBg">
                                <span class="text-xs text-mutedBrown">المبلغ الإجمالي</span>
                                <span id="m_amount" class="text-sm font-bold text-darkText"></span>
                            </div>
                            <div class="flex justify-between items-center pb-2 border-b border-primaryBg">
                                <span class="text-xs text-mutedBrown">المحصل</span>
                                <span id="m_collected" class="text-sm font-bold text-green-600"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-mutedBrown">المرتجع</span>
                                <span id="m_return" class="text-sm font-bold text-red-500"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- التواريخ والملاحظات -->
                <div class="bg-primaryBg/30 border border-secondaryCream rounded-2xl p-4">
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <p class="text-[10px] text-mutedBrown">تاريخ الإنشاء</p>
                            <p id="m_created" class="text-xs font-bold text-darkText"></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-mutedBrown">آخر تحديث</p>
                            <p id="m_synced" class="text-xs font-bold text-darkText"></p>
                        </div>
                    </div>
                    <div id="m_notes_container" class="mt-3 pt-3 border-t border-secondaryCream hidden">
                        <p class="text-[10px] text-mutedBrown mb-1"><i class="fa-regular fa-comment-dots"></i> الملاحظات:
                        </p>
                        <p id="m_notes"
                            class="text-xs font-medium text-darkText bg-white p-2 rounded-lg border border-secondaryCream">
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        /* تنسيقات الـ Pagination لتتناسب مع ألوان الموقع */
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
            border-radius: 0.5rem !important;
            border: 1px solid #DBD0C3;
            margin: 0;
            padding: 0.5rem 0.75rem;
            color: #503E39;
            background: white;
            font-weight: 600;
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

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #CABCB0;
            border-radius: 4px;
        }
    </style>

@endsection

@push('scripts')
    <script>
        // 1. نظام الفترات السريعة (Quick Dates)
        function applyQuickDate(type) {
            const fromInput = document.getElementById('dateFrom');
            const toInput = document.getElementById('dateTo');
            const presetInput = document.getElementById('datePreset');

            let fromDate = new Date();
            let toDate = new Date();

            // تصفير الوقت لتجنب أخطاء الفروق الزمنية
            fromDate.setHours(0, 0, 0, 0);
            toDate.setHours(23, 59, 59, 999);

            if (type === 'today') {
                // fromDate is already today
            } else if (type === 'yesterday') {
                fromDate.setDate(fromDate.getDate() - 1);
                toDate.setDate(toDate.getDate() - 1);
            } else if (type === 'last2days') {
                fromDate.setDate(fromDate.getDate() - 2);
            } else if (type === 'last7days') {
                fromDate.setDate(fromDate.getDate() - 7);
            } else if (type === 'thisMonth') {
                fromDate.setDate(1); // أول يوم في الشهر الحالي
            }

            const formatDate = (date) => {
                let d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            };

            fromInput.value = formatDate(fromDate);
            toInput.value = formatDate(toDate);
            presetInput.value = type;

            // تفعيل الفورم فوراً
            document.getElementById('filterForm').submit();
        }

        // 2. إدارة المودال الخاص بتفاصيل الأوردر
        const modal = document.getElementById('shipmentModal');
        const modalContent = document.getElementById('modalContent');

        function openModal(shipment) {
            // تعبئة البيانات
            document.getElementById('m_tracking').innerText = shipment.tracking_number || 'بدون بوليصة';
            document.getElementById('m_order_num').innerText = shipment.order ? 'رقم الطلب المرجعي: #' + shipment.order
                .order_number : '';

            document.getElementById('m_receiver').innerText = shipment.receiver || '-';
            document.getElementById('m_phone').innerText = shipment.phone + (shipment.phone2 ? ' / ' + shipment.phone2 :
                '');

            const address = [shipment.government, shipment.area, shipment.address].filter(Boolean).join(' - ');
            document.getElementById('m_address').innerText = address || '-';

            document.getElementById('m_status').innerText = shipment.status || 'مسجل';
            document.getElementById('m_internal_status').innerText = shipment.internal_status || '-';
            document.getElementById('m_delivery_type').innerText = shipment.delivery_type || '-';

            document.getElementById('m_amount').innerText = parseFloat(shipment.amount).toFixed(2) + ' ج.م';
            document.getElementById('m_collected').innerText = parseFloat(shipment.collected_amount || 0).toFixed(2) +
                ' ج.م';
            document.getElementById('m_return').innerText = parseFloat(shipment.return_amount || 0).toFixed(2) + ' ج.م';

            // التواريخ
            const createdAt = new Date(shipment.created_at);
            document.getElementById('m_created').innerText = createdAt.toLocaleDateString('ar-EG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const syncedAt = shipment.last_synced_at ? new Date(shipment.last_synced_at) : createdAt;
            document.getElementById('m_synced').innerText = syncedAt.toLocaleDateString('ar-EG', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // الملاحظات
            const notesContainer = document.getElementById('m_notes_container');
            if (shipment.notes) {
                document.getElementById('m_notes').innerText = shipment.notes;
                notesContainer.classList.remove('hidden');
            } else {
                notesContainer.classList.add('hidden');
            }

            // إظهار المودال وأنيميشن الدخول
            modal.classList.remove('hidden');
            // timeout صغير جداً للسماح للـ CSS برصد الـ hidden removal قبل تطبيق الـ transition
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); // نفس مدة الـ transition
        }
    </script>
@endpush
