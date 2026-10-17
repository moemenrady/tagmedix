@extends('layouts.app')

@section('title', 'لوحة التحكم | Dashboard')
@section('page_title', 'لوحة التحكم الرئيسية')

@section('content')
<div class="space-y-6 page-transition pb-10">

    <!-- View Mode Switcher & Greeting Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primaryBrown/10 text-primaryBrown flex items-center justify-center font-bold text-base">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <div>
                <h2 class="text-base font-black text-darkText">مرحباً بك، {{ auth()->user()->name ?? 'مدير النظام' }}</h2>
                <p class="text-xs text-gray-500">متابعة فورية ومباشرة للطلبات، الذمم المالية، وتدفق عمليات الورشة</p>
            </div>
        </div>

        <!-- View Mode Selector -->
        <div class="flex items-center bg-gray-50 p-1 rounded-xl border border-gray-200 text-xs font-semibold self-start sm:self-auto">
            <button type="button" onclick="setDashboardView('both')" id="btnViewBoth"
                    class="dashboard-view-btn px-3 py-1.5 rounded-lg transition {{ ($filters['view'] ?? 'both') === 'both' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText' }}">
                <i class="fa-solid fa-table-columns ml-1"></i> العرض الشامل
            </button>
            <button type="button" onclick="setDashboardView('admin')" id="btnViewAdmin"
                    class="dashboard-view-btn px-3 py-1.5 rounded-lg transition {{ ($filters['view'] ?? '') === 'admin' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText' }}">
                <i class="fa-solid fa-table-list ml-1"></i> جدول الإدارة
            </button>
            <button type="button" onclick="setDashboardView('staff')" id="btnViewStaff"
                    class="dashboard-view-btn px-3 py-1.5 rounded-lg transition {{ ($filters['view'] ?? '') === 'staff' ? 'bg-primaryBrown text-white shadow-sm' : 'text-gray-600 hover:text-darkText' }}">
                <i class="fa-solid fa-kanban ml-1"></i> كانبان الورشة
            </button>
        </div>
    </div>

    <!-- Stat Cards (Real Live Database Metrics) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        <!-- Stat 1: Total Orders -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-start hover:shadow-md transition">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1" data-ar="إجمالي الطلبات" data-en="Total Orders">إجمالي الطلبات</p>
                <h3 class="text-2xl font-black text-gray-900">{{ number_format($stats['totalOrders']) }}</h3>
                <p class="text-[11px] text-emerald-600 font-bold mt-1">
                    <i class="fa-solid fa-arrow-trend-up"></i> {{ $stats['thisMonthOrders'] }} طلب هذا الشهر
                </p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
        </div>

        <!-- Stat: Urgent Orders -->
        <div class="bg-[#FFF5F5] p-5 rounded-2xl shadow-sm border border-red-200 flex justify-between items-start hover:shadow-md transition cursor-pointer" onclick="window.location='{{ route('orders.index', ['urgency' => 'urgent']) }}'" title="عرض الطلبات المستعجلة">
            <div>
                <p class="text-[11px] font-bold text-red-600 uppercase tracking-wider mb-1 flex items-center gap-1">
                    <i class="fa-solid fa-bolt text-red-500 animate-bounce"></i> طلبيات عاجلة
                </p>
                <h3 class="text-2xl font-black text-red-600">{{ number_format($stats['urgentOrdersCount'] ?? 0) }}</h3>
                <p class="text-[11px] text-red-500 font-bold mt-1">أولوية إنتاج وتسليم قصوى</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-fire-flame-curved"></i>
            </div>
        </div>

        <!-- Stat 2: Pending Payments -->
        <div class="bg-[#FFF5F5] p-5 rounded-2xl shadow-sm border border-red-100 flex justify-between items-start hover:shadow-md transition">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1" data-ar="المبالغ المعلقة (COD)" data-en="Pending Payments">المبالغ المعلقة (COD)</p>
                <h3 class="text-2xl font-black text-red-600">EGP {{ number_format($stats['pendingPayments'], 0) }}</h3>
                <p class="text-[11px] text-red-500 font-bold mt-1">{{ $stats['pendingOrdersCount'] }} طلب مطلوب تحصيله</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <!-- Stat 3: Orders in Production -->
        <div class="bg-[#FFFAF2] p-5 rounded-2xl shadow-sm border border-yellow-100 flex justify-between items-start hover:shadow-md transition">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1" data-ar="طلبات قيد الإنتاج" data-en="Orders in Production">طلبات قيد الإنتاج</p>
                <h3 class="text-2xl font-black text-amber-600">{{ number_format($stats['inProductionCount']) }}</h3>
                <p class="text-[11px] text-amber-600 font-bold mt-1">{{ $stats['inProductionPct'] }}% من إجمالي الطلبات</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-yellow-100 text-yellow-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-scissors"></i>
            </div>
        </div>

        <!-- Stat 4: Ready to Ship -->
        <div class="bg-[#F0FDF4] p-5 rounded-2xl shadow-sm border border-green-100 flex justify-between items-start hover:shadow-md transition">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1" data-ar="جاهز للشحن" data-en="Ready to Ship">جاهز للشحن</p>
                <h3 class="text-2xl font-black text-teal-700">{{ number_format($stats['readyToShipCount']) }}</h3>
                <p class="text-[11px] text-teal-600 font-bold mt-1">بانتظار تسليم المندوب</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
        </div>

        <!-- Stat 5: Delivered / Shipped -->
        <div class="bg-[#F8FAFC] p-5 rounded-2xl shadow-sm border border-blue-100 flex justify-between items-start hover:shadow-md transition">
            <div>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1" data-ar="شحنات تم تسليمها" data-en="Shipped / Delivered">شحنات تم تسليمها</p>
                <h3 class="text-2xl font-black text-blue-700">{{ number_format($stats['shippedCount']) }}</h3>
                <p class="text-[11px] text-blue-600 font-bold mt-1">عبر Turbo للشحن</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg shadow-inner">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
    </div>

    <!-- Toast Notification Banner -->
    <div id="quickToast" class="hidden fixed bottom-6 left-6 z-50 bg-gray-900 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-bold border border-gray-700 transition-all">
        <i class="fa-solid fa-circle-check text-emerald-400 text-base" id="toastIcon"></i>
        <span id="toastMsg">تم تحديث حالة الطلب بنجاح</span>
    </div>

    <!-- SECTION 1: ADMIN VIEW Table -->
    <div id="adminViewContainer" class="{{ ($filters['view'] ?? '') === 'staff' ? 'hidden' : '' }} bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col transition-all">
        <!-- Table Header & Controls -->
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gray-50/50">
            <div class="flex items-center gap-2 bg-[#111827] text-white px-4 py-2 rounded-xl font-bold text-xs w-max shadow-sm">
                <i class="fa-solid fa-shield-halved text-[10px]"></i> ADMIN VIEW (Full Details - {{ $adminOrders->total() }} Orders)
            </div>

            <!-- Search and Status Filter Form -->
            <form method="GET" action="{{ route('dashboard') }}" id="adminSearchForm" class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <input type="hidden" name="view" value="{{ $filters['view'] ?? 'both' }}">

                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="بحث برقم الطلب، العميل، الهاتف..."
                           class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primaryBrown/30 transition">
                </div>

                <select name="status" onchange="this.form.submit()"
                        class="w-full sm:w-auto px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-primaryBrown/30 transition">
                    <option value="all">جميع الحالات</option>
                    <option value="new" {{ ($filters['status'] ?? '') === 'new' ? 'selected' : '' }}>جديد (New)</option>
                    <option value="cutting" {{ ($filters['status'] ?? '') === 'cutting' ? 'selected' : '' }}>قص (Cutting)</option>
                    <option value="sewing" {{ ($filters['status'] ?? '') === 'sewing' ? 'selected' : '' }}>خياطة (Sewing)</option>
                    <option value="finishing" {{ ($filters['status'] ?? '') === 'finishing' ? 'selected' : '' }}>تشطيب (Finishing)</option>
                    <option value="ready-to-ship" {{ ($filters['status'] ?? '') === 'ready-to-ship' ? 'selected' : '' }}>جاهز للشحن (Ready)</option>
                    <option value="shipped" {{ ($filters['status'] ?? '') === 'shipped' ? 'selected' : '' }}>تم الشحن (Shipped)</option>
                </select>

                @if(!empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'all'))
                <a href="{{ route('dashboard', ['view' => $filters['view'] ?? 'both']) }}" class="text-xs font-bold text-gray-500 hover:text-red-600 flex items-center gap-1" title="إلغاء الفلتر">
                    <i class="fa-solid fa-rotate-left"></i> إلغاء
                </a>
                @endif
            </form>
        </div>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left whitespace-nowrap">
                <thead class="text-[11px] text-gray-500 font-bold bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-4">Order ID</th>
                        <th class="px-5 py-4">Customer Name</th>
                        <th class="px-5 py-4">Phone 1</th>
                        <th class="px-5 py-4">Phone 2</th>
                        <th class="px-5 py-4">Address / Governorate</th>
                        <th class="px-5 py-4">Product / Summary</th>
                        <th class="px-5 py-4">Total Price</th>
                        <th class="px-5 py-4">Paid</th>
                        <th class="px-5 py-4">Remaining</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Source</th>
                        <th class="px-5 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($adminOrders as $order)
                    @php
                        $paidAmount = $order->payments->sum('amount');
                        $remainingAmount = max(0, $order->total_price - $paidAmount);
                        $firstItem = $order->items->first();

                        // Robust fallback for phone and customer name
                        $customerName = $order->customer_name ?: ($order->customer?->name ?: 'عميل');
                        $phone1 = $order->customer_phone_1 ?: ($order->phone1 ?: ($order->customer?->phone_1 ?: '-'));
                        $phone2 = $order->customer_phone_2 ?: ($order->phone2 ?: ($order->customer?->phone_2 ?: '-'));
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <!-- Order ID -->
                        <td class="px-5 py-4 font-bold text-darkText">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-primaryBrown hover:underline font-mono">
                                    #{{ $order->order_number }}
                                </a>
                                @if ($order->is_urgent)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-red-100 text-red-600 border border-red-200 flex items-center gap-0.5">
                                        <i class="fa-solid fa-bolt text-[9px]"></i> عاجل
                                    </span>
                                @endif
                                @php $deliveryInfo = $order->delivery_status_info; @endphp
                                @if ($deliveryInfo['has_date'])
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $deliveryInfo['badge_class'] }}">
                                        {{ $deliveryInfo['label'] }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Customer Name -->
                        <td class="px-5 py-4 font-bold text-gray-900">
                            {{ $customerName }}
                        </td>

                        <!-- Phone 1 -->
                        <td class="px-5 py-4 text-gray-600 font-mono">
                            {{ $phone1 }}
                        </td>

                        <!-- Phone 2 -->
                        <td class="px-5 py-4 text-gray-400 font-mono">
                            {{ $phone2 }}
                        </td>

                        <!-- Address -->
                        <td class="px-5 py-4 text-xs text-gray-500 max-w-xs truncate" title="{{ $order->full_address }}">
                            <span class="font-bold text-darkText block">{{ $order->address?->governorate ?: ($order->governorate ?: 'غير محدد') }}</span>
                            <span class="text-[11px] text-gray-400">{{ Str::limit($order->full_address ?: $order->address?->city, 35) }}</span>
                        </td>

                        <!-- Product Summary -->
                        <td class="px-5 py-4 text-xs text-gray-600">
                            @if($firstItem)
                                <span class="font-semibold text-darkText block">{{ $firstItem->style?->name ?: 'موديل مخصص' }}</span>
                                <span class="text-[10px] text-gray-400">
                                    {{ $firstItem->color?->name ?: '-' }} • {{ $firstItem->size?->name ?: '-' }} ({{ $firstItem->quantity }} ق)
                                    @if($order->items->count() > 1)
                                        <span class="text-primaryBrown font-bold">+{{ $order->items->count() - 1 }}</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-400">لا توجد عناصر</span>
                            @endif
                        </td>

                        <!-- Total Price -->
                        <td class="px-5 py-4 font-bold text-gray-900">
                            EGP {{ number_format($order->total_price, 2) }}
                        </td>

                        <!-- Paid -->
                        <td class="px-5 py-4 font-semibold text-emerald-600">
                            EGP {{ number_format($paidAmount, 2) }}
                        </td>

                        <!-- Remaining -->
                        <td class="px-5 py-4 font-bold {{ $remainingAmount > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                            EGP {{ number_format($remainingAmount, 2) }}
                        </td>

                        <!-- Status Badge -->
                        <td class="px-5 py-4">
                            @switch($order->status)
                                @case('new')
                                    <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded-md">New</span>
                                    @break
                                @case('cutting')
                                    <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2.5 py-1 rounded-md">Cutting</span>
                                    @break
                                @case('sewing')
                                    <span class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2.5 py-1 rounded-md">Sewing</span>
                                    @break
                                @case('finishing')
                                    <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2.5 py-1 rounded-md">Finishing</span>
                                    @break
                                @case('ready-to-ship')
                                    <span class="bg-teal-100 text-teal-700 text-[10px] font-bold px-2.5 py-1 rounded-md">Ready to Ship</span>
                                    @break
                                @case('shipped')
                                    <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2.5 py-1 rounded-md">Shipped</span>
                                    @break
                                @case('delivered')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-md">Delivered</span>
                                    @break
                                @default
                                    <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2.5 py-1 rounded-md">{{ $order->status }}</span>
                            @endswitch
                        </td>

                        <!-- Source Badge -->
                        <td class="px-5 py-4">
                            @if($order->source === 'from_stock')
                                <span class="bg-green-50 text-green-600 text-[10px] font-bold px-2.5 py-1 rounded-md">From Stock</span>
                            @elseif($order->source === 'replacement')
                                <span class="bg-orange-50 text-orange-600 text-[10px] font-bold px-2.5 py-1 rounded-md">Replacement</span>
                            @else
                                <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2.5 py-1 rounded-md">New</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-gray-400 hover:text-primaryBrown p-1" title="عرض الطلب">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('orders.edit', $order->id) }}" class="text-gray-400 hover:text-blue-600 p-1" title="تعديل">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-12 text-gray-400 font-semibold">
                            لا توجد طلبات مطابقة للبحث أو الفلتر
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Pagination Footer -->
        <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <div>
                عرض {{ $adminOrders->firstItem() ?? 0 }} إلى {{ $adminOrders->lastItem() ?? 0 }} من أصل {{ $adminOrders->total() }} طلب
            </div>
            <div>
                {{ $adminOrders->links() }}
            </div>
        </div>
    </div>

    <!-- SECTION 2: STAFF VIEW Kanban Board -->
    <div id="staffViewContainer" class="{{ ($filters['view'] ?? '') === 'admin' ? 'hidden' : '' }} bg-white border border-gray-200 rounded-2xl shadow-sm flex flex-col transition-all overflow-hidden">
        <!-- Kanban Header -->
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex items-center gap-2 bg-[#111827] text-white px-4 py-2 rounded-xl font-bold text-xs w-max shadow-sm">
                    <i class="fa-solid fa-kanban text-[10px]"></i> STAFF VIEW (Live Production Kanban)
                </div>
                <span class="text-[11px] text-gray-400 font-medium hidden md:block">متابعة إسناد وتوزيع العمليات مع زر النقل السريع للحالة</span>
            </div>

            <!-- Search within Kanban -->
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="kanbanSearchInput" placeholder="بحث سريع في كروت الورشة..."
                       class="w-full pl-9 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primaryBrown/30 transition">
            </div>
        </div>

        <!-- Kanban Columns Horizontal Scroll -->
        <div class="p-4 overflow-x-auto">
            <div class="flex gap-4 min-w-max pb-2">

                @foreach($kanbanColumns as $colKey => $colData)
                @php
                    $colOrders = $kanbanOrdersByStatus[$colKey] ?? collect();
                    $colTotalCount = $kanbanTotalCounts[$colKey] ?? $colOrders->count();
                    $headerBg = match($colKey) {
                        'new' => 'bg-blue-50 text-blue-800 border-blue-100',
                        'cutting' => 'bg-yellow-50 text-yellow-800 border-yellow-100',
                        'sewing' => 'bg-purple-50 text-purple-800 border-purple-100',
                        'finishing' => 'bg-indigo-50 text-indigo-800 border-indigo-100',
                        'ready-to-ship' => 'bg-teal-50 text-teal-800 border-teal-100',
                        'shipped' => 'bg-green-50 text-green-800 border-green-100',
                        default => 'bg-gray-50 text-gray-700 border-gray-100',
                    };
                @endphp

                <!-- Column -->
                <div class="w-[285px] flex flex-col h-full bg-gray-50/60 border border-gray-200 rounded-2xl shadow-sm overflow-hidden kanban-col" data-col="{{ $colKey }}">
                    <!-- Column Header -->
                    <div class="{{ $headerBg }} py-2.5 px-4 font-bold text-xs rounded-t-2xl border-b flex items-center justify-between">
                        <span>{{ $colData['title'] }}</span>
                        <span class="bg-white/80 px-2 py-0.5 rounded-full text-[10px] font-black shadow-sm" title="العدد الإجمالي في هذه المرحلة">
                            {{ $colTotalCount }}
                        </span>
                    </div>

                    <!-- Cards Container -->
                    <div class="p-3 space-y-3 flex-1 min-h-[350px] max-h-[640px] overflow-y-auto">
                        @forelse($colOrders as $kOrder)
                        @php
                            $cutter = $kOrder->assignments->firstWhere('stage', 'cutting')?->employee?->name;
                            $tailor = $kOrder->assignments->firstWhere('stage', 'sewing')?->employee?->name;
                            $finisher = $kOrder->assignments->firstWhere('stage', 'ready-to-ship')?->employee?->name ?? $kOrder->assignments->firstWhere('stage', 'finishing')?->employee?->name;
                            $kItem = $kOrder->items->first();

                            // SLA Overdue Alert calculation (> 48 hours in current active stage)
                            $hoursInStage = $kOrder->updated_at ? \Carbon\Carbon::parse($kOrder->updated_at)->diffInHours(now()) : 0;
                            $isOverdue = in_array($kOrder->status, ['new', 'cutting', 'sewing', 'finishing']) && $hoursInStage >= 48;

                            $kCustomerName = $kOrder->customer_name ?: ($kOrder->customer?->name ?: 'عميل');
                            $kPhone = $kOrder->customer_phone_1 ?: ($kOrder->phone1 ?: ($kOrder->customer?->phone_1 ?: '-'));
                        @endphp
                        <!-- Kanban Card -->
                        <div class="bg-white border {{ $isOverdue ? 'border-rose-300 ring-1 ring-rose-200' : 'border-gray-200' }} p-3.5 rounded-xl shadow-sm hover:shadow-md transition kanban-card" id="card-order-{{ $kOrder->id }}">
                            <!-- Top Card Bar -->
                            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                                <div class="flex items-center gap-1">
                                    <span class="font-mono text-xs font-bold text-primaryBrown">#{{ $kOrder->order_number }}</span>
                                    @if ($kOrder->is_urgent)
                                        <span class="inline-flex items-center gap-0.5 bg-red-100 text-red-600 border border-red-200 text-[9px] font-black px-1.5 py-0.5 rounded-md">
                                            <i class="fa-solid fa-bolt text-[8px]"></i> عاجل
                                        </span>
                                    @endif
                                    @php $kDelivery = $kOrder->delivery_status_info; @endphp
                                    @if ($kDelivery['has_date'])
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $kDelivery['badge_class'] }}">
                                            {{ $kDelivery['label'] }}
                                        </span>
                                    @endif
                                </div>
                                @if($isOverdue)
                                <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-black px-1.5 py-0.5 rounded-md animate-pulse" title="الطلب متأخر في هذه المرحلة لأكثر من 48 ساعة">
                                    <i class="fa-solid fa-triangle-exclamation"></i> متأخر (+{{ floor($hoursInStage / 24) }}يوم)
                                </span>
                                @else
                                <span class="text-[10px] text-gray-400 font-semibold">{{ $kOrder->created_at ? $kOrder->created_at->format('m/d') : '' }}</span>
                                @endif
                            </div>

                            <!-- Customer & Phone -->
                            <div class="font-bold text-gray-900 text-sm truncate">
                                {{ $kCustomerName }}
                            </div>
                            <div class="text-xs text-gray-500 mb-2 font-mono flex items-center gap-1.5">
                                <i class="fa-solid fa-phone text-[10px] text-gray-400"></i>
                                {{ $kPhone }}
                            </div>

                            <!-- Product Preview -->
                            @if($kItem)
                            <div class="text-[11px] bg-gray-50 p-2 rounded-lg text-gray-600 mb-2.5 border border-gray-100">
                                <span class="font-semibold block truncate">{{ $kItem->style?->name ?: 'تفصيل' }}</span>
                                <span class="text-[10px] text-gray-400">{{ $kItem->color?->name ?: '-' }} / {{ $kItem->size?->name ?: '-' }}</span>
                            </div>
                            @endif

                            <!-- Assigned Staff Badges -->
                            <div class="space-y-1 text-[11px] pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="text-gray-400 flex items-center gap-1"><i class="fa-solid fa-scissors text-[10px] text-orange-500"></i> Cutter:</span>
                                    <span class="font-bold {{ $cutter ? 'text-darkText' : 'text-gray-300 italic' }}">{{ $cutter ?: 'غير معين' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="text-gray-400 flex items-center gap-1"><i class="fa-solid fa-needle text-[10px] text-purple-500"></i> Tailor:</span>
                                    <span class="font-bold {{ $tailor ? 'text-darkText' : 'text-gray-300 italic' }}">{{ $tailor ?: 'غير معين' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-gray-600">
                                    <span class="text-gray-400 flex items-center gap-1"><i class="fa-solid fa-box text-[10px] text-teal-500"></i> Finisher:</span>
                                    <span class="font-bold {{ $finisher ? 'text-darkText' : 'text-gray-300 italic' }}">{{ $finisher ?: 'غير معين' }}</span>
                                </div>
                            </div>

                            <!-- Quick Action Button to Advance Order -->
                            @if(!empty($colData['next_status']))
                            <button type="button"
                                    onclick="quickAdvanceOrderStatus({{ $kOrder->id }}, '{{ $colData['next_status'] }}', this)"
                                    class="w-full mt-2.5 bg-gray-50 hover:bg-primaryBrown hover:text-white text-gray-700 text-[11px] font-bold py-1.5 px-2 rounded-lg border border-gray-200 transition flex items-center justify-center gap-1.5 shadow-xs group">
                                <i class="fa-solid fa-arrow-left text-[10px] group-hover:-translate-x-0.5 transition"></i>
                                <span>{{ $colData['next_label'] }}</span>
                            </button>
                            @endif

                            <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                                <a href="{{ route('orders.show', $kOrder->id) }}" class="text-primaryBrown hover:underline font-bold">
                                    عرض التفاصيل &larr;
                                </a>
                                <span class="font-bold text-gray-800">EGP {{ number_format($kOrder->total_price, 0) }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-6 flex-1 min-h-[220px] flex flex-col items-center justify-center text-gray-300">
                            <div class="mb-2 w-10 h-10 bg-white rounded-full flex items-center justify-center border border-dashed border-gray-200">
                                <i class="fa-solid fa-inbox text-base"></i>
                            </div>
                            <span class="text-[11px] font-medium uppercase tracking-wide">لا توجد طلبات</span>
                        </div>
                        @endforelse
                    </div>

                    <!-- Column Footer -->
                    <div class="p-2.5 text-center text-gray-400 hover:text-darkText text-[11px] font-semibold border-t border-gray-200 bg-white transition">
                        <a href="{{ route('orders.index', ['status' => $colKey]) }}" class="block">
                            عرض الكل في صفحة الطلبات ({{ $colTotalCount }})
                        </a>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>

</div>

<!-- Interactive Scripts -->
<script>
    // 1. Synchronize Dashboard View mode, Form input, and Browser URL
    function setDashboardView(mode) {
        const adminContainer = document.getElementById('adminViewContainer');
        const staffContainer = document.getElementById('staffViewContainer');

        // Update hidden form input so any subsequent search/status submission keeps the active view mode
        const viewInput = document.querySelector('input[name="view"]');
        if (viewInput) {
            viewInput.value = mode;
        }

        // Update URL query parameter smoothly without refreshing
        const url = new URL(window.location);
        url.searchParams.set('view', mode);
        window.history.replaceState({}, '', url);

        // Update Button Styling
        document.querySelectorAll('.dashboard-view-btn').forEach(btn => {
            btn.classList.remove('bg-primaryBrown', 'text-white', 'shadow-sm');
            btn.classList.add('text-gray-600');
        });

        if (mode === 'admin') {
            adminContainer?.classList.remove('hidden');
            staffContainer?.classList.add('hidden');
            document.getElementById('btnViewAdmin')?.classList.add('bg-primaryBrown', 'text-white', 'shadow-sm');
            document.getElementById('btnViewAdmin')?.classList.remove('text-gray-600');
        } else if (mode === 'staff') {
            adminContainer?.classList.add('hidden');
            staffContainer?.classList.remove('hidden');
            document.getElementById('btnViewStaff')?.classList.add('bg-primaryBrown', 'text-white', 'shadow-sm');
            document.getElementById('btnViewStaff')?.classList.remove('text-gray-600');
        } else {
            adminContainer?.classList.remove('hidden');
            staffContainer?.classList.remove('hidden');
            document.getElementById('btnViewBoth')?.classList.add('bg-primaryBrown', 'text-white', 'shadow-sm');
            document.getElementById('btnViewBoth')?.classList.remove('text-gray-600');
        }
    }

    // 2. Quick Advance Order Status with AJAX & Live Feedback
    function quickAdvanceOrderStatus(orderId, nextStatus, buttonEl) {
        if (!confirm('هل تريد تأكيد نقل الطلب للمرحلة التالية؟')) {
            return;
        }

        const originalHtml = buttonEl.innerHTML;
        buttonEl.disabled = true;
        buttonEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري التحديث...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(`/orders/${orderId}/change-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: nextStatus })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('فشل التحديث');
            }
            return response.json();
        })
        .then(data => {
            showToast('تم نقل الطلب إلى المرحلة التالية بنجاح!', 'success');
            // Reload page smoothly to reflect new counts and columns
            setTimeout(() => {
                window.location.reload();
            }, 600);
        })
        .catch(err => {
            buttonEl.disabled = false;
            buttonEl.innerHTML = originalHtml;
            showToast('حدث خطأ أثناء تحديث حالة الطلب', 'error');
        });
    }

    // 3. Simple Toast Helper
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('quickToast');
        const toastMsg = document.getElementById('toastMsg');
        const toastIcon = document.getElementById('toastIcon');

        if (!toast) return;

        toastMsg.innerText = msg;
        if (type === 'success') {
            toastIcon.className = 'fa-solid fa-circle-check text-emerald-400 text-base';
        } else {
            toastIcon.className = 'fa-solid fa-circle-xmark text-rose-400 text-base';
        }

        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // 4. Client-side live search for Kanban cards
    document.getElementById('kanbanSearchInput')?.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.kanban-card');
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = (query === '' || text.includes(query)) ? '' : 'none';
        });
    });
</script>
@endsection