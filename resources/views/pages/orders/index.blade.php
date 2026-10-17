@extends('layouts.app')

@section('title', 'الطلبات')
@section('page_title', 'إدارة الطلبات')

@section('content')

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        select.status-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>

    <div dir="rtl" class="-mt-4 md:-mt-6 space-y-3 pb-8 font-sans" x-data="orderManager()"
        @keydown.window="(e) => { if(e.key === '/' && document.activeElement !== $refs.searchInput && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') { e.preventDefault(); $refs.searchInput.focus(); } }">

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms
                class="bg-emerald-50 border-r-4 border-emerald-500 p-3 rounded-lg flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-check-circle text-emerald-600 text-lg"></i>
                    <p class="text-emerald-800 font-bold text-sm">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        {{-- إحصائيات الأدمن (مغلفة بـ ID للتحديث الديناميكي) --}}
        <div id="stats-container">
            @if (auth()->user() && auth()->user()->role == 'admin')
                <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                    <div
                        class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition">
                        <div>
                            <p class="text-gray-500 text-[11px] font-bold mb-0.5">إجمالي الطلبات</p>
                            <h3 class="text-lg font-black text-gray-800">{{ number_format($stats['total_orders'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600">
                            <i class="fa-solid fa-layer-group text-sm"></i>
                        </div>
                    </div>

                    {{-- كارت الطلبات المستعجلة --}}
                    <div
                        @click="currentUrgency = currentUrgency === 'urgent' ? 'all' : 'urgent'; applyFilters()"
                        class="bg-white p-3 rounded-xl border border-red-200 shadow-sm flex items-center justify-between hover:shadow-md transition cursor-pointer"
                        :class="currentUrgency === 'urgent' ? 'ring-2 ring-red-500 bg-red-50/40' : ''"
                        title="انقر لتصفية الطلبات المستعجلة">
                        <div>
                            <p class="text-red-600 text-[11px] font-bold mb-0.5 flex items-center gap-1">
                                <i class="fa-solid fa-bolt text-red-500 animate-bounce"></i> طلبيات مستعجلة
                            </p>
                            <h3 class="text-lg font-black text-red-600">
                                {{ number_format($stats['urgent_orders'] ?? 0) }}
                            </h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                            <i class="fa-solid fa-fire-flame-curved text-sm"></i>
                        </div>
                    </div>

                    <div
                        class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition">
                        <div>
                            <p class="text-gray-500 text-[11px] font-bold mb-0.5">قيد التجهيز</p>
                            <h3 class="text-lg font-black text-gray-800">
                                {{ number_format($stats['processing_orders'] ?? 0) }}</h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-box-open text-sm"></i>
                        </div>
                    </div>

                    <div
                        class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition">
                        <div>
                            <p class="text-gray-500 text-[11px] font-bold mb-0.5">الإيرادات</p>
                            <h3 class="text-base font-black text-gray-800">
                                {{ number_format($stats['total_revenue'] ?? 0, 0) }} <span
                                    class="text-[10px] text-gray-400 font-normal">ج</span>
                            </h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i class="fa-solid fa-wallet text-sm"></i>
                        </div>
                    </div>

                    <div
                        class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition">
                        <div>
                            <p class="text-gray-500 text-[11px] font-bold mb-0.5">المدفوع</p>
                            <h3 class="text-base font-black text-gray-800">
                                {{ number_format($stats['paid_amount'] ?? 0, 0) }} <span
                                    class="text-[10px] text-gray-400 font-normal">ج</span>
                            </h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
                        </div>
                    </div>

                    <div
                        class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition">
                        <div>
                            <p class="text-gray-500 text-[11px] font-bold mb-0.5">المتبقي</p>
                            <h3 class="text-base font-black text-rose-600">
                                {{ number_format($stats['remaining_payments'] ?? 0, 0) }} <span
                                    class="text-[10px] text-rose-400 font-normal">ج</span>
                            </h3>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600">
                            <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- الحاوية الرئيسية --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col relative"
            :class="{ 'opacity-70 pointer-events-none': isFetching }">

            {{-- مؤشر تحميل خفيف --}}
            <div x-show="isFetching"
                class="absolute inset-0 z-50 flex items-center justify-center bg-white/40 backdrop-blur-[1px] rounded-xl"
                style="display: none;">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-gray-900"></i>
            </div>

            {{-- شريط الإجراءات والبحث والفلترة --}}
            <div class="p-3 border-b border-gray-200 bg-gray-50/50 flex flex-col gap-3">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 w-full">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a href="{{ route('orders.create') }}"
                            class="w-full sm:w-auto bg-gray-900 hover:bg-black text-white px-5 py-2 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-plus"></i> طلب جديد
                        </a>

                        {{-- فلتر الاستعجال السريع --}}
                        <button type="button" @click="currentUrgency = (currentUrgency === 'urgent' ? 'all' : 'urgent'); applyFilters()"
                            :class="currentUrgency === 'urgent' ? 'bg-red-600 text-white shadow-sm ring-2 ring-red-400' : 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200'"
                            class="px-3.5 py-2 rounded-lg text-xs font-black transition-all flex items-center gap-1.5 shrink-0">
                            <i class="fa-solid fa-bolt"></i>
                            <span>المستعجل فقط ({{ $stats['urgent_orders'] ?? 0 }})</span>
                        </button>
                    </div>

                    {{-- قسم الترتيب والبحث --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        {{-- قائمة الترتيب --}}
                        <div class="relative shrink-0">
                            <select x-model="currentSort" @change="applyFilters()"
                                class="h-[38px] px-3 pr-7 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-700 focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 appearance-none shadow-sm cursor-pointer">
                                <option value="latest">الأحدث إضافة (افتراضي)</option>
                                <option value="urgent_first">أولوية المستعجل أولاً</option>
                                <option value="delivery_soonest">موعد التسليم الأقرب</option>
                                <option value="price_desc">الأعلى سعراً</option>
                                <option value="price_asc">الأقل سعراً</option>
                            </select>
                            <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-gray-400 text-[10px]">
                                <i class="fa-solid fa-sort"></i>
                            </div>
                        </div>

                        {{-- حقل البحث --}}
                        <div class="relative w-full sm:w-72 shrink-0">
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-search text-xs"></i>
                            </div>
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="applyFilters()"
                                x-ref="searchInput" placeholder="بحث... (اضغط / للتركيز)"
                                class="block w-full pr-8 pl-8 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all shadow-sm">
                            <button x-show="searchQuery.length > 0"
                                @click="searchQuery = ''; applyFilters(); $refs.searchInput.focus()"
                                class="absolute inset-y-0 left-3 flex items-center text-gray-400 hover:text-rose-500">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- فلاتر الحالة --}}
                <div class="flex flex-wrap items-center gap-1 w-full">
                    <button @click="currentFilter = 'all'; applyFilters()"
                        :class="currentFilter === 'all' ? 'bg-gray-900 text-white shadow-sm' :
                            'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition-colors">الكل</button>
                    @foreach ($statusMap as $key => $status)
                        <button @click="currentFilter = '{{ $key }}'; applyFilters()"
                            :class="currentFilter === '{{ $key }}' ? 'bg-gray-900 text-white shadow-sm' :
                                'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                            class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition-colors">{{ $status['label'] }}</button>
                    @endforeach
                </div>
            </div>

            {{-- قسم محتوى الطلبات المغلف للتبديل الديناميكي --}}
            <div id="orders-container">
                {{-- 1. العرض الشاشات الكبيرة --}}
                <div class="hidden md:block overflow-x-auto min-h-[400px]">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-gray-100 text-gray-700 font-bold sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-2.5 whitespace-nowrap w-20">الطلب</th>
                                <th class="px-4 py-2.5 whitespace-nowrap">العميل</th>
                                <th class="px-4 py-2.5 whitespace-nowrap w-40">العنوان</th>
                                <th class="px-4 py-2.5 whitespace-nowrap w-40">الحالة</th>
                                <th class="px-4 py-2.5 whitespace-nowrap w-32">الإجمالي</th>
                                <th class="px-4 py-2.5 whitespace-nowrap w-40">المالية</th>
                                <th class="px-4 py-2.5 text-center whitespace-nowrap w-32">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders as $order)
                                <tr class="hover:bg-blue-50/50 transition-colors group" x-data="{ liveStatus: '{{ $order->status }}', isUpdating: false }">
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <a href="{{ route('orders.show', $order->id) }}"
                                                class="font-bold text-gray-900 hover:text-blue-600 transition-colors">
                                                {{ $order->order_number }}
                                            </a>
                                            @if ($order->is_urgent)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-red-100 text-red-600 border border-red-200 flex items-center gap-0.5">
                                                    <i class="fa-solid fa-bolt text-[9px]"></i> عاجل
                                                </span>
                                            @endif
                                            @php $deliveryInfo = $order->delivery_status_info; @endphp
                                            @if ($deliveryInfo['has_date'])
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $deliveryInfo['badge_class'] }}" title="تاريخ التسليم المتوقع: {{ $order->delivery_date?->format('Y-m-d') }}">
                                                    {{ $deliveryInfo['label'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-col">
                                            <a href="{{ route('customers.index', ['q' => $order->customer?->phone_1 ?? ($order->phone_1 ?? ($order->customer?->name ?? $order->customer_name))]) }}"
                                                class="font-bold text-gray-800 text-sm hover:text-blue-600 hover:underline transition-colors">
                                                {{ $order->customer?->name ?? $order->customer_name }}
                                            </a>
                                            <span class="text-[11px] text-gray-500 font-mono"
                                                dir="ltr">{{ $order->customer?->phone_1 ?? $order->phone_1 }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-col">
                                            <span
                                                class="font-bold text-gray-800 text-[12px]">{{ $order->address?->governorate ?? 'غير محدد' }}</span>
                                            <span class="text-[10px] text-gray-500 truncate max-w-[140px]"
                                                title="{{ $order->address?->full_address ?? '' }}">{{ $order->address?->full_address ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="relative w-full max-w-[140px]">
                                            <select x-model="liveStatus"
                                                @change="updateStatus({{ $order->id }}, liveStatus, $data)"
                                                :disabled="isUpdating" :class="getStatusClass(liveStatus)"
                                                class="status-select w-full cursor-pointer border-0 rounded-lg py-1.5 pl-2 pr-7 text-xs font-bold transition-all focus:ring-2 focus:ring-black/20 disabled:opacity-50">
                                                @if (!array_key_exists($order->status, $statusMap))
                                                    <option value="{{ $order->status }}" selected disabled>{{ $order->status ?: 'غير محدد' }}</option>
                                                @endif
                                                @foreach ($statusMap as $key => $status)
                                                    <option value="{{ $key }}">{{ $status['label'] }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
                                                <i class="fa-solid fa-chevron-down text-[10px] opacity-60"
                                                    x-show="!isUpdating"></i>
                                                <i class="fa-solid fa-spinner fa-spin text-[10px] opacity-80"
                                                    x-show="isUpdating"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 font-black text-gray-900">
                                        {{ number_format($order->total_price, 0) }} <span
                                            class="text-[10px] text-gray-400 font-normal">ج.م</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-col text-[11px] font-bold gap-0.5">
                                            <div class="flex justify-between items-center text-emerald-700">
                                                <span>مدفوع:</span>
                                                <span>{{ number_format($order->getTotalPaid(), 0) }}</span>
                                            </div>
                                            @if ($order->getRemainingAmount() > 0)
                                                <div class="flex justify-between items-center text-rose-600">
                                                    <span>متبقي:</span>
                                                    <span>{{ number_format($order->getRemainingAmount(), 0) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div
                                            class="flex items-center justify-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('orders.show', $order->id) }}"
                                                class="w-7 h-7 rounded bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center transition-colors"><i
                                                    class="fa-solid fa-eye text-xs"></i></a>
                                            <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                                                class="w-7 h-7 rounded bg-gray-100 text-gray-700 hover:bg-purple-100 hover:text-purple-700 flex items-center justify-center transition-colors"><i
                                                    class="fa-solid fa-print text-xs"></i></a>
                                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                                class="m-0 inline"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="w-7 h-7 rounded bg-gray-100 text-gray-700 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition-colors"><i
                                                        class="fa-solid fa-trash-can text-xs"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-16 text-center text-gray-400">
                                        <i class="fa-solid fa-inbox text-5xl mb-4 opacity-30"></i>
                                        <p class="text-base font-bold text-gray-500">لا توجد طلبات</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 2. عرض الموبايل --}}
                <div class="block md:hidden p-3 space-y-2 bg-gray-50 min-h-[400px]">
                    @forelse($orders as $order)
                        @php $phone = $order->customer?->phone_1 ?? $order->phone_1; @endphp
                        <div x-data="{ liveStatus: '{{ $order->status }}', isUpdating: false }"
                            class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 flex flex-col gap-2">
                           <div class="flex items-center justify-between">
    <div class="flex items-center gap-1.5 flex-wrap">
        <a href="{{ route('orders.show', $order->id) }}"
            class="font-black text-gray-900 text-sm flex items-center gap-1 active:text-blue-600 transition">
            <span class="text-gray-400">#</span>{{ $order->order_number }}
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

    <div class="flex items-center gap-1.5">
        {{-- الحالة --}}
        <div class="relative w-[125px]">
            <select x-model="liveStatus"
                @change="updateStatus({{ $order->id }}, liveStatus, $data)"
                :disabled="isUpdating"
                :class="getStatusClass(liveStatus)"
                class="status-select w-full cursor-pointer border-0 rounded-lg py-1 pl-2 pr-6 text-[10px] font-bold focus:ring-2 focus:ring-black/10 disabled:opacity-50">
                @foreach ($statusMap as $key => $status)
                    <option value="{{ $key }}">{{ $status['label'] }}</option>
                @endforeach
            </select>

            <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
                <i class="fa-solid fa-chevron-down text-[8px] opacity-60"
                    x-show="!isUpdating"></i>

                <i class="fa-solid fa-spinner fa-spin text-[8px] opacity-80"
                    x-show="isUpdating"></i>
            </div>
        </div>

        {{-- حذف الطلب --}}
        <form action="{{ route('orders.destroy', $order->id) }}"
            method="POST"
            class="m-0"
            onsubmit="return confirm('هل أنت متأكد من حذف الطلب #{{ $order->order_number }}؟');">
            @csrf
            @method('DELETE')

            <button type="submit"
                class="w-8 h-8 flex items-center justify-center bg-rose-50 rounded-md text-rose-600 active:bg-rose-100 transition"
                title="حذف الطلب">
                <i class="fa-solid fa-trash-can text-[11px]"></i>
            </button>
        </form>
    </div>
</div>

                            {{-- العميل والعنوان --}}
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span
                                        class="font-bold text-sm text-gray-800">{{ $order->customer?->name ?? $order->customer_name }}</span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[11px] text-gray-500 font-mono"
                                            dir="ltr">{{ $phone }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <div>
                                    <span
                                        class="text-sm font-black text-gray-900">{{ number_format($order->total_price, 0) }}
                                        <span class="text-[10px] text-gray-500">ج.م</span></span>
                                </div>
                                <div class="flex gap-1.5">
                                    <a href="{{ route('orders.show', $order->id) }}"
                                        class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-md text-gray-700 active:bg-gray-200 transition"><i
                                            class="fa-solid fa-eye text-[11px]"></i></a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400 bg-white rounded-xl border border-gray-200">
                            <i class="fa-solid fa-inbox text-4xl mb-3 opacity-30"></i>
                            <p class="text-sm font-bold text-gray-500">لا توجد طلبات</p>
                        </div>
                    @endforelse
                </div>

                {{-- روابط الترقيم --}}
                <div class="p-3 border-t border-gray-200 bg-white pagination-links">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderManager', () => ({
                searchQuery: '{{ request('q') }}',
                currentFilter: '{{ request('status', 'all') }}',
                currentUrgency: '{{ request('urgency', 'all') }}',
                currentSort: '{{ request('sort', 'latest') }}',
                isFetching: false,

                init() {
                    // اعتراض ضغطات أزرار الترقيم ليتم جلبها كـ SPA بدون ريفرش للصفحة
                    document.addEventListener('click', (e) => {
                        const link = e.target.closest('.pagination-links a');
                        if (link) {
                            e.preventDefault();
                            this.fetchPageContent(link.href, true);
                        }
                    });
                },

                applyFilters() {
                    const url = new URL(window.location.origin + window.location.pathname);
                    if (this.searchQuery) url.searchParams.set('q', this.searchQuery);
                    if (this.currentFilter !== 'all') url.searchParams.set('status', this.currentFilter);
                    if (this.currentUrgency !== 'all') url.searchParams.set('urgency', this.currentUrgency);
                    if (this.currentSort !== 'latest') url.searchParams.set('sort', this.currentSort);

                    this.fetchPageContent(url.toString(), true);
                },

                async fetchPageContent(url, updateHistory = false) {
                    this.isFetching = true;
                    if (updateHistory) {
                        window.history.pushState({}, '', url);
                    }

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const html = await response.text();

                        // استخراج الدوم الجديد وتحديث الأجزاء المتغيرة فقط
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        document.querySelector('#orders-container').innerHTML = doc.querySelector(
                            '#orders-container').innerHTML;
                        document.querySelector('#stats-container').innerHTML = doc.querySelector(
                            '#stats-container').innerHTML;
                    } catch (error) {
                        console.error('Error Fetching Data:', error);
                        window.location.href = url; // للرجوع للأسلوب العادي في حالة فشل الجلب
                    } finally {
                        this.isFetching = false;
                    }
                },

                getStatusClass(status) {
                    const classes = {
                        @foreach ($statusMap as $key => $status)
                            '{{ $key }}': '{{ $status['class'] }}',
                        @endforeach
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800';
                },

                async updateStatus(orderId, newStatus, rowData) {
                    if (rowData.isUpdating) return;
                    rowData.isUpdating = true;
                    try {
                        const response = await fetch(`/orders/${orderId}/change-status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                status: newStatus
                            })
                        });
                        if (!response.ok) throw new Error('فشل التحديث');
                    } catch (error) {
                        alert('حدث خطأ أثناء تحديث الحالة');
                    } finally {
                        rowData.isUpdating = false;
                    }
                }
            }));
        });
    </script>
@endpush
