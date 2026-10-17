@extends('layouts.app')

@section('title', 'مركز إدارة الشحنات وعمليات Turbo')
@section('hide_header', 'true')

@section('content')
    <style>
        /* إخفاء شريط لوحة التحكم العلوي بالكامل لتوفير المساحة */
        #headerWrapper {
            display: none !important;
        }

        /* توحيد وإلزام ظهور كافة الأرقام بالإنجليزية (English Numerals 0-9) */
        .num-en, .font-mono, [dir="ltr"], .stat-number {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            font-variant-numeric: tabular-nums lining-nums !important;
            direction: ltr !important;
            unicode-bidi: isolate !important;
        }

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

        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CABCB0; border-radius: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8f6f2; }
    </style>

    <div class="space-y-6 animate-fade-in pb-20 md:pb-6">

        <!-- Banner & Action Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#31006f] via-[#430099] to-[#111827] text-white p-5 md:p-7 shadow-xl border border-purple-900/40">
            <div class="absolute -right-10 -top-10 w-60 h-60 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <!-- Mobile Sidebar Toggle -->
                        <button type="button" onclick="document.getElementById('openSidebarBtn')?.click()" 
                            class="md:hidden w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" title="فتح القائمة">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-purple-200 text-xs font-semibold backdrop-blur-md border border-white/10">
                            <i class="fa-solid fa-bolt text-xs text-yellow-300"></i>
                            <span>Turbo Logistics Command Center</span>
                        </div>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight flex items-center gap-3">
                        <span>إدارة ومتابعة الشحنات</span>
                    </h1>
                    <p class="text-purple-100/80 text-xs md:text-sm max-w-2xl">
                        لوحة موحدة لمتابعة مسار الشحنات، التواصل الفوري مع مناديب التوصيل، وتحديث الحالات والتحصيلات المالية مباشرة من خوادم Turbo.
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" onclick="openSyncModal()" 
                        class="px-5 py-2.5 bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-extrabold rounded-xl shadow-lg transition-all flex items-center gap-2 text-xs md:text-sm cursor-pointer hover:scale-105 active:scale-95">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>مزامنة الشحنات من Turbo</span>
                    </button>

                    <button type="button" onclick="openWebhookModal()" 
                        class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl border border-white/20 backdrop-blur-sm transition-all flex items-center gap-2 text-xs md:text-sm cursor-pointer">
                        <i class="fa-solid fa-satellite-dish text-purple-300"></i>
                        <span>إعدادات الـ Webhook</span>
                    </button>
                </div>
            </div>
        </div>

        @if (auth()->user() && auth()->user()->role == 'admin')
            <!-- 1. شريط الإحصائيات (Statistics KPI Bar) -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <!-- إجمالي الشحنات -->
                <div class="bg-white rounded-2xl p-4 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold">إجمالي الشحنات</p>
                            <h3 class="text-2xl font-bold text-darkText num-en">{{ number_format($statistics['total']) }}</h3>
                            <p class="text-[11px] text-mutedBrown mt-1 num-en">{{ number_format($statistics['total_amount'], 2) }} EGP</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-primaryBg flex items-center justify-center text-primaryBrown">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                    </div>
                </div>

                <!-- جاري التوصيل مع المناديب -->
                <div class="bg-white rounded-2xl p-4 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold">مع المناديب (جاري التوصيل)</p>
                            <h3 class="text-2xl font-bold text-blue-600 num-en">{{ number_format($statistics['in_transit_count']) }}</h3>
                            <p class="text-[11px] text-blue-500 mt-1">شحنة في الطريق</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                    </div>
                </div>

                <!-- تم التسليم والمحصل -->
                <div class="bg-white rounded-2xl p-4 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold">تم التسليم والتحصيل</p>
                            <h3 class="text-2xl font-bold text-green-700 num-en">{{ number_format($statistics['delivered_count']) }}</h3>
                            <p class="text-[11px] text-green-600 font-bold mt-1 num-en">حصل: {{ number_format($statistics['total_collected_amount'], 2) }} EGP</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                </div>

                <!-- تنبيهات وتأخيرات -->
                <div class="bg-white rounded-2xl p-4 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold">شحنات بها تأخير/ملاحظات</p>
                            <h3 class="text-2xl font-bold text-amber-600 num-en">{{ number_format($statistics['delayed_count']) }}</h3>
                            <p class="text-[11px] text-amber-500 mt-1">تحتاج متابعة</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                </div>

                <!-- المرتجعات -->
                <div class="bg-white rounded-2xl p-4 shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="flex justify-between items-start relative z-10">
                        <div>
                            <p class="text-xs text-mutedBrown mb-1 font-bold">مرتجعات وملغية</p>
                            <h3 class="text-2xl font-bold text-red-600 num-en">{{ number_format($statistics['returned_count']) }}</h3>
                            <p class="text-[11px] text-red-500 mt-1 num-en">{{ number_format($statistics['total_return_amount'], 2) }} EGP</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. الفلاتر والبحث (Filters & Search) -->
        <div class="bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream p-4 md:p-5">
            <form id="filterForm" method="GET" action="{{ route('shipments.index') }}" class="space-y-4">

                <!-- البحث السريع -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute top-1/2 rtl:right-4 ltr:left-4 -translate-y-1/2 text-mutedBrown"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                        class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl py-3.5 rtl:pr-11 rtl:pl-4 ltr:pl-11 ltr:pr-4 focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all text-sm font-bold"
                        placeholder="ابحث برقم البوليصة، رقم الأوردر، اسم العميل، الهاتف، أو اسم المندوب...">
                </div>

                <!-- أزرار الفترات السريعة (Quick Dates) -->
                <div class="flex flex-nowrap overflow-x-auto gap-2 pb-2 scrollbar-hide items-center">
                    <span class="text-xs font-bold text-mutedBrown whitespace-nowrap ml-2"><i class="fa-solid fa-filter mr-1"></i> الفترات:</span>

                    <button type="button" onclick="applyQuickDate('today')"
                        class="quick-date-btn {{ request('date_preset') == 'today' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-sun"></i> اليوم
                    </button>
                    <button type="button" onclick="applyQuickDate('yesterday')"
                        class="quick-date-btn {{ request('date_preset') == 'yesterday' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-moon"></i> أمس
                    </button>
                    <button type="button" onclick="applyQuickDate('last2days')"
                        class="quick-date-btn {{ request('date_preset') == 'last2days' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-calendar-day"></i> آخر يومين
                    </button>
                    <button type="button" onclick="applyQuickDate('last7days')"
                        class="quick-date-btn {{ request('date_preset') == 'last7days' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-calendar-week"></i> آخر 7 أيام
                    </button>
                    <button type="button" onclick="applyQuickDate('thisMonth')"
                        class="quick-date-btn {{ request('date_preset') == 'thisMonth' ? 'active-tab' : 'bg-primaryBg text-mutedBrown hover:bg-secondaryCream' }} rounded-lg px-4 py-2 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer">
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
                        class="bg-primaryBrown hover:bg-mutedBrown text-white rounded-xl px-4 py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2 shadow-md cursor-pointer">
                        <i class="fa-solid fa-filter"></i> تطبيق الفلاتر
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. عرض البيانات (Data View) -->
        @if ($shipments->count() > 0)
            <!-- Desktop View (Table) -->
            <div class="hidden md:block bg-white rounded-2xl shadow-[0_4px_15px_rgba(202,188,176,0.15)] border border-secondaryCream overflow-hidden">
                <table class="w-full text-right">
                    <thead class="bg-primaryBg border-b border-secondaryCream">
                        <tr>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">البوليصة والطلب</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">العميل والعنوان</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">مندوب التوصيل</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">المالية والتحصيل</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">الحالة والتأخير</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown">التوقيت والمزامنة</th>
                            <th class="py-4 px-5 text-xs font-bold text-mutedBrown text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondaryCream">
                        @foreach ($shipments as $shipment)
                            <tr id="shipment-row-{{ $shipment->id }}"
                                data-shipment-id="{{ $shipment->id }}"
                                data-shipment-json="{{ json_encode($shipment) }}"
                                class="hover:bg-primaryBg/40 cursor-pointer transition-colors group shipment-table-row"
                                onclick='openShipmentFromRow(this)'>
                                
                                <!-- البوليصة والطلب -->
                                <td class="py-3 px-5 shipment-tracking-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-primaryBrown font-mono num-en">{{ $shipment->tracking_number ?? '-' }}</span>
                                        @if($shipment->tracking_number)
                                            <button type="button" onclick="event.stopPropagation(); copyText('{{ $shipment->tracking_number }}')" 
                                                class="text-gray-400 hover:text-primaryBrown transition p-1" title="نسخ البوليصة">
                                                <i class="fa-regular fa-copy text-xs"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @if ($shipment->order)
                                        <p class="text-xs text-mutedBrown mt-0.5 font-mono num-en">طلب: #{{ $shipment->order->order_number }}</p>
                                    @endif
                                </td>

                                <!-- العميل والعنوان -->
                                <td class="py-3 px-5 shipment-customer-col">
                                    <p class="text-sm font-bold text-darkText">{{ $shipment->receiver }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-mutedBrown font-mono num-en" dir="ltr">{{ $shipment->phone }}</span>
                                        <p class="text-[11px] text-gray-500 truncate max-w-[120px]">{{ $shipment->government }} - {{ $shipment->area }}</p>
                                    </div>
                                </td>

                                <!-- مندوب التوصيل مع الاتصال والواتساب -->
                                <td class="py-3 px-5 shipment-captain-col">
                                    @if($shipment->captain_name)
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                                <i class="fa-solid fa-motorcycle text-[#31006f] text-xs"></i>
                                                {{ $shipment->captain_name }}
                                            </span>
                                            
                                            <div class="flex items-center gap-2 mt-0.5" onclick="event.stopPropagation()">
                                                @if($shipment->captain_phone)
                                                    <a href="tel:{{ $shipment->captain_phone }}" 
                                                        class="inline-flex items-center gap-1 text-[10px] bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded transition font-mono num-en"
                                                        title="اتصال هاتفي">
                                                        <i class="fa-solid fa-phone"></i>
                                                        <span dir="ltr">{{ $shipment->captain_phone }}</span>
                                                    </a>
                                                @endif
                                                @if($shipment->captain_whatsapp_url)
                                                    <a href="{{ $shipment->captain_whatsapp_url }}" target="_blank"
                                                        class="inline-flex items-center gap-1 text-[10px] bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded transition"
                                                        title="مراسلة واتساب">
                                                        <i class="fa-brands fa-whatsapp text-xs"></i>
                                                        <span>واتساب</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 font-medium">لم يُحدد كابتن بعد</span>
                                    @endif
                                </td>

                                <!-- المالية والتحصيل -->
                                <td class="py-3 px-5 shipment-fin-col">
                                    <p class="text-sm font-bold text-darkText font-mono num-en">{{ number_format($shipment->amount, 2) }} EGP</p>
                                    @if ($shipment->collected_amount > 0)
                                        <p class="text-xs text-green-600 font-bold mt-0.5 font-mono num-en">حصل: {{ number_format($shipment->collected_amount, 2) }}</p>
                                    @endif
                                    @if ($shipment->return_amount > 0)
                                        <p class="text-[10px] text-red-500 mt-0.5 font-mono num-en">مرتجع: {{ number_format($shipment->return_amount, 2) }}</p>
                                    @endif
                                </td>

                                <!-- الحالة وملاحظات التأخير -->
                                <td class="py-3 px-5 shipment-status-col">
                                    <span class="shipment-status-badge inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border 
                                        {{ $shipment->is_delivered
                                            ? 'bg-green-50 text-green-700 border-green-200'
                                            : ($shipment->is_returned
                                                ? 'bg-red-50 text-red-700 border-red-200'
                                                : ($shipment->is_delayed
                                                    ? 'bg-amber-50 text-amber-800 border-amber-300'
                                                    : 'bg-blue-50 text-blue-700 border-blue-200')) }}">
                                        {{ $shipment->status_text ?: ($shipment->status ?? 'جارِ الشحن') }}
                                    </span>

                                    @if($shipment->delay_reason)
                                        <div class="shipment-delay-tag mt-1 flex items-center gap-1 text-[10px] text-amber-700 font-semibold bg-amber-50/80 px-2 py-0.5 rounded border border-amber-200 max-w-[170px] truncate" title="{{ $shipment->delay_reason }}">
                                            <i class="fa-solid fa-clock-rotate-left shrink-0"></i>
                                            <span class="truncate">{{ $shipment->delay_reason }}</span>
                                        </div>
                                    @endif

                                    @if($shipment->return_reason)
                                        <div class="shipment-return-tag mt-1 flex items-center gap-1 text-[10px] text-red-700 font-semibold bg-red-50/80 px-2 py-0.5 rounded border border-red-200 max-w-[170px] truncate" title="{{ $shipment->return_reason }}">
                                            <i class="fa-solid fa-arrow-rotate-left shrink-0"></i>
                                            <span class="truncate">{{ $shipment->return_reason }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- التوقيت وآخر مزامنة -->
                                <td class="py-3 px-5 shipment-time-col">
                                    <p class="text-xs text-darkText font-mono num-en">{{ $shipment->created_at->format('Y-m-d') }}</p>
                                    <p class="text-[10px] text-mutedBrown mt-0.5 font-mono num-en">
                                        <i class="fa-solid fa-arrows-rotate mr-0.5 text-gray-400"></i>
                                        {{ $shipment->last_synced_at ? $shipment->last_synced_at->format('Y-m-d H:i') : 'لم يُزامن بعد' }}
                                    </p>
                                </td>

                                <!-- الإجراءات -->
                                <td class="py-3 px-5 text-center" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Single Sync Button -->
                                        <button type="button" onclick="syncSingleShipment({{ $shipment->id }}, this)" 
                                            class="w-8 h-8 rounded-xl bg-purple-50 text-[#31006f] hover:bg-[#31006f] hover:text-white transition flex items-center justify-center shadow-sm cursor-pointer"
                                            title="تحديث فوري من Turbo">
                                            <i class="fa-solid fa-rotate text-xs"></i>
                                        </button>

                                        @if ($shipment->order)
                                            <a href="{{ route('orders.show', $shipment->order->id) }}"
                                                class="w-8 h-8 rounded-xl bg-primaryBg text-primaryBrown hover:bg-primaryBrown hover:text-white border border-secondaryCream transition flex items-center justify-center shadow-sm"
                                                title="فتح تفاصيل الطلب">
                                                <i class="fa-solid fa-file-lines text-xs"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View (Cards) -->
            <div class="md:hidden space-y-3">
                @foreach ($shipments as $shipment)
                    <div id="shipment-card-{{ $shipment->id }}"
                        data-shipment-id="{{ $shipment->id }}"
                        data-shipment-json="{{ json_encode($shipment) }}"
                        class="bg-white rounded-2xl p-4 shadow-sm border border-secondaryCream cursor-pointer hover:border-primaryBrown transition-colors active:scale-98 shipment-mobile-card"
                        onclick='openShipmentFromRow(this)'>
                        
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-sm font-bold text-primaryBrown font-mono num-en">
                                    {{ $shipment->tracking_number ?? 'لا يوجد بوليصة' }}
                                </p>
                                <p class="text-xs text-mutedBrown mt-0.5">{{ $shipment->receiver }}</p>
                            </div>
                            <span class="shipment-card-status-badge inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold border 
                                {{ $shipment->is_delivered
                                    ? 'bg-green-50 text-green-700 border-green-200'
                                    : ($shipment->is_returned
                                        ? 'bg-red-50 text-red-700 border-red-200'
                                        : 'bg-orange-50 text-orange-700 border-orange-200') }}">
                                {{ $shipment->status_text ?: ($shipment->status ?? 'جارِ') }}
                            </span>
                        </div>

                        <!-- Captain Info on Mobile -->
                        <div class="shipment-card-captain-col">
                            @if($shipment->captain_name)
                                <div class="bg-purple-50/60 p-2.5 rounded-xl border border-purple-100 flex items-center justify-between mb-2 text-xs" onclick="event.stopPropagation()">
                                    <div class="flex items-center gap-1.5 font-bold text-purple-900">
                                        <i class="fa-solid fa-motorcycle text-[#31006f]"></i>
                                        <span>{{ $shipment->captain_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($shipment->captain_phone)
                                            <a href="tel:{{ $shipment->captain_phone }}" class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-mono num-en">
                                                <i class="fa-solid fa-phone text-xs"></i>
                                            </a>
                                        @endif
                                        @if($shipment->captain_whatsapp_url)
                                            <a href="{{ $shipment->captain_whatsapp_url }}" target="_blank" class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                                <i class="fa-brands fa-whatsapp text-xs"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="shipment-card-delay-tag">
                            @if($shipment->delay_reason)
                                <div class="bg-amber-50 text-amber-800 text-xs p-2 rounded-xl border border-amber-200 mb-2 flex items-center gap-1.5">
                                    <i class="fa-solid fa-clock text-amber-600"></i>
                                    <span>تأخير: {{ $shipment->delay_reason }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-between items-end mt-2 pt-2 border-t border-secondaryCream/50 text-xs">
                            <div>
                                <p class="text-mutedBrown"><i class="fa-solid fa-location-dot mr-1"></i> {{ $shipment->government }} - {{ $shipment->area }}</p>
                                <p class="text-mutedBrown mt-1 font-mono num-en" dir="ltr"><i class="fa-solid fa-phone mr-1"></i> {{ $shipment->phone }}</p>
                            </div>
                            <div class="text-left shipment-card-fin-col">
                                <p class="text-sm font-bold text-darkText font-mono num-en">{{ number_format($shipment->amount, 2) }} EGP</p>
                                @if($shipment->collected_amount > 0)
                                    <p class="text-[10px] text-green-600 font-bold font-mono num-en">حصل: {{ number_format($shipment->collected_amount, 2) }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 pt-2 border-t border-secondaryCream/50 flex items-center gap-2" onclick="event.stopPropagation()">
                            <button type="button" onclick="syncSingleShipment({{ $shipment->id }}, this)" 
                                class="flex-1 py-2 bg-purple-50 hover:bg-purple-100 text-[#31006f] font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                                <i class="fa-solid fa-rotate text-xs"></i>
                                <span>تحديث فوري من Turbo</span>
                            </button>
                            @if ($shipment->order)
                                <a href="{{ route('orders.show', $shipment->order->id) }}" 
                                    class="py-2 px-3 bg-primaryBg text-primaryBrown font-bold rounded-xl text-xs border border-secondaryCream flex items-center gap-1">
                                    <i class="fa-solid fa-file-lines"></i>
                                    <span>الطلب</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center w-full pagination-wrapper">
                {{ $shipments->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-secondaryCream p-10 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-primaryBg rounded-full flex items-center justify-center text-softBeige mb-4">
                    <i class="fa-solid fa-box-open text-3xl"></i>
                </div>
                <h4 class="text-lg font-bold text-darkText">لا توجد شحنات مطابقة</h4>
                <p class="text-sm text-mutedBrown mt-1">لم يتم العثور على أي شحنات وفقاً لمعايير البحث المحددة.</p>
            </div>
        @endif

    </div>

    <!-- 4. المودال المتطور الشامل لتفاصيل الشحنة (Spacious & Complete Logistics Modal) -->
    <div id="shipmentModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-3 md:p-6">
        <!-- خلفية شفافة للإغلاق -->
        <div class="absolute inset-0 bg-darkText/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <!-- محتوى المودال -->
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[92vh] overflow-hidden relative z-10 flex flex-col transform scale-95 opacity-0 transition-all duration-300 border border-gray-100"
            id="modalContent">

            <!-- Modal Header (Sticky) -->
            <div class="bg-primaryBg/60 border-b border-secondaryCream p-4 md:p-5 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-purple-100 text-[#31006f] flex items-center justify-center text-xl shadow-sm">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg md:text-xl font-black text-darkText">شحنة <span id="m_tracking" class="text-primaryBrown font-mono num-en"></span></h3>
                            <button type="button" onclick="copyModalTracking()" class="text-gray-400 hover:text-primaryBrown transition p-1 cursor-pointer" title="نسخ البوليصة">
                                <i class="fa-regular fa-copy text-sm"></i>
                            </button>
                        </div>
                        <p id="m_order_num" class="text-xs text-mutedBrown font-mono num-en mt-0.5"></p>
                    </div>
                </div>
                    {{-- only for test  --}}
                <div class="flex items-center gap-2">
                    @if(config('app.debug'))
                        <button type="button" onclick="toggleDebugPayload()" 
                            class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-black text-amber-300 border border-slate-700 text-xs font-mono font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                            title="عرض استجابة Turbo الخام (Dev Mode Only)">
                            <i class="fa-solid fa-bug text-xs"></i>
                            <span>Turbo JSON (Dev)</span>
                        </button>
                    @endif
                    {{-- only for test  --}}

                    <button type="button" id="m_sync_btn" onclick="syncCurrentModalShipment()"
                        class="px-4 py-2 rounded-xl bg-purple-50 hover:bg-[#31006f] text-[#31006f] hover:text-white border border-purple-200 text-xs font-bold transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>مزامنة الآن</span>
                    </button>
                    <button onclick="closeModal()"
                        class="w-9 h-9 rounded-full bg-white border border-secondaryCream flex items-center justify-center text-mutedBrown hover:bg-roseAccent hover:text-white transition cursor-pointer shadow-sm">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body (Scrollable & Fully Visible) -->
            <div class="p-5 md:p-6 overflow-y-auto space-y-5 custom-scrollbar flex-1 bg-gray-50/40">

                @if(config('app.debug'))
                    <!-- 🐞 كارت وضع المطور لفحص الـ JSON الخام مباشرة -->
                    <div id="m_debug_section" class="hidden bg-slate-950 border border-slate-800 rounded-2xl p-4 shadow-xl">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-800 text-xs">
                            <div class="flex items-center gap-2 text-amber-400 font-mono font-bold">
                                <i class="fa-solid fa-terminal"></i>
                                <span>Turbo API Raw Payload (وضع المطور - Debug Mode)</span>
                            </div>
                            <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                <a id="m_debug_link" href="#" target="_blank"
                                    class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-emerald-300 rounded-lg text-xs font-mono font-bold transition flex items-center gap-1.5 border border-slate-700">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    <span>فتح dd() كامل في نافذة مستقلة</span>
                                </a>
                                <button type="button" onclick="copyDebugJson()" 
                                    class="px-3 py-1.5 bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 rounded-lg text-xs font-mono font-bold transition border border-amber-500/30 flex items-center gap-1 cursor-pointer">
                                    <i class="fa-regular fa-copy"></i>
                                    <span>نسخ JSON</span>
                                </button>
                            </div>
                        </div>
                        <pre id="m_debug_json" class="text-emerald-400 font-mono text-[11px] overflow-x-auto max-h-64 p-3 rounded-xl bg-black/60 dir-ltr text-left select-all custom-scrollbar border border-slate-800/80 leading-relaxed"></pre>
                    </div>
                @endif

                <!-- 1. شريط الحالة ومسار التتبع (Status & Timeline Card) -->
                <div class="bg-white border border-secondaryCream rounded-2xl p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-gray-500">حالة الشحنة في Turbo:</span>
                            <span id="m_status_badge" class="px-3 py-1 rounded-full text-xs font-black shadow-sm"></span>
                        </div>
                        <div class="text-xs text-gray-500 flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate text-purple-600"></i>
                            <span>آخر تحديث:</span>
                            <span id="m_synced" class="font-bold text-gray-800 font-mono num-en"></span>
                        </div>
                    </div>

                    <!-- مسار الشحنة المرئي (Timeline Stepper) -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between relative px-4">
                            <!-- Background Line -->
                            <div class="absolute top-1/2 left-8 right-8 -translate-y-1/2 h-1.5 bg-gray-200 z-0 rounded-full"></div>
                            <!-- Active Line -->
                            <div id="m_step_line" class="absolute top-1/2 right-8 -translate-y-1/2 h-1.5 bg-[#31006f] transition-all duration-500 z-0 rounded-full" style="width: 0%;"></div>

                            <!-- Step 1: Created -->
                            <div class="flex flex-col items-center relative z-10">
                                <div id="m_step1_circle" class="w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md">
                                    <i class="fa-solid fa-file-circle-check"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-700 mt-1.5">تم الإنشاء</span>
                            </div>

                            <!-- Step 2: Hub / Processing -->
                            <div class="flex flex-col items-center relative z-10">
                                <div id="m_step2_circle" class="w-9 h-9 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold shadow-md transition-colors">
                                    <i class="fa-solid fa-warehouse"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-500 mt-1.5">استلام الفرع</span>
                            </div>

                            <!-- Step 3: Out for Delivery -->
                            <div class="flex flex-col items-center relative z-10">
                                <div id="m_step3_circle" class="w-9 h-9 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold shadow-md transition-colors">
                                    <i class="fa-solid fa-motorcycle"></i>
                                </div>
                                <span class="text-[11px] font-bold text-gray-500 mt-1.5">مع الكابتن</span>
                            </div>

                            <!-- Step 4: Delivered / Returned -->
                            <div class="flex flex-col items-center relative z-10">
                                <div id="m_step4_circle" class="w-9 h-9 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold shadow-md transition-colors">
                                    <i class="fa-solid fa-check-double"></i>
                                </div>
                                <span id="m_step4_label" class="text-[11px] font-bold text-gray-500 mt-1.5">التسليم</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. تنبيهات التأخير أو الارتجاع (Alerts) -->
                <div id="m_delay_box" class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs font-semibold flex items-start gap-3 shadow-sm hidden">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="font-black text-sm">تنبيه تأخير الشحنة لدى شركة الشحن:</strong>
                        <p id="m_delay_text" class="mt-1 text-amber-800 leading-relaxed"></p>
                    </div>
                </div>

                <div id="m_return_box" class="p-4 rounded-2xl bg-red-50 border border-red-300 text-red-900 text-xs font-semibold flex items-start gap-3 shadow-sm hidden">
                    <i class="fa-solid fa-arrow-rotate-left text-red-600 text-lg mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="font-black text-sm">سبب الارتجاع المسجل من شركة الشحن:</strong>
                        <p id="m_return_text" class="mt-1 text-red-800 leading-relaxed"></p>
                    </div>
                </div>

                <!-- 3. الشبكة الرئيسية (Two Column Grid) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- كرت العميل وموقع التسليم -->
                    <div class="bg-white border border-secondaryCream rounded-2xl p-4 md:p-5 shadow-sm space-y-3 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primaryBrown"></div>
                        <h4 class="text-xs font-bold text-mutedBrown flex items-center gap-2 border-b border-gray-100 pb-2">
                            <i class="fa-solid fa-user-tag text-primaryBrown"></i>
                            <span>بيانات المستلم وموقع التسليم</span>
                        </h4>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-500">اسم المستلم:</span>
                                <span id="m_receiver" class="font-bold text-gray-900 text-sm"></span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-500">رقم الهاتف:</span>
                                <div class="flex items-center gap-2">
                                    <span id="m_phone" class="font-bold text-gray-900 font-mono num-en" dir="ltr"></span>
                                    <a id="m_phone_call" href="#" class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition" title="اتصال">
                                        <i class="fa-solid fa-phone text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="flex justify-between items-start py-1">
                                <span class="text-gray-500 shrink-0">المحافظة والمنطقة:</span>
                                <span id="m_gov_area" class="font-bold text-gray-800 text-left"></span>
                            </div>
                            <div class="flex justify-between items-start py-1">
                                <span class="text-gray-500 shrink-0">العنوان التفصيلي:</span>
                                <span id="m_address" class="font-medium text-gray-700 text-left leading-relaxed"></span>
                            </div>
                        </div>
                    </div>

                    <!-- كرت المندوب وفروع الشحن -->
                    <div class="bg-white border border-secondaryCream rounded-2xl p-4 md:p-5 shadow-sm space-y-3 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#31006f]"></div>
                        <h4 class="text-xs font-bold text-mutedBrown flex items-center gap-2 border-b border-gray-100 pb-2">
                            <i class="fa-solid fa-motorcycle text-[#31006f]"></i>
                            <span>كابتن التوصيل والفرع</span>
                        </h4>

                        <!-- حالة تعيين المندوب -->
                        <div id="m_captain_assigned" class="space-y-3 hidden">
                            <div class="flex items-center justify-between bg-purple-50 p-3 rounded-xl border border-purple-100">
                                <div>
                                    <p class="text-[10px] text-purple-600 font-bold">اسم الكابتن:</p>
                                    <h5 id="m_captain_name" class="font-extrabold text-gray-900 text-sm mt-0.5"></h5>
                                    <p id="m_captain_phone" class="text-xs text-gray-600 font-mono num-en mt-0.5" dir="ltr"></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a id="m_captain_call_btn" href="#" 
                                        class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-1 shadow transition">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                        <span>اتصال</span>
                                    </a>
                                    <a id="m_captain_wa_btn" href="#" target="_blank"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-1 shadow transition">
                                        <i class="fa-brands fa-whatsapp text-xs"></i>
                                        <span>واتساب</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div id="m_captain_unassigned" class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-gray-500 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-truck-ramp-box text-gray-400 text-base"></i>
                            <span>لم يُحدد كابتن توصيل حتى الآن (الشحنة في مرحلة التجهيز والاستلام).</span>
                        </div>

                        <!-- فروع الشحن -->
                        <div class="space-y-2 text-xs pt-1">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">الفرع الحالي المستلم:</span>
                                <span id="m_branch" class="font-bold text-gray-800"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">الفرع المتوقع للتسليم:</span>
                                <span id="m_expected_branch" class="font-bold text-primaryBrown"></span>
                            </div>
                        </div>
                    </div>

                    <!-- كرت محتويات الطرد والمواصفات -->
                    <div class="bg-white border border-secondaryCream rounded-2xl p-4 md:p-5 shadow-sm space-y-3 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-roseAccent"></div>
                        <h4 class="text-xs font-bold text-mutedBrown flex items-center gap-2 border-b border-gray-100 pb-2">
                            <i class="fa-solid fa-box-open text-roseAccent"></i>
                            <span>محتويات ومواصفات الطرد</span>
                        </h4>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-start py-1">
                                <span class="text-gray-500 shrink-0">وصف المنتجات:</span>
                                <span id="m_order_summary" class="font-bold text-gray-900 text-left"></span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-500">عدد القطع:</span>
                                <span id="m_items_count" class="font-bold text-gray-900 font-mono num-en"></span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-500">وزن الشحنة:</span>
                                <span id="m_weight" class="font-bold text-gray-900 font-mono num-en"></span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-500">فحص ومعاينة الطرد:</span>
                                <span id="m_can_open" class="font-black"></span>
                            </div>
                        </div>
                    </div>

                    <!-- كرت الحسابات والمالية -->
                    <div class="bg-white border border-secondaryCream rounded-2xl p-4 md:p-5 shadow-sm space-y-3 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-green-500"></div>
                        <h4 class="text-xs font-bold text-mutedBrown flex items-center gap-2 border-b border-gray-100 pb-2">
                            <i class="fa-solid fa-money-bill-wave text-green-600"></i>
                            <span>الحسابات والتحصيلات المالية</span>
                        </h4>

                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-gray-50">
                                <span class="text-gray-500">المبلغ المطلوب تحصيله:</span>
                                <span id="m_amount" class="text-sm font-black text-gray-900 font-mono num-en"></span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-gray-50">
                                <span class="text-gray-500">المبلغ المحصل فعلياً:</span>
                                <span id="m_collected" class="text-sm font-black text-green-600 font-mono num-en"></span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-500">رسوم الشحن / الارتجاع:</span>
                                <span id="m_return" class="font-bold text-red-500 font-mono num-en"></span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 4. سجل الحركات والتحديثات الزمنية (Audit Trail) -->
                <div id="m_history_container" class="bg-white border border-secondaryCream rounded-2xl p-4 md:p-5 shadow-sm space-y-3">
                    <h4 class="text-xs font-bold text-mutedBrown flex items-center gap-2 border-b border-gray-100 pb-2">
                        <i class="fa-solid fa-clock-rotate-left text-primaryBrown"></i>
                        <span>سجل حركات وتحديثات الشحنة (Status History)</span>
                    </h4>
                    <div id="m_history_list" class="space-y-2 text-xs"></div>
                </div>

                <!-- 5. التواريخ والملاحظات الإضافية -->
                <div class="bg-white border border-secondaryCream rounded-2xl p-4 shadow-sm space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-500 block mb-1">تاريخ الإنشاء الأولي:</span>
                            <span id="m_created" class="font-bold text-gray-800 font-mono num-en"></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block mb-1">تاريخ ووقت آخر مزامنة:</span>
                            <span id="m_synced_detail" class="font-bold text-gray-800 font-mono num-en"></span>
                        </div>
                    </div>

                    <div id="m_notes_container" class="pt-3 border-t border-gray-100 hidden">
                        <span class="text-gray-500 block mb-1 font-bold">ملاحظات الشحنة:</span>
                        <p id="m_notes" class="p-3 bg-gray-50 rounded-xl text-gray-800 border border-gray-100 leading-relaxed"></p>
                    </div>
                </div>

            </div>

            <!-- Modal Footer (Sticky) -->
            <div class="bg-gray-50 border-t border-secondaryCream p-4 flex flex-col sm:flex-row justify-between items-center gap-3 shrink-0">
                <button type="button" onclick="closeModal()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-white border border-secondaryCream text-gray-700 hover:bg-gray-100 font-bold text-xs transition cursor-pointer">
                    إغلاق النافذة
                </button>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" id="m_cancel_btn" onclick="cancelCurrentModalShipment()"
                        class="px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 font-bold text-xs transition flex items-center gap-1.5 shadow-sm cursor-pointer"
                        title="إلغاء / حذف الشحنة من النظام ومطابقتها مع Turbo">
                        <i class="fa-solid fa-ban"></i>
                        <span>إلغاء الشحنة</span>
                    </button>

                    <button type="button" onclick="syncCurrentModalShipment()"
                        class="px-5 py-2.5 rounded-xl bg-[#31006f] hover:bg-[#430099] text-white font-bold text-xs transition flex items-center gap-2 shadow-md cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>تحديث البيانات من Turbo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. مودال مزامنة Turbo الجماعية (Batch Sync Modal) -->
    <div id="syncModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-darkText/60 backdrop-blur-sm transition-opacity" onclick="closeSyncModal()"></div>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden relative z-10 p-6 space-y-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2.5 text-[#31006f]">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base">مزامنة الشحنات من Turbo</h3>
                        <p class="text-xs text-gray-500">جلب أحدث التحديثات وتحديث قاعدة البيانات</p>
                    </div>
                </div>
                <button onclick="closeSyncModal()" class="text-gray-400 hover:text-gray-700 text-lg cursor-pointer">&times;</button>
            </div>

            <form id="bulkSyncForm" onsubmit="executeBulkSync(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">من تاريخ</label>
                    <input type="date" id="syncFromDate" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#31006f] font-mono num-en">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">إلى تاريخ</label>
                    <input type="date" id="syncToDate" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#31006f] font-mono num-en">
                </div>

                <div class="bg-purple-50 p-3 rounded-xl border border-purple-100 text-xs text-purple-900 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-[#31006f] ml-1"></i>
                    سيتم الاستعلام عن كافة الشحنات في هذا النطاق الزمني وتحديث حالات التسليم وأسماء المناديب والتحصيلات في قاعدة بياناتك تلقائياً.
                </div>

                <button type="submit" id="bulkSyncSubmitBtn"
                    class="w-full py-3 bg-[#31006f] hover:bg-[#430099] text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>بدء المزامنة الآن</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 6. مودال إعدادات الـ Webhook -->
    <div id="webhookModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-darkText/60 backdrop-blur-sm transition-opacity" onclick="closeWebhookModal()"></div>
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden relative z-10 p-6 space-y-5">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2.5 text-[#31006f]">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-satellite-dish"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base">رابط الـ Webhook الخاص بنظامك</h3>
                        <p class="text-xs text-gray-500">لتلقي التحديثات التلقائية فورياً من Turbo</p>
                    </div>
                </div>
                <button onclick="closeWebhookModal()" class="text-gray-400 hover:text-gray-700 text-lg cursor-pointer">&times;</button>
            </div>

            <div class="space-y-3">
                <p class="text-xs text-gray-600 leading-relaxed">
                    قم بنسخ الرابط التالي وإضافته في إعدادات الـ Webhook داخل حسابك في لوحة تحكم شركة <strong>Turbo</strong>:
                </p>

                <div class="relative">
                    <input type="text" readonly value="{{ url('/api/turbo/webhook') }}" id="webhookCopyInput"
                        class="w-full ltr text-left pl-3 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-xs font-mono text-gray-700 num-en">
                    <button type="button" onclick="copyWebhookUrl()" 
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-[#31006f] hover:bg-purple-50 rounded-lg transition cursor-pointer" title="نسخ الرابط">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>

                <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100 flex items-center gap-2 text-xs text-emerald-800 font-semibold">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>الرابط نشط ومبرمج لاستقبال ومعالجة إشعارات Turbo وتحديث الطلبات فورياً.</span>
                </div>
            </div>

            <button type="button" onclick="closeWebhookModal()"
                class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition text-xs cursor-pointer">
                إغلاق
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastNotification" class="fixed bottom-6 left-6 z-[120] hidden transform transition-all duration-300">
        <div class="px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-bold border" id="toastCard">
            <i id="toastIcon" class="fa-solid fa-circle-check text-base"></i>
            <span id="toastMessage"></span>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let currentModalShipment = null;

        // دالة موحدة لتنسيق التاريخ بالأرقام الإنجليزية الصريحة (YYYY-MM-DD hh:mm AM/PM)
        function formatEnDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            let hours = d.getHours();
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            return `${yyyy}-${mm}-${dd} ${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
        }

        // دالة موحدة لتنسيق الأرقام والأسعار بالأرقام الإنجليزية
        function formatEnNumber(num, decimals = 2) {
            const val = parseFloat(num || 0);
            return val.toLocaleString('en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        // 1. نظام الفترات السريعة (Quick Dates)
        function applyQuickDate(type) {
            const fromInput = document.getElementById('dateFrom');
            const toInput = document.getElementById('dateTo');
            const presetInput = document.getElementById('datePreset');

            let fromDate = new Date();
            let toDate = new Date();

            fromDate.setHours(0, 0, 0, 0);
            toDate.setHours(23, 59, 59, 999);

            if (type === 'today') {
                // today
            } else if (type === 'yesterday') {
                fromDate.setDate(fromDate.getDate() - 1);
                toDate.setDate(toDate.getDate() - 1);
            } else if (type === 'last2days') {
                fromDate.setDate(fromDate.getDate() - 2);
            } else if (type === 'last7days') {
                fromDate.setDate(fromDate.getDate() - 7);
            } else if (type === 'thisMonth') {
                fromDate.setDate(1);
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

            document.getElementById('filterForm').submit();
        }

        // 2. إدارة مودال تفاصيل الشحنة
        const modal = document.getElementById('shipmentModal');
        const modalContent = document.getElementById('modalContent');

        function openShipmentFromRow(element) {
            const raw = element.getAttribute('data-shipment-json');
            if (raw) {
                try {
                    const data = JSON.parse(raw);
                    openModal(data);
                } catch (e) {
                    console.error('Failed to parse shipment data from row:', e);
                }
            }
        }

        function openModal(shipment) {
            currentModalShipment = shipment;

            // Header
            document.getElementById('m_tracking').innerText = shipment.tracking_number || '-';
            document.getElementById('m_order_num').innerText = shipment.order 
                ? 'رقم الطلب المرجعي: #' + shipment.order.order_number 
                : (shipment.remote_order_id ? 'الطلب المرجعي: #' + shipment.remote_order_id : '');

            // Status Badge
            const statusBadge = document.getElementById('m_status_badge');
            const statusText = shipment.status_text || shipment.status || 'مسجل';
            statusBadge.innerText = statusText;

            if (shipment.is_delivered) {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black shadow-sm bg-green-100 text-green-800 border border-green-300';
            } else if (shipment.is_returned) {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black shadow-sm bg-red-100 text-red-800 border border-red-300';
            } else if (shipment.is_delayed) {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black shadow-sm bg-amber-100 text-amber-900 border border-amber-300';
            } else {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black shadow-sm bg-purple-100 text-[#31006f] border border-purple-200';
            }

            // Client Info
            document.getElementById('m_receiver').innerText = shipment.receiver || '-';
            const phoneStr = shipment.phone + (shipment.phone2 ? ' / ' + shipment.phone2 : '');
            document.getElementById('m_phone').innerText = phoneStr || '-';
            const phoneCall = document.getElementById('m_phone_call');
            if (shipment.phone) {
                phoneCall.href = 'tel:' + shipment.phone;
                phoneCall.classList.remove('hidden');
            } else {
                phoneCall.classList.add('hidden');
            }

            const govArea = [shipment.government, shipment.area].filter(Boolean).join(' - ');
            document.getElementById('m_gov_area').innerText = govArea || '-';
            document.getElementById('m_address').innerText = shipment.address || '-';

            // Package & Items
            document.getElementById('m_order_summary').innerText = shipment.order_summary || 'منتجات ملابس طبية';
            document.getElementById('m_items_count').innerText = (shipment.number_of_items || 1) + ' قطعة';
            document.getElementById('m_weight').innerText = formatEnNumber(shipment.weight || 1, 2) + ' كجم';
            
            const canOpenEl = document.getElementById('m_can_open');
            if (shipment.can_open === false || shipment.can_open === 0 || shipment.can_open === '0') {
                canOpenEl.innerText = 'غير مسموح بالمعاينة ❌';
                canOpenEl.className = 'font-black text-red-600';
            } else {
                canOpenEl.innerText = 'مسموح بالفتح والمعاينة قبل الدفع ✅';
                canOpenEl.className = 'font-black text-emerald-600';
            }

            // Branches
            document.getElementById('m_branch').innerText = shipment.current_branch || (shipment.response && shipment.response.branch) || 'المركز اللوجستي الرئيسي';
            document.getElementById('m_expected_branch').innerText = shipment.expected_branch || (shipment.response && shipment.response.expected_branch) || 'وسط البلد';

            // Captain Info
            const captainAssigned = document.getElementById('m_captain_assigned');
            const captainUnassigned = document.getElementById('m_captain_unassigned');
            const captainName = shipment.captain_name || (shipment.response && (shipment.response.captain_name || shipment.response.captain));
            const captainPhone = shipment.captain_phone || (shipment.response && (shipment.response.captain_number1 || shipment.response.captain_phone));
            const captainWa = shipment.captain_whatsapp_url;

            if (captainName) {
                document.getElementById('m_captain_name').innerText = captainName;
                document.getElementById('m_captain_phone').innerText = captainPhone || '';
                
                const callBtn = document.getElementById('m_captain_call_btn');
                const waBtn = document.getElementById('m_captain_wa_btn');

                if (captainPhone) {
                    callBtn.href = 'tel:' + captainPhone;
                    callBtn.classList.remove('hidden');
                } else {
                    callBtn.classList.add('hidden');
                }

                if (captainWa) {
                    waBtn.href = captainWa;
                    waBtn.classList.remove('hidden');
                } else {
                    waBtn.classList.add('hidden');
                }

                captainAssigned.classList.remove('hidden');
                captainUnassigned.classList.add('hidden');
            } else {
                captainAssigned.classList.add('hidden');
                captainUnassigned.classList.remove('hidden');
            }

            // Financials (English Numbers)
            document.getElementById('m_amount').innerText = formatEnNumber(shipment.amount, 2) + ' EGP';
            document.getElementById('m_collected').innerText = formatEnNumber(shipment.collected_amount, 2) + ' EGP';
            document.getElementById('m_return').innerText = formatEnNumber(shipment.return_amount, 2) + ' EGP';

            // Timestamps (English Digits)
            const createdFormatted = formatEnDate(shipment.created_at);
            const syncedFormatted = shipment.last_synced_at ? formatEnDate(shipment.last_synced_at) : createdFormatted;

            document.getElementById('m_created').innerText = createdFormatted;
            document.getElementById('m_synced').innerText = syncedFormatted;
            document.getElementById('m_synced_detail').innerText = syncedFormatted;

            // Delay & Return Alerts
            const delayBox = document.getElementById('m_delay_box');
            const delayReason = shipment.delay_reason || (shipment.response && shipment.response.delay_reason);
            if (delayReason) {
                document.getElementById('m_delay_text').innerText = delayReason;
                delayBox.classList.remove('hidden');
            } else {
                delayBox.classList.add('hidden');
            }

            const returnBox = document.getElementById('m_return_box');
            const returnReason = shipment.return_reason || (shipment.response && shipment.response.return_reason);
            if (returnReason) {
                document.getElementById('m_return_text').innerText = returnReason;
                returnBox.classList.remove('hidden');
            } else {
                returnBox.classList.add('hidden');
            }

            // Notes
            const notesContainer = document.getElementById('m_notes_container');
            if (shipment.notes) {
                document.getElementById('m_notes').innerText = shipment.notes;
                notesContainer.classList.remove('hidden');
            } else {
                notesContainer.classList.add('hidden');
            }

            // Stepper & History
            renderStepper(shipment);
            renderStatusHistories(shipment);

            // Cancel button state
            const cancelBtn = document.getElementById('m_cancel_btn');
            if (cancelBtn) {
                const isCanceled = shipment.is_canceled || ['canceled', 'cancelled', '4'].includes(String(shipment.status || '').toLowerCase()) || String(shipment.status || '').includes('محذوف') || String(shipment.status || '').includes('ملغي');
                if (isCanceled) {
                    cancelBtn.innerHTML = '<i class="fa-solid fa-ban"></i> <span>الشحنة ملغية / محذوفة</span>';
                    cancelBtn.classList.add('opacity-60', 'cursor-not-allowed');
                    cancelBtn.disabled = true;
                } else {
                    cancelBtn.innerHTML = '<i class="fa-solid fa-ban"></i> <span>إلغاء الشحنة</span>';
                    cancelBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                    cancelBtn.disabled = false;
                }
            }

            // Dev Mode Raw Response View
            @if(config('app.debug'))
                const debugJsonEl = document.getElementById('m_debug_json');
                const debugLinkEl = document.getElementById('m_debug_link');
                if (debugJsonEl) {
                    const rawResponse = shipment.response || {};
                    debugJsonEl.textContent = JSON.stringify(rawResponse, null, 2);
                }
                if (debugLinkEl) {
                    debugLinkEl.href = `/shipments/${shipment.id}/debug-turbo`;
                }
                console.log('%c🐞 [Turbo Shipment Raw Response]:', 'color: #f59e0b; font-weight: bold;', shipment.response);
            @endif

            // Open Modal Animation
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function toggleDebugPayload() {
            const sec = document.getElementById('m_debug_section');
            if (sec) {
                sec.classList.toggle('hidden');
            }
        }

        function copyDebugJson() {
            const el = document.getElementById('m_debug_json');
            if (el) {
                navigator.clipboard.writeText(el.textContent);
                showToast('تم نسخ JSON الخاص بـ Turbo بنجاح!', true);
            }
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                currentModalShipment = null;
            }, 250);
        }

        function renderStepper(shipment) {
            const status = String(shipment.status || '').toLowerCase();
            const stepLine = document.getElementById('m_step_line');
            const step2 = document.getElementById('m_step2_circle');
            const step3 = document.getElementById('m_step3_circle');
            const step4 = document.getElementById('m_step4_circle');
            const step4Label = document.getElementById('m_step4_label');

            stepLine.style.width = '0%';
            step2.className = 'w-9 h-9 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold shadow-md transition-colors';
            step3.className = 'w-9 h-9 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold shadow-md transition-colors';
            step4.className = 'w-9 h-9 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold shadow-md transition-colors';
            step4Label.innerText = 'التسليم';

            if (shipment.is_delivered || ['delivered', 'completed', '3'].includes(status) || status.includes('تسليم')) {
                stepLine.style.width = '100%';
                step2.className = 'w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md';
                step3.className = 'w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md';
                step4.className = 'w-9 h-9 rounded-full bg-green-600 text-white flex items-center justify-center text-xs font-bold shadow-md';
                step4Label.innerText = 'تم التسليم';
            } else if (shipment.is_returned || shipment.is_canceled || ['returned', 'canceled', 'cancelled', '4'].includes(status) || status.includes('مرتجع') || status.includes('ملغي') || status.includes('محذوف')) {
                stepLine.style.width = '100%';
                step2.className = 'w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md';
                step3.className = 'w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md';
                step4.className = 'w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center text-xs font-bold shadow-md';
                step4Label.innerText = status.includes('محذوف') ? 'محذوفة' : 'مرتجع / ملغي';
            } else if (shipment.is_in_transit || shipment.captain_name || ['in_transit', '2'].includes(status) || status.includes('جاري') || status.includes('تنفيذ')) {
                stepLine.style.width = '66%';
                step2.className = 'w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md';
                step3.className = 'w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md';
            } else {
                stepLine.style.width = '33%';
                step2.className = 'w-9 h-9 rounded-full bg-[#31006f] text-white flex items-center justify-center text-xs font-bold shadow-md';
            }
        }

        function renderStatusHistories(shipment) {
            const container = document.getElementById('m_history_container');
            const list = document.getElementById('m_history_list');
            list.innerHTML = '';

            const histories = shipment.status_histories || shipment.statusHistories || [];
            if (histories.length > 0) {
                histories.forEach(h => {
                    const item = document.createElement('div');
                    item.className = 'flex justify-between items-center p-2.5 rounded-xl bg-gray-50 border border-gray-100';
                    const dateFormatted = formatEnDate(h.created_at);
                    item.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#31006f]"></span>
                            <span class="font-bold text-gray-800 text-xs">${h.new_status || 'تحديث الحالة'}</span>
                            ${h.old_status ? `<span class="text-[11px] text-gray-400 font-mono num-en">(من ${h.old_status})</span>` : ''}
                        </div>
                        <span class="text-[11px] text-gray-500 font-mono num-en">${dateFormatted}</span>
                    `;
                    list.appendChild(item);
                });
                container.classList.remove('hidden');
            } else {
                const item = document.createElement('div');
                item.className = 'p-3 rounded-xl bg-gray-50 border border-gray-100 text-gray-500 text-xs flex items-center gap-2';
                item.innerHTML = `
                    <i class="fa-solid fa-circle-info text-[#31006f]"></i>
                    <span>الشحنة مسجلة حديثاً في مرحلة <strong>${shipment.status_text || shipment.status || 'قيد الإنتظار'}</strong> ولم تسجل انتقالات إضافية حتى الآن.</span>
                `;
                list.appendChild(item);
                container.classList.remove('hidden');
            }
        }

        // 3. تحديث بيانات الشحنة في الصفحة لحظياً بدون إعادة تحميل (In-Place DOM Update)
        function updateShipmentInDom(shipment) {
            if (!shipment || !shipment.id) return;

            // 1. تحديث صف جدول الشاشات الكبيرة (Desktop Table Row)
            const row = document.getElementById('shipment-row-' + shipment.id);
            if (row) {
                row.setAttribute('data-shipment-json', JSON.stringify(shipment));

                // تحديث عمود الحالة والشارات
                const statusCol = row.querySelector('.shipment-status-col');
                const statusBadge = row.querySelector('.shipment-status-badge');
                if (statusBadge) {
                    statusBadge.textContent = shipment.status_text || shipment.status || 'جارِ الشحن';
                    statusBadge.className = 'shipment-status-badge inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border ' + (
                        shipment.is_delivered ? 'bg-green-50 text-green-700 border-green-200' :
                        (shipment.is_returned || shipment.is_canceled || String(shipment.status || '').includes('محذوف') || String(shipment.status || '').includes('ملغي')) ? 'bg-red-50 text-red-700 border-red-200' :
                        shipment.is_delayed ? 'bg-amber-50 text-amber-800 border-amber-300' :
                        'bg-blue-50 text-blue-700 border-blue-200'
                    );
                }

                if (statusCol) {
                    let delayTag = statusCol.querySelector('.shipment-delay-tag');
                    if (shipment.delay_reason) {
                        if (!delayTag) {
                            delayTag = document.createElement('div');
                            delayTag.className = 'shipment-delay-tag mt-1 flex items-center gap-1 text-[10px] text-amber-700 font-semibold bg-amber-50/80 px-2 py-0.5 rounded border border-amber-200 max-w-[170px] truncate';
                            delayTag.innerHTML = '<i class="fa-solid fa-clock-rotate-left shrink-0"></i> <span class="truncate"></span>';
                            statusCol.appendChild(delayTag);
                        }
                        delayTag.title = shipment.delay_reason;
                        delayTag.querySelector('span').textContent = shipment.delay_reason;
                        delayTag.classList.remove('hidden');
                    } else if (delayTag) {
                        delayTag.remove();
                    }

                    let returnTag = statusCol.querySelector('.shipment-return-tag');
                    if (shipment.return_reason) {
                        if (!returnTag) {
                            returnTag = document.createElement('div');
                            returnTag.className = 'shipment-return-tag mt-1 flex items-center gap-1 text-[10px] text-red-700 font-semibold bg-red-50/80 px-2 py-0.5 rounded border border-red-200 max-w-[170px] truncate';
                            returnTag.innerHTML = '<i class="fa-solid fa-arrow-rotate-left shrink-0"></i> <span class="truncate"></span>';
                            statusCol.appendChild(returnTag);
                        }
                        returnTag.title = shipment.return_reason;
                        returnTag.querySelector('span').textContent = shipment.return_reason;
                        returnTag.classList.remove('hidden');
                    } else if (returnTag) {
                        returnTag.remove();
                    }
                }

                // تحديث عمود المندوب والاتصال
                const captainCol = row.querySelector('.shipment-captain-col');
                if (captainCol) {
                    if (shipment.captain_name) {
                        let phoneHtml = shipment.captain_phone ? `
                            <a href="tel:${shipment.captain_phone}" class="inline-flex items-center gap-1 text-[10px] bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded transition font-mono num-en" title="اتصال هاتفي">
                                <i class="fa-solid fa-phone"></i>
                                <span dir="ltr">${shipment.captain_phone}</span>
                            </a>` : '';
                        let waHtml = shipment.captain_whatsapp_url ? `
                            <a href="${shipment.captain_whatsapp_url}" target="_blank" class="inline-flex items-center gap-1 text-[10px] bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded transition" title="مراسلة واتساب">
                                <i class="fa-brands fa-whatsapp text-xs"></i>
                                <span>واتساب</span>
                            </a>` : '';
                        captainCol.innerHTML = `
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                                    <i class="fa-solid fa-motorcycle text-[#31006f] text-xs"></i>
                                    ${shipment.captain_name}
                                </span>
                                <div class="flex items-center gap-2 mt-0.5" onclick="event.stopPropagation()">
                                    ${phoneHtml}
                                    ${waHtml}
                                </div>
                            </div>
                        `;
                    } else {
                        captainCol.innerHTML = '<span class="text-xs text-gray-400 font-medium">لم يُحدد كابتن بعد</span>';
                    }
                }

                // تحديث عمود المالية والتحصيل (أرقام إنجليزية)
                const finCol = row.querySelector('.shipment-fin-col');
                if (finCol) {
                    const collected = parseFloat(shipment.collected_amount || 0);
                    const ret = parseFloat(shipment.return_amount || 0);
                    let collectedHtml = collected > 0 ? `<p class="text-xs text-green-600 font-bold mt-0.5 font-mono num-en">حصل: ${formatEnNumber(collected, 2)}</p>` : '';
                    let retHtml = ret > 0 ? `<p class="text-[10px] text-red-500 mt-0.5 font-mono num-en">مرتجع: ${formatEnNumber(ret, 2)}</p>` : '';
                    finCol.innerHTML = `
                        <p class="text-sm font-bold text-darkText font-mono num-en">${formatEnNumber(shipment.amount, 2)} EGP</p>
                        ${collectedHtml}
                        ${retHtml}
                    `;
                }

                // تحديث عمود التوقيت والمزامنة
                const timeCol = row.querySelector('.shipment-time-col');
                if (timeCol) {
                    const createdDate = formatEnDate(shipment.created_at).split(' ')[0];
                    const syncedStr = shipment.last_synced_at ? formatEnDate(shipment.last_synced_at) : 'لم يُزامن بعد';
                    timeCol.innerHTML = `
                        <p class="text-xs text-darkText font-mono num-en">${createdDate}</p>
                        <p class="text-[10px] text-mutedBrown mt-0.5 font-mono num-en">
                            <i class="fa-solid fa-arrows-rotate mr-0.5 text-gray-400"></i>
                            ${syncedStr}
                        </p>
                    `;
                }

                // وميض تمييز ناعم للصف الذي تم تحديثه
                row.classList.remove('bg-purple-100/70', 'ring-2', 'ring-purple-400');
                void row.offsetWidth; // إجبار المتصفح على إعادة الحساب لظهور التأثير
                row.classList.add('bg-purple-100/70', 'ring-2', 'ring-purple-400', 'transition-all', 'duration-500');
                setTimeout(() => {
                    row.classList.remove('bg-purple-100/70', 'ring-2', 'ring-purple-400');
                }, 2500);
            }

            // 2. تحديث كارت الموبايل (Mobile Card)
            const card = document.getElementById('shipment-card-' + shipment.id);
            if (card) {
                card.setAttribute('data-shipment-json', JSON.stringify(shipment));

                const cardStatusBadge = card.querySelector('.shipment-card-status-badge');
                if (cardStatusBadge) {
                    cardStatusBadge.textContent = shipment.status_text || shipment.status || 'جارِ';
                    cardStatusBadge.className = 'shipment-card-status-badge inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold border ' + (
                        shipment.is_delivered ? 'bg-green-50 text-green-700 border-green-200' :
                        (shipment.is_returned || shipment.is_canceled || String(shipment.status || '').includes('محذوف') || String(shipment.status || '').includes('ملغي')) ? 'bg-red-50 text-red-700 border-red-200' :
                        'bg-orange-50 text-orange-700 border-orange-200'
                    );
                }

                const cardCaptainCol = card.querySelector('.shipment-card-captain-col');
                if (cardCaptainCol) {
                    if (shipment.captain_name) {
                        let phoneBtn = shipment.captain_phone ? `<a href="tel:${shipment.captain_phone}" class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-mono num-en"><i class="fa-solid fa-phone text-xs"></i></a>` : '';
                        let waBtn = shipment.captain_whatsapp_url ? `<a href="${shipment.captain_whatsapp_url}" target="_blank" class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-brands fa-whatsapp text-xs"></i></a>` : '';
                        cardCaptainCol.innerHTML = `
                            <div class="bg-purple-50/60 p-2.5 rounded-xl border border-purple-100 flex items-center justify-between mb-2 text-xs" onclick="event.stopPropagation()">
                                <div class="flex items-center gap-1.5 font-bold text-purple-900">
                                    <i class="fa-solid fa-motorcycle text-[#31006f]"></i>
                                    <span>${shipment.captain_name}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    ${phoneBtn}
                                    ${waBtn}
                                </div>
                            </div>
                        `;
                    } else {
                        cardCaptainCol.innerHTML = '';
                    }
                }

                const cardDelayTag = card.querySelector('.shipment-card-delay-tag');
                if (cardDelayTag) {
                    if (shipment.delay_reason) {
                        cardDelayTag.innerHTML = `
                            <div class="bg-amber-50 text-amber-800 text-xs p-2 rounded-xl border border-amber-200 mb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-clock text-amber-600"></i>
                                <span>تأخير: ${shipment.delay_reason}</span>
                            </div>
                        `;
                    } else {
                        cardDelayTag.innerHTML = '';
                    }
                }

                const cardFinCol = card.querySelector('.shipment-card-fin-col');
                if (cardFinCol) {
                    const collected = parseFloat(shipment.collected_amount || 0);
                    let collectedHtml = collected > 0 ? `<p class="text-[10px] text-green-600 font-bold font-mono num-en">حصل: ${formatEnNumber(collected, 2)}</p>` : '';
                    cardFinCol.innerHTML = `
                        <p class="text-sm font-bold text-darkText font-mono num-en">${formatEnNumber(shipment.amount, 2)} EGP</p>
                        ${collectedHtml}
                    `;
                }

                card.classList.remove('ring-2', 'ring-[#31006f]', 'bg-purple-50/80');
                void card.offsetWidth;
                card.classList.add('ring-2', 'ring-[#31006f]', 'bg-purple-50/80', 'transition-all', 'duration-500');
                setTimeout(() => {
                    card.classList.remove('ring-2', 'ring-[#31006f]', 'bg-purple-50/80');
                }, 2500);
            }

            // إذا كان المودال مغلقاً والمستخدم ضغط تحديث، نمرر بلطف للعنصر إذا كان خارج مجال الرؤية
            const isModalOpen = modal && !modal.classList.contains('hidden');
            if (!isModalOpen) {
                const target = (window.innerWidth >= 768) ? row : card;
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        }

        // 4. المزامنة الفردية المباشرة (Single Shipment Sync - بدون ريفريش كامل)
        async function syncSingleShipment(shipmentId, btnElement) {
            const isModalBtn = btnElement && (btnElement.id === 'm_sync_btn');
            const originalHtml = btnElement ? btnElement.innerHTML : '';

            if (btnElement) {
                btnElement.disabled = true;
                if (isModalBtn) {
                    btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>جاري المزامنة...</span>';
                } else {
                    btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
                }
            }

            try {
                const res = await fetch(`/shipments/${shipmentId}/sync`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await res.json();
                if (data.success) {
                    showToast(data.message, true);

                    // 1. إذا كان مودال الشحنة مفتوحاً، حدّث محتواه فوراً في مكانه بدون إغلاق
                    if (currentModalShipment && String(currentModalShipment.id) === String(shipmentId)) {
                        openModal(data.shipment);
                    }

                    // 2. حدّث صف الشحنة أو كارت الموبايل في الصفحة مباشرة دون إعادة تحميل
                    updateShipmentInDom(data.shipment);

                    // 3. علامة تأكيد نجاح خضراء على الزر لمدة ثانيتين
                    if (btnElement) {
                        if (isModalBtn) {
                            btnElement.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i> <span class="text-emerald-700">تم التحديث</span>';
                        } else {
                            btnElement.innerHTML = '<i class="fa-solid fa-check text-emerald-600 text-xs"></i>';
                        }
                        setTimeout(() => {
                            btnElement.innerHTML = originalHtml;
                            btnElement.disabled = false;
                        }, 2000);
                    }
                    return;
                } else {
                    showToast(data.message || 'فشلت المزامنة', false);
                }
            } catch (err) {
                console.error(err);
                showToast('حدث خطأ أثناء الاتصال بالخادم', false);
            } finally {
                if (btnElement && btnElement.disabled) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = originalHtml;
                }
            }
        }

        function syncCurrentModalShipment() {
            if (!currentModalShipment) return;
            const btn = document.getElementById('m_sync_btn');
            syncSingleShipment(currentModalShipment.id, btn);
        }

        async function cancelCurrentModalShipment() {
            if (!currentModalShipment || !currentModalShipment.id) return;

            if (!confirm('هل أنت متأكد من رغبتك في إلغاء / حذف هذه الشحنة؟\nسيتم وسم حالتها كمحذوفة وتحديث حالة الطلب المرتبط إلى ملغي.')) {
                return;
            }

            const btn = document.getElementById('m_cancel_btn');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>جاري الإلغاء...</span>';
            }

            try {
                const res = await fetch(`/shipments/${currentModalShipment.id}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (data.success && data.shipment) {
                    currentModalShipment = data.shipment;
                    populateModal(data.shipment);
                    updateShipmentInDom(data.shipment);
                    showToast(data.message || 'تم إلغاء الشحنة ووسمها كمحذوفة بنجاح!', true);
                } else {
                    showToast(data.message || 'فشل إلغاء الشحنة', false);
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                }
            } catch (err) {
                console.error(err);
                showToast('حدث خطأ أثناء إلغاء الشحنة: ' + err.message, false);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        }

        // 5. المزامنة الجماعية (Bulk Sync)
        function openSyncModal() {
            const today = new Date().toISOString().split('T')[0];
            const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            document.getElementById('syncFromDate').value = monthAgo;
            document.getElementById('syncToDate').value = today;
            document.getElementById('syncModal').classList.remove('hidden');
        }

        function closeSyncModal() {
            document.getElementById('syncModal').classList.add('hidden');
        }

        async function executeBulkSync(e) {
            e.preventDefault();
            const from = document.getElementById('syncFromDate').value;
            const to = document.getElementById('syncToDate').value;
            const btn = document.getElementById('bulkSyncSubmitBtn');

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>جاري جلب البيانات من Turbo...</span>';

            try {
                const res = await fetch('/shipments/sync-turbo', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ from, to })
                });

                const data = await res.json();
                if (data.success) {
                    closeSyncModal();
                    showToast(data.message, true);
                    sessionStorage.setItem('shipments_scroll_top', window.scrollY);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'فشلت المزامنة', false);
                }
            } catch (err) {
                console.error(err);
                showToast('حدث خطأ أثناء الاتصال بالخادم', false);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> <span>بدء المزامنة الآن</span>';
            }
        }

        // 6. مودال الـ Webhook
        function openWebhookModal() {
            document.getElementById('webhookModal').classList.remove('hidden');
        }

        function closeWebhookModal() {
            document.getElementById('webhookModal').classList.add('hidden');
        }

        function copyWebhookUrl() {
            const val = document.getElementById('webhookCopyInput').value;
            navigator.clipboard.writeText(val);
            showToast('تم نسخ رابط الـ Webhook بنجاح!', true);
        }

        function copyText(text) {
            navigator.clipboard.writeText(text);
            showToast('تم نسخ رقم البوليصة: ' + text, true);
        }

        function copyModalTracking() {
            if (currentModalShipment && currentModalShipment.tracking_number) {
                copyText(currentModalShipment.tracking_number);
            }
        }

        // 6. Toast Helper
        function showToast(msg, isSuccess = true) {
            const toast = document.getElementById('toastNotification');
            const card = document.getElementById('toastCard');
            const icon = document.getElementById('toastIcon');
            const text = document.getElementById('toastMessage');

            text.innerText = msg;
            if (isSuccess) {
                card.className = 'px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-bold border bg-emerald-50 text-emerald-800 border-emerald-200';
                icon.className = 'fa-solid fa-circle-check text-emerald-600 text-base';
            } else {
                card.className = 'px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 text-sm font-bold border bg-red-50 text-red-800 border-red-200';
                icon.className = 'fa-solid fa-circle-xmark text-red-600 text-base';
            }

            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 4500);
        }

        // 7. الحفاظ التلقائي على مكان التمرير في الصفحة
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('shipments_scroll_top', window.scrollY);
        });
        document.addEventListener('DOMContentLoaded', () => {
            const savedScroll = sessionStorage.getItem('shipments_scroll_top');
            if (savedScroll !== null) {
                window.scrollTo({ top: parseInt(savedScroll, 10), behavior: 'instant' });
                sessionStorage.removeItem('shipments_scroll_top');
            }
        });
    </script>
@endpush
