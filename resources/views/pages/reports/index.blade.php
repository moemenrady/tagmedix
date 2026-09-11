@extends('layouts.app')

@section('title', 'تقارير الأداء والمبيعات الشاملة')
@section('page_title', 'التقارير التحليلية المتقدمة')

@section('content')
<div class="space-y-6 page-transition pb-10" dir="rtl">

    <!-- Top Header & Filter Presets -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-gray-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-primaryBrown/10 text-primaryBrown flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-darkText" data-ar="لوحة التقارير والتحليلات الشاملة" data-en="Comprehensive Reports & Analytics">لوحة التقارير والتحليلات الشاملة</h1>
                    <p class="text-xs text-gray-500 mt-0.5" data-ar="مؤشرات أداء المبيعات، خطوط الإنتاج، تفاصيل المقاسات والألوان، والشحن" data-en="Sales performance, production pipeline, variations, and logistics KPIs">مؤشرات أداء المبيعات، خطوط الإنتاج، تفاصيل المقاسات والألوان، والشحن</p>
                </div>
            </div>
        </div>

        <!-- Period Quick Filters & Actions -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Period Pills -->
            <div class="bg-gray-50 p-1 rounded-xl border border-gray-200 flex items-center gap-1 text-xs font-semibold">
                <a href="{{ route('reports.index', array_merge(request()->except(['period', 'from_date', 'to_date']), ['period' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ ($filters['period'] ?? 'all') === 'all' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText hover:bg-white' }}">
                    الكل
                </a>
                <a href="{{ route('reports.index', array_merge(request()->except(['period', 'from_date', 'to_date']), ['period' => 'this_month'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ ($filters['period'] ?? '') === 'this_month' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText hover:bg-white' }}">
                    هذا الشهر
                </a>
                <a href="{{ route('reports.index', array_merge(request()->except(['period', 'from_date', 'to_date']), ['period' => 'last_30_days'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ ($filters['period'] ?? '') === 'last_30_days' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText hover:bg-white' }}">
                    آخر 30 يوم
                </a>
                <a href="{{ route('reports.index', array_merge(request()->except(['period', 'from_date', 'to_date']), ['period' => 'this_week'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ ($filters['period'] ?? '') === 'this_week' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText hover:bg-white' }}">
                    هذا الأسبوع
                </a>
                <a href="{{ route('reports.index', array_merge(request()->except(['period', 'from_date', 'to_date']), ['period' => 'today'])) }}"
                   class="px-3 py-1.5 rounded-lg transition {{ ($filters['period'] ?? '') === 'today' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText hover:bg-white' }}">
                    اليوم
                </a>
            </div>

            <!-- Toggle Advanced Filters -->
            <button type="button" onclick="document.getElementById('advancedFilterPanel').classList.toggle('hidden')"
                    class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-2 transition shadow-sm">
                <i class="fa-solid fa-filter text-primaryBrown"></i>
                <span>تصفية مخصصة</span>
                @if(request('from_date') || request('to_date') || (request('status') && request('status') !== 'all') || (request('governorate') && request('governorate') !== 'all') || (request('salary_type') && request('salary_type') !== 'all'))
                    <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                @endif
            </button>

            <!-- Export Dropdown -->
            <div class="relative group">
                <button type="button" class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 flex items-center gap-2 transition shadow-sm">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>تصدير Excel/CSV</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div class="hidden group-hover:block absolute left-0 top-full pt-1 z-30 w-56">
                    <div class="bg-white rounded-xl shadow-xl border border-gray-100 py-2 text-xs font-medium text-gray-700 divide-y divide-gray-50">
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv', 'export_type' => 'orders'])) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i> تقرير الطلبات والتحصيل
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv', 'export_type' => 'styles'])) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fa-solid fa-shirt text-emerald-600"></i> تقرير مبيعات الموديلات
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv', 'export_type' => 'sizes_colors'])) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fa-solid fa-palette text-emerald-600"></i> تقرير المقاسات والألوان
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv', 'export_type' => 'governorates'])) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fa-solid fa-map-location-dot text-emerald-600"></i> تقرير المحافظات
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv', 'export_type' => 'employees'])) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fa-solid fa-user-gear text-emerald-600"></i> تقرير إنتاجية الموظفين
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->all(), ['export' => 'csv', 'export_type' => 'turbo_shipments'])) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fa-solid fa-truck-fast text-emerald-600"></i> شحنات وبوالص Turbo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Print Button -->
            <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-gray-900 text-white text-xs font-bold hover:bg-black flex items-center gap-2 transition shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span class="hidden sm:inline">طباعة التقرير</span>
            </button>
        </div>
    </div>

    <!-- Advanced Filter Collapsible Drawer -->
    <div id="advancedFilterPanel" class="{{ (request('from_date') || request('to_date') || (request('status') && request('status') !== 'all') || (request('governorate') && request('governorate') !== 'all') || (request('salary_type') && request('salary_type') !== 'all')) ? '' : 'hidden' }} bg-white rounded-2xl p-5 border border-gray-200 shadow-sm transition-all">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end text-xs">
            <input type="hidden" name="period" value="custom">

            <div>
                <label class="block font-bold text-gray-600 mb-1.5">من تاريخ:</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primaryBrown/30">
            </div>

            <div>
                <label class="block font-bold text-gray-600 mb-1.5">إلى تاريخ:</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primaryBrown/30">
            </div>

            <div>
                <label class="block font-bold text-gray-600 mb-1.5">حالة الطلب:</label>
                <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primaryBrown/30">
                    <option value="all">جميع الحالات</option>
                    @foreach($allStatuses as $st)
                        <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>
                            @switch($st)
                                @case('new') جديد @break
                                @case('cutting') مرحلة القص @break
                                @case('sewing') مرحلة الخياطة @break
                                @case('finishing') مرحلة التشطيب @break
                                @case('ready-to-ship') جاهز للشحن @break
                                @case('shipped') تم الشحن @break
                                @case('delivered') تم التسليم @break
                                @case('canceled') ملغي @break
                                @default {{ $st }}
                            @endswitch
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-gray-600 mb-1.5">المحافظة:</label>
                <select name="governorate" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primaryBrown/30">
                    <option value="all">جميع المحافظات</option>
                    @foreach($allGovernorates as $gov)
                        <option value="{{ $gov }}" {{ ($filters['governorate'] ?? '') === $gov ? 'selected' : '' }}>{{ $gov }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-primaryBrown text-white font-bold py-2.5 px-4 rounded-xl hover:bg-mutedBrown transition shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> تطبيق
                </button>
                <a href="{{ route('reports.index') }}" class="bg-gray-100 text-gray-600 font-bold py-2.5 px-3 rounded-xl hover:bg-gray-200 transition text-center" title="إعادة تعيين">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Executive Secondary KPI Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Metric 1: Customers -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 font-bold block uppercase">العملاء المسجلين</span>
                <span class="text-sm font-black text-darkText">{{ number_format($kpi['totalCustomers']) }} عميل</span>
            </div>
        </div>

        <!-- Metric 2: Active Workforce -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
                <i class="fa-solid fa-user-gear"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 font-bold block uppercase">العمالة النشطة</span>
                <span class="text-sm font-black text-darkText">{{ $kpi['activeEmployees'] }} موظف <span class="text-[10px] text-gray-400 font-normal">({{ $kpi['pieceRateEmployees'] }} بالقطعة)</span></span>
            </div>
        </div>

        <!-- Metric 3: Lead Time -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm">
                <i class="fa-solid fa-stopwatch"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 font-bold block uppercase">دورة الإنتاج (Lead Time)</span>
                <span class="text-sm font-black text-darkText">{{ $kpi['avgLeadTimeDays'] }} يوم <span class="text-[10px] text-gray-400 font-normal">({{ $kpi['avgLeadTimeHours'] }} س)</span></span>
            </div>
        </div>

        <!-- Metric 4: Delivered Revenue -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 font-bold block uppercase">المبيعات المحققة</span>
                <span class="text-sm font-black text-emerald-700">{{ number_format($kpi['deliveredRevenue'], 0) }} ج.م <span class="text-[10px] font-semibold text-emerald-600">({{ $kpi['deliveredRevenueRate'] }}%)</span></span>
            </div>
        </div>

        <!-- Metric 5: In Pipeline Revenue -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                <i class="fa-solid fa-dolly"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 font-bold block uppercase">قيد التصنيع والإنتاج</span>
                <span class="text-sm font-black text-amber-700">{{ number_format($kpi['pipelineRevenue'], 0) }} ج.م</span>
            </div>
        </div>

        <!-- Metric 6: Cancellation Rate -->
        <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg {{ $kpi['cancellationRate'] > 0 ? 'bg-rose-50 text-rose-600' : 'bg-green-50 text-green-600' }} flex items-center justify-center text-sm">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 font-bold block uppercase">معدل الإلغاء</span>
                <span class="text-sm font-black {{ $kpi['cancellationRate'] > 0 ? 'text-rose-600' : 'text-green-700' }}">{{ $kpi['cancellationRate'] }}% <span class="text-[10px] text-gray-400 font-normal">({{ $kpi['canceledOrdersCount'] }} طلب)</span></span>
            </div>
        </div>
    </div>

    <!-- Core Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- KPI 1: Gross Sales -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">إجمالي المبيعات الكلية</p>
                    <h3 class="text-2xl font-black text-darkText">{{ number_format($kpi['totalRevenue'], 0) }} <span class="text-xs font-semibold text-gray-500">ج.م</span></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-500">
                <span>متوسط الطلب:</span>
                <span class="font-bold text-darkText">{{ number_format($kpi['avgOrderValue'], 0) }} ج.م</span>
            </div>
        </div>

        <!-- KPI 2: Paid Advances -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">المحصل مقدماً</p>
                    <h3 class="text-2xl font-black text-emerald-600">{{ number_format($kpi['totalPaidAdvances'], 0) }} <span class="text-xs font-semibold text-gray-500">ج.م</span></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-50">
                <div class="flex justify-between items-center text-[11px] mb-1">
                    <span class="text-gray-500">نسبة التحصيل:</span>
                    <span class="font-bold text-emerald-700">{{ $kpi['advanceCollectionRate'] }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min(100, $kpi['advanceCollectionRate']) }}%"></div>
                </div>
            </div>
        </div>

        <!-- KPI 3: Remaining Due (COD) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">المتبقي عند التسليم</p>
                    <h3 class="text-2xl font-black text-rose-600">{{ number_format($kpi['remainingDue'], 0) }} <span class="text-xs font-semibold text-gray-500">ج.م</span></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-500">
                <span>يتم تحصيله مع الشحن</span>
                <span class="font-bold text-rose-600">{{ 100 - min(100, $kpi['advanceCollectionRate']) }}%</span>
            </div>
        </div>

        <!-- KPI 4: Total Orders & Pieces -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">الطلبات والقطع</p>
                    <h3 class="text-2xl font-black text-darkText">{{ number_format($kpi['totalOrders']) }} <span class="text-xs font-semibold text-gray-500">طلب</span></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-primaryBrown/10 text-primaryBrown flex items-center justify-center text-lg">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-500">
                <span>إجمالي القطع المباعة:</span>
                <span class="font-bold text-primaryBrown">{{ number_format($kpi['totalPiecesSold']) }} قطعة</span>
            </div>
        </div>

        <!-- KPI 5: Shipments & Logistics -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">شحنات Turbo</p>
                    <h3 class="text-2xl font-black text-indigo-600">{{ number_format($kpi['totalShipments']) }} <span class="text-xs font-semibold text-gray-500">شحنة</span></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px] text-gray-500">
                <span>قيمة بوالص التوصيل:</span>
                <span class="font-bold text-indigo-700">{{ number_format($kpi['totalShipmentAmount'], 0) }} ج.م</span>
            </div>
        </div>
    </div>

    <!-- Charts Grid Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Sales & Orders Timeline (2 Cols) -->
        <div class="lg:col-span-2 bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-black text-base text-darkText flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-primaryBrown"></i> مسار المبيعات وحجم الطلبات اليومي
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">تتبع الإيرادات وعدد الطلبات عبر الأيام</p>
                </div>
                <span class="text-[11px] bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg font-bold">آخر النشاطات</span>
            </div>
            <div class="h-72 relative">
                <canvas id="salesTimelineChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Order Pipeline Status (1 Col) -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="font-black text-base text-darkText flex items-center gap-2">
                        <i class="fa-solid fa-circle-notch text-roseAccent"></i> مراحل دورة العمل والإنتاج
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">حالة الطلبات في خطوط المصنع والتسليم</p>
                </div>
            </div>
            <div class="h-60 relative flex items-center justify-center my-auto">
                <canvas id="orderStatusChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-[10px] pt-3 border-t border-gray-100 mt-2">
                @foreach($statusCounts->take(6) as $st)
                <div class="bg-gray-50 rounded-lg p-1.5">
                    <span class="text-gray-400 block truncate">{{ $st->status }}</span>
                    <span class="font-bold text-darkText">{{ $st->count }} طلب</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Charts Grid Row 2: Top Selling Designs & Governorates -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 3: Top Selling Styles -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-black text-base text-darkText flex items-center gap-2">
                        <i class="fa-solid fa-shirt text-primaryBrown"></i> أعلى الموديلات والتصاميم مبيعاً
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">ترتيب الموديلات بحسب إجمالي القطع المطلوبة</p>
                </div>
            </div>
            <div class="h-72 relative">
                <canvas id="topStylesChart"></canvas>
            </div>
        </div>

        <!-- Chart 4: Top Governorates -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-black text-base text-darkText flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-roseAccent"></i> التوزيع الجغرافي للطلبات (المحافظات)
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">أكثر المحافظات طلباً من متجر تجميدكس</p>
                </div>
            </div>
            <div class="h-72 relative">
                <canvas id="topGovChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Attributes Cards: Sizes and Colors Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sizes Breakdown -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <h3 class="font-black text-sm text-darkText flex items-center gap-2">
                    <i class="fa-solid fa-up-right-and-down-left-from-center text-primaryBrown"></i> توزيع المقاسات الأكثر طلباً (Sizes)
                </h3>
                <span class="text-[11px] font-bold text-gray-400">إجمالي: {{ number_format($kpi['totalPiecesSold']) }} قطعة</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($topSizes->take(6) as $sz)
                @php
                    $szShare = $kpi['totalPiecesSold'] > 0 ? round(($sz->total_quantity / $kpi['totalPiecesSold']) * 100, 1) : 0;
                @endphp
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-black text-base text-darkText font-mono">{{ $sz->size_name }}</span>
                        <span class="text-xs font-bold text-primaryBrown">{{ number_format($sz->total_quantity) }} ق</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden mt-2">
                        <div class="bg-primaryBrown h-1.5 rounded-full" style="width: {{ min(100, $szShare) }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 text-left font-semibold">{{ $szShare }}%</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Colors Breakdown -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <h3 class="font-black text-sm text-darkText flex items-center gap-2">
                    <i class="fa-solid fa-palette text-roseAccent"></i> توزيع الألوان الأكثر طلباً (Colors)
                </h3>
                <span class="text-[11px] font-bold text-gray-400">{{ $topColors->count() }} لون</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($topColors->take(6) as $cl)
                @php
                    $clShare = $kpi['totalPiecesSold'] > 0 ? round(($cl->total_quantity / $kpi['totalPiecesSold']) * 100, 1) : 0;
                @endphp
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 flex flex-col justify-between">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-xs text-darkText flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full border border-gray-300 inline-block"
                                  style="background-color: {{ $cl->color_name === 'ابيض' ? '#FFFFFF' : ($cl->color_name === 'اسود' ? '#000000' : ($cl->color_name === 'كحلى' ? '#1E3A8A' : ($cl->color_name === 'رصاصى' ? '#6B7280' : ($cl->color_name === 'بترولى' ? '#0E7490' : '#C19077')))) }}"></span>
                            {{ $cl->color_name }}
                        </span>
                        <span class="text-xs font-bold text-roseAccent">{{ number_format($cl->total_quantity) }} ق</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden mt-2">
                        <div class="bg-roseAccent h-1.5 rounded-full" style="width: {{ min(100, $clShare) }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 text-left font-semibold">{{ $clShare }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tabbed Detailed Tables Section -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <!-- Tabs Header Bar -->
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
            <!-- Tabs Navigation Buttons -->
            <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0" id="reportsTabsHeader">
                <button type="button" onclick="switchReportTab('stylesTab', this)"
                        class="report-tab-btn active-tab px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-shirt"></i> تقرير الموديلات ({{ $topStyles->count() }})
                </button>
                <button type="button" onclick="switchReportTab('variationsTab', this)"
                        class="report-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:text-darkText hover:bg-gray-100 transition flex items-center gap-2">
                    <i class="fa-solid fa-palette"></i> المقاسات والألوان
                </button>
                <button type="button" onclick="switchReportTab('governoratesTab', this)"
                        class="report-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:text-darkText hover:bg-gray-100 transition flex items-center gap-2">
                    <i class="fa-solid fa-map"></i> تقرير المحافظات ({{ $topGovernorates->count() }})
                </button>
                <button type="button" onclick="switchReportTab('employeesTab', this)"
                        class="report-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:text-darkText hover:bg-gray-100 transition flex items-center gap-2">
                    <i class="fa-solid fa-user-gear"></i> إنتاجية الورشة ({{ $employeeWorkload->count() }})
                </button>
                <button type="button" onclick="switchReportTab('financialTab', this)"
                        class="report-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:text-darkText hover:bg-gray-100 transition flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice-dollar"></i> ميزان الطلبات والتحصيل
                </button>
                <button type="button" onclick="switchReportTab('turboTab', this)"
                        class="report-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:text-darkText hover:bg-gray-100 transition flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast"></i> شحنات Turbo ({{ $kpi['totalShipments'] }})
                </button>
            </div>

            <!-- Table Quick Search -->
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="reportTableSearch" placeholder="بحث سريع في الجدول المعروض..."
                       class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primaryBrown/30 transition">
            </div>
        </div>

        <!-- Tab 1: Styles Report -->
        <div id="stylesTab" class="report-tab-pane overflow-x-auto">
            <table class="w-full text-xs text-right whitespace-nowrap report-data-table">
                <thead class="text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">اسم الموديل / التصميم</th>
                        <th class="px-5 py-3.5 text-center">القطع المباعة</th>
                        <th class="px-5 py-3.5">إجمالي المبيعات</th>
                        <th class="px-5 py-3.5 text-center">عدد الطلبات</th>
                        <th class="px-5 py-3.5">متوسط سعر القطعة</th>
                        <th class="px-5 py-3.5">نسبة المساهمة من القطع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($topStyles as $idx => $style)
                    @php
                        $qtyShare = $kpi['totalPiecesSold'] > 0 ? round(($style->total_quantity / $kpi['totalPiecesSold']) * 100, 1) : 0;
                        $avgPrice = $style->total_quantity > 0 ? round($style->total_sales / $style->total_quantity, 1) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition search-row">
                        <td class="px-5 py-3.5 font-bold text-gray-400">{{ $idx + 1 }}</td>
                        <td class="px-5 py-3.5 font-bold text-darkText flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primaryBrown inline-block"></span>
                            {{ $style->style_name }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-black text-primaryBrown text-sm">{{ number_format($style->total_quantity) }}</td>
                        <td class="px-5 py-3.5 font-bold text-darkText">{{ number_format($style->total_sales, 2) }} ج.م</td>
                        <td class="px-5 py-3.5 text-center">{{ number_format($style->orders_count) }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ number_format($avgPrice, 2) }} ج.م</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-primaryBrown h-2 rounded-full" style="width: {{ min(100, $qtyShare) }}%"></div>
                                </div>
                                <span class="font-semibold text-[11px] text-gray-500">{{ $qtyShare }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-400 font-semibold">لا توجد بيانات متاحة لهذا النطاق</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tab 2: Variations & Attributes (Sizes & Colors) -->
        <div id="variationsTab" class="report-tab-pane hidden overflow-x-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x md:divide-x-reverse divide-gray-100">
                <!-- Sizes Table -->
                <div class="p-4">
                    <h4 class="font-bold text-xs text-darkText mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-up-right-and-down-left-from-center text-primaryBrown"></i> تقرير مبيعات المقاسات (Sizes)
                    </h4>
                    <table class="w-full text-xs text-right whitespace-nowrap report-data-table">
                        <thead class="text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-3 py-2">المقاس</th>
                                <th class="px-3 py-2 text-center">القطع</th>
                                <th class="px-3 py-2">إجمالي المبيعات</th>
                                <th class="px-3 py-2">الحصة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($topSizes as $sz)
                            @php
                                $szShare = $kpi['totalPiecesSold'] > 0 ? round(($sz->total_quantity / $kpi['totalPiecesSold']) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition search-row">
                                <td class="px-3 py-2 font-black text-darkText font-mono">{{ $sz->size_name }}</td>
                                <td class="px-3 py-2 text-center font-bold text-primaryBrown">{{ number_format($sz->total_quantity) }}</td>
                                <td class="px-3 py-2 font-semibold">{{ number_format($sz->total_sales, 2) }} ج.م</td>
                                <td class="px-3 py-2 text-gray-500 font-semibold">{{ $szShare }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Colors Table -->
                <div class="p-4">
                    <h4 class="font-bold text-xs text-darkText mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-palette text-roseAccent"></i> تقرير مبيعات الألوان (Colors)
                    </h4>
                    <table class="w-full text-xs text-right whitespace-nowrap report-data-table">
                        <thead class="text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-3 py-2">اللون</th>
                                <th class="px-3 py-2 text-center">القطع</th>
                                <th class="px-3 py-2">إجمالي المبيعات</th>
                                <th class="px-3 py-2">الحصة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($topColors as $cl)
                            @php
                                $clShare = $kpi['totalPiecesSold'] > 0 ? round(($cl->total_quantity / $kpi['totalPiecesSold']) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition search-row">
                                <td class="px-3 py-2 font-bold text-darkText">{{ $cl->color_name }}</td>
                                <td class="px-3 py-2 text-center font-bold text-roseAccent">{{ number_format($cl->total_quantity) }}</td>
                                <td class="px-3 py-2 font-semibold">{{ number_format($cl->total_sales, 2) }} ج.م</td>
                                <td class="px-3 py-2 text-gray-500 font-semibold">{{ $clShare }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 3: Governorates Report -->
        <div id="governoratesTab" class="report-tab-pane hidden overflow-x-auto">
            <table class="w-full text-xs text-right whitespace-nowrap report-data-table">
                <thead class="text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">المحافظة</th>
                        <th class="px-5 py-3.5 text-center">عدد الطلبات</th>
                        <th class="px-5 py-3.5">إجمالي المبيعات</th>
                        <th class="px-5 py-3.5">متوسط الطلب</th>
                        <th class="px-5 py-3.5">نسبة الطلبات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($topGovernorates as $idx => $gov)
                    @php
                        $govShare = $kpi['totalOrders'] > 0 ? round(($gov->orders_count / $kpi['totalOrders']) * 100, 1) : 0;
                        $govAvg = $gov->orders_count > 0 ? round($gov->total_sales / $gov->orders_count, 1) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition search-row">
                        <td class="px-5 py-3.5 font-bold text-gray-400">{{ $idx + 1 }}</td>
                        <td class="px-5 py-3.5 font-bold text-darkText flex items-center gap-2">
                            <i class="fa-solid fa-map-pin text-roseAccent"></i>
                            {{ $gov->governorate }}
                        </td>
                        <td class="px-5 py-3.5 text-center font-bold text-indigo-700">{{ number_format($gov->orders_count) }}</td>
                        <td class="px-5 py-3.5 font-bold text-darkText">{{ number_format($gov->total_sales, 2) }} ج.م</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ number_format($govAvg, 2) }} ج.م</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-roseAccent h-2 rounded-full" style="width: {{ min(100, $govShare) }}%"></div>
                                </div>
                                <span class="font-semibold text-[11px] text-gray-500">{{ $govShare }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-400 font-semibold">لا توجد محافظات مطابقة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tab 4: Employees / Production Workload -->
        <div id="employeesTab" class="report-tab-pane hidden overflow-x-auto">
            <!-- Employee Sub-Filter by Salary Type -->
            <div class="p-3 bg-gray-50/70 border-b border-gray-100 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-600">تصفية حسب طبيعة الراتب:</span>
                <div class="flex items-center gap-1 text-xs">
                    <a href="{{ route('reports.index', array_merge(request()->all(), ['salary_type' => 'all'])) }}"
                       class="px-2.5 py-1 rounded-lg {{ ($filters['salary_type'] ?? 'all') === 'all' ? 'bg-primaryBrown text-white font-bold' : 'bg-white text-gray-600 border' }}">الكل</a>
                    <a href="{{ route('reports.index', array_merge(request()->all(), ['salary_type' => 'piece_rate'])) }}"
                       class="px-2.5 py-1 rounded-lg {{ ($filters['salary_type'] ?? '') === 'piece_rate' ? 'bg-primaryBrown text-white font-bold' : 'bg-white text-gray-600 border' }}">عمالة بالقطعة</a>
                    <a href="{{ route('reports.index', array_merge(request()->all(), ['salary_type' => 'monthly'])) }}"
                       class="px-2.5 py-1 rounded-lg {{ ($filters['salary_type'] ?? '') === 'monthly' ? 'bg-primaryBrown text-white font-bold' : 'bg-white text-gray-600 border' }}">رواتب شهرية</a>
                </div>
            </div>

            <table class="w-full text-xs text-right whitespace-nowrap report-data-table">
                <thead class="text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">اسم الموظف / الخياط</th>
                        <th class="px-5 py-3.5 text-center">نظام الراتب</th>
                        <th class="px-5 py-3.5 text-center">إجمالي المهام المنجزة</th>
                        <th class="px-5 py-3.5 text-center">مهام القص (Cutting)</th>
                        <th class="px-5 py-3.5 text-center">مهام الخياطة (Sewing)</th>
                        <th class="px-5 py-3.5 text-center">مهام التشطيب (Finishing)</th>
                        <th class="px-5 py-3.5 text-center">جاهز للشحن (Packing)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($employeeWorkload as $idx => $emp)
                    <tr class="hover:bg-gray-50/80 transition search-row">
                        <td class="px-5 py-3.5 font-bold text-gray-400">{{ $idx + 1 }}</td>
                        <td class="px-5 py-3.5 font-bold text-darkText flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-primaryBrown/10 text-primaryBrown flex items-center justify-center font-bold text-xs">
                                {{ mb_substr($emp->name, 0, 1) }}
                            </div>
                            <div>
                                <span>{{ $emp->name }}</span>
                                @if($emp->phone)
                                <span class="block text-[10px] text-gray-400">{{ $emp->phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($emp->salary_type === 'piece_rate')
                                <span class="bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-md">بالقطعة (خياطة)</span>
                            @else
                                <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-md">راتب شهري</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center font-black text-sm text-primaryBrown">{{ number_format($emp->total_tasks) }}</td>
                        <td class="px-5 py-3.5 text-center font-semibold text-orange-600">{{ number_format($emp->cutting_tasks) }}</td>
                        <td class="px-5 py-3.5 text-center font-semibold text-purple-600">{{ number_format($emp->sewing_tasks) }}</td>
                        <td class="px-5 py-3.5 text-center font-semibold text-blue-600">{{ number_format($emp->finishing_tasks) }}</td>
                        <td class="px-5 py-3.5 text-center font-semibold text-emerald-600">{{ number_format($emp->ready_tasks) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-400 font-semibold">لا توجد بيانات موظفين مسجلة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tab 5: Financial Balances & Orders -->
        <div id="financialTab" class="report-tab-pane hidden overflow-x-auto">
            <table class="w-full text-xs text-right whitespace-nowrap report-data-table">
                <thead class="text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5">رقم الطلب</th>
                        <th class="px-5 py-3.5">اسم العميل</th>
                        <th class="px-5 py-3.5">الهاتف</th>
                        <th class="px-5 py-3.5">المحافظة</th>
                        <th class="px-5 py-3.5">إجمالي الطلب</th>
                        <th class="px-5 py-3.5">تكلفة الشحن</th>
                        <th class="px-5 py-3.5">المقدم المدفوع</th>
                        <th class="px-5 py-3.5">المتبقي (COD)</th>
                        <th class="px-5 py-3.5 text-center">الحالة</th>
                        <th class="px-5 py-3.5">تاريخ الطلب</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($financialOrders as $order)
                    <tr class="hover:bg-gray-50/80 transition search-row">
                        <td class="px-5 py-3.5 font-bold text-darkText">#{{ $order->order_number }}</td>
                        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $order->customer_name ?: '-' }}</td>
                        <td class="px-5 py-3.5 text-gray-500 font-mono">{{ $order->customer_phone_1 ?: '-' }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $order->governorate ?: '-' }}</td>
                        <td class="px-5 py-3.5 font-bold text-darkText">{{ number_format($order->total_price, 2) }} ج.م</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ number_format($order->shipping_due_amount, 2) }} ج.م</td>
                        <td class="px-5 py-3.5 font-semibold text-emerald-600">{{ number_format($order->paid_amount, 2) }} ج.م</td>
                        <td class="px-5 py-3.5 font-bold {{ $order->remaining_amount > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                            {{ number_format($order->remaining_amount, 2) }} ج.م
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @switch($order->status)
                                @case('new')
                                    <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-md">جديد</span>
                                    @break
                                @case('cutting')
                                    <span class="bg-orange-50 text-orange-700 text-[10px] font-bold px-2 py-0.5 rounded-md">قص</span>
                                    @break
                                @case('sewing')
                                    <span class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-md">خياطة</span>
                                    @break
                                @case('finishing')
                                    <span class="bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-md">تشطيب</span>
                                    @break
                                @case('ready-to-ship')
                                    <span class="bg-teal-50 text-teal-700 text-[10px] font-bold px-2 py-0.5 rounded-md">جاهز للشحن</span>
                                    @break
                                @case('shipped')
                                    <span class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-md">تم الشحن</span>
                                    @break
                                @default
                                    <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2 py-0.5 rounded-md">{{ $order->status }}</span>
                            @endswitch
                        </td>
                        <td class="px-5 py-3.5 text-gray-400 text-[11px]">{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-8 text-gray-400 font-semibold">لا توجد طلبات مسجلة ضمن هذه المعايير</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tab 6: Turbo Shipments Logistics -->
        <div id="turboTab" class="report-tab-pane hidden overflow-x-auto">
            <table class="w-full text-xs text-right whitespace-nowrap report-data-table">
                <thead class="text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5">#</th>
                        <th class="px-5 py-3.5">رقم الطلب المرجعي</th>
                        <th class="px-5 py-3.5">المستلم</th>
                        <th class="px-5 py-3.5">رقم الهاتف</th>
                        <th class="px-5 py-3.5">المحافظة</th>
                        <th class="px-5 py-3.5">المنطقة</th>
                        <th class="px-5 py-3.5">مبلغ التحصيل (COD)</th>
                        <th class="px-5 py-3.5 text-center">حالة الشحنة</th>
                        <th class="px-5 py-3.5">تاريخ الإرسال</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($turboShipmentsList as $sh)
                    <tr class="hover:bg-gray-50/80 transition search-row">
                        <td class="px-5 py-3.5 font-bold text-gray-400">#{{ $sh->id }}</td>
                        <td class="px-5 py-3.5 font-bold text-darkText">{{ $sh->remote_order_id ?: '-' }}</td>
                        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $sh->receiver }}</td>
                        <td class="px-5 py-3.5 text-gray-500 font-mono">{{ $sh->phone }}</td>
                        <td class="px-5 py-3.5 text-gray-700">{{ $sh->government }}</td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $sh->area ?: '-' }}</td>
                        <td class="px-5 py-3.5 font-black text-indigo-700">{{ number_format($sh->amount, 2) }} ج.م</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="bg-indigo-50 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-md">{{ $sh->status ?: 'shipped' }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-400 text-[11px]">{{ \Carbon\Carbon::parse($sh->created_at)->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-400 font-semibold">لا توجد شحنات مسجلة</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Print Styles -->
<style>
@media print {
    /* Hide top nav, sidebar, footer, buttons, search when printing */
    header, aside, #reportsTabsHeader, #advancedFilterPanel, #reportTableSearch, button, .group, nav {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .report-tab-pane {
        display: block !important;
        margin-bottom: 2rem;
    }
    .shadow-sm, .shadow-md, .shadow-xl {
        box-shadow: none !important;
    }
    .border {
        border-color: #ddd !important;
    }
    canvas {
        max-width: 100% !important;
    }
}
</style>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Tab Switching Function
    function switchReportTab(tabId, btn) {
        document.querySelectorAll('.report-tab-pane').forEach(el => el.classList.add('hidden'));
        document.getElementById(tabId).classList.remove('hidden');

        document.querySelectorAll('.report-tab-btn').forEach(b => {
            b.classList.remove('active-tab', 'bg-primaryBrown', 'text-white');
            b.classList.add('text-gray-600', 'hover:bg-gray-100');
        });

        btn.classList.remove('text-gray-600', 'hover:bg-gray-100');
        btn.classList.add('active-tab');

        // Reset filter search
        const searchInput = document.getElementById('reportTableSearch');
        if (searchInput) {
            searchInput.value = '';
            filterVisibleTableRows('');
        }
    }

    // Quick Live Search in active table
    const searchInput = document.getElementById('reportTableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            filterVisibleTableRows(e.target.value.toLowerCase().trim());
        });
    }

    function filterVisibleTableRows(query) {
        const activePane = document.querySelector('.report-tab-pane:not(.hidden)');
        if (!activePane) return;

        const rows = activePane.querySelectorAll('tbody tr.search-row');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = (query === '' || text.includes(query)) ? '' : 'none';
        });
    }

    // Charts Initialization
    document.addEventListener('DOMContentLoaded', function() {
        const brandBrown = '#926857';
        const brandDark = '#2A2120';
        const brandRose = '#C19077';
        const brandSoftPink = '#EAAD99';
        const brandBg = '#DBD0C3';

        // 1. Sales Timeline Chart
        @php
            $timelineLabels = $salesTimeline->pluck('order_date')->map(function($d) {
                return \Carbon\Carbon::parse($d)->format('m/d');
            });
            $timelineRevenues = $salesTimeline->pluck('daily_revenue');
            $timelineOrders = $salesTimeline->pluck('orders_count');
        @endphp

        const ctxTimeline = document.getElementById('salesTimelineChart')?.getContext('2d');
        if (ctxTimeline) {
            new Chart(ctxTimeline, {
                data: {
                    labels: {!! json_encode($timelineLabels) !!},
                    datasets: [
                        {
                            type: 'line',
                            label: 'إجمالي المبيعات (ج.م)',
                            data: {!! json_encode($timelineRevenues) !!},
                            borderColor: brandBrown,
                            backgroundColor: 'rgba(146, 104, 87, 0.1)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                            pointBackgroundColor: brandBrown,
                            pointRadius: 4,
                            yAxisID: 'yRevenue',
                        },
                        {
                            type: 'bar',
                            label: 'عدد الطلبات',
                            data: {!! json_encode($timelineOrders) !!},
                            backgroundColor: 'rgba(193, 144, 119, 0.45)',
                            borderColor: brandRose,
                            borderWidth: 1,
                            borderRadius: 6,
                            yAxisID: 'yOrders',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            rtl: true,
                            labels: {
                                font: { family: 'Cairo', size: 11, weight: 'bold' },
                                boxWidth: 12
                            }
                        },
                        tooltip: {
                            rtl: true,
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.yAxisID === 'yRevenue') {
                                        return 'المبيعات: ' + Number(context.raw).toLocaleString() + ' ج.م';
                                    }
                                    return 'الطلبات: ' + context.raw + ' طلب';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Cairo', size: 10 } }
                        },
                        yRevenue: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                font: { family: 'Cairo', size: 10 },
                                callback: function(val) { return (val / 1000) + 'k'; }
                            }
                        },
                        yOrders: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                font: { family: 'Cairo', size: 10 },
                                stepSize: 5
                            }
                        }
                    }
                }
            });
        }

        // 2. Order Status Breakdown Donut
        @php
            $statusLabels = [];
            $statusData = [];
            $statusBg = [];
            $statusMap = [
                'new' => ['name' => 'جديد', 'color' => '#3B82F6'],
                'cutting' => ['name' => 'قص', 'color' => '#F97316'],
                'sewing' => ['name' => 'خياطة', 'color' => '#A855F7'],
                'finishing' => ['name' => 'تشطيب', 'color' => '#6366F1'],
                'ready-to-ship' => ['name' => 'جاهز للشحن', 'color' => '#14B8A6'],
                'shipped' => ['name' => 'تم الشحن', 'color' => '#10B981'],
                'delivered' => ['name' => 'تم التسليم', 'color' => '#059669'],
                'canceled' => ['name' => 'ملغي', 'color' => '#EF4444'],
            ];
            foreach ($statusCounts as $st) {
                $statusLabels[] = $statusMap[$st->status]['name'] ?? $st->status;
                $statusData[] = $st->count;
                $statusBg[] = $statusMap[$st->status]['color'] ?? '#9CA3AF';
            }
        @endphp

        const ctxStatus = document.getElementById('orderStatusChart')?.getContext('2d');
        if (ctxStatus) {
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($statusLabels) !!},
                    datasets: [{
                        data: {!! json_encode($statusData) !!},
                        backgroundColor: {!! json_encode($statusBg) !!},
                        borderWidth: 2,
                        borderColor: '#FFFFFF',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: true,
                            labels: {
                                font: { family: 'Cairo', size: 10, weight: 'bold' },
                                boxWidth: 10,
                                padding: 8
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        // 3. Top Styles Horizontal Bar Chart
        @php
            $topStylesFiltered = $topStyles->where('style_name', '!=', '-')->take(6);
            $styleLabels = $topStylesFiltered->pluck('style_name');
            $styleQuantities = $topStylesFiltered->pluck('total_quantity');
        @endphp

        const ctxStyles = document.getElementById('topStylesChart')?.getContext('2d');
        if (ctxStyles) {
            new Chart(ctxStyles, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($styleLabels) !!},
                    datasets: [{
                        label: 'القطع المطلوبة',
                        data: {!! json_encode($styleQuantities) !!},
                        backgroundColor: brandBrown,
                        borderRadius: 8,
                        barThickness: 18
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            rtl: true,
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' قطعة';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: { font: { family: 'Cairo', size: 10 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { family: 'Cairo', size: 11, weight: '600' } }
                        }
                    }
                }
            });
        }

        // 4. Top Governorates Bar Chart
        @php
            $topGovFiltered = $topGovernorates->take(7);
            $govLabels = $topGovFiltered->pluck('governorate');
            $govOrders = $topGovFiltered->pluck('orders_count');
        @endphp

        const ctxGov = document.getElementById('topGovChart')?.getContext('2d');
        if (ctxGov) {
            new Chart(ctxGov, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($govLabels) !!},
                    datasets: [{
                        label: 'عدد الطلبات',
                        data: {!! json_encode($govOrders) !!},
                        backgroundColor: '#C19077',
                        borderRadius: 8,
                        barThickness: 22
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            rtl: true,
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' طلب';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: { font: { family: 'Cairo', size: 10 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Cairo', size: 10, weight: '600' } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
