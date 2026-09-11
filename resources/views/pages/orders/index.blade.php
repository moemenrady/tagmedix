@extends('layouts.app')

@section('title', 'الطلبات')
@section('page_title', 'إدارة الطلبات')

@section('content')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

        {{-- إحصائيات الأدمن (مدمجة) --}}
        @if (auth()->user() && auth()->user()->role == 'admin')
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div
                    class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition">
                    <div>
                        <p class="text-gray-500 text-[11px] font-bold mb-0.5">إجمالي الطلبات</p>
                        <h3 class="text-lg font-black text-gray-800">{{ \App\Models\Order::count() ?? 0 }}</h3>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600">
                        <i class="fa-solid fa-layer-group text-sm"></i>
                    </div>
                </div>

                <div
                    class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between hover:shadow-md transition">
                    <div>
                        <p class="text-gray-500 text-[11px] font-bold mb-0.5">قيد التجهيز</p>
                        <h3 class="text-lg font-black text-gray-800">
                            {{ \App\Models\Order::whereIn('status', ['new', 'cutting', 'sewing', 'ready-to-ship'])->count() ?? 0 }}
                        </h3>
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
                            {{ number_format(\App\Models\Order::sum('total_price') ?? 0, 0) }} <span
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

        {{-- الحاوية الرئيسية --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col">

            {{-- شريط الإجراءات والبحث والفلترة --}}
            <div class="p-3 border-b border-gray-200 bg-gray-50/50 flex flex-col gap-3">
                {{-- الصف الأول: زر إضافة طلب وبحث --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 w-full">
                    <a href="{{ route('orders.create') }}"
                        class="w-full sm:w-auto bg-gray-900 hover:bg-black text-white px-6 py-2 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2 active:scale-95">
                        <i class="fa-solid fa-plus"></i> طلب جديد
                    </a>

                    {{-- حقل البحث --}}
                    <div class="relative w-full sm:w-80 shrink-0">
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-xs"></i>
                        </div>
                        <input type="text" x-model="searchQuery" x-ref="searchInput"
                            placeholder="بحث... (اضغط / للتركيز)"
                            class="block w-full pr-8 pl-8 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 transition-all shadow-sm">
                        <button x-show="searchQuery.length > 0" @click="searchQuery = ''; $refs.searchInput.focus()"
                            class="absolute inset-y-0 left-3 flex items-center text-gray-400 hover:text-rose-500">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                {{-- الصف الثاني: الفلاتر (تلتف على الشاشات لتقليل التمرير الأفقي) --}}
                <div class="flex flex-wrap items-center gap-1 w-full">
                    <button @click="currentFilter = 'all'"
                        :class="currentFilter === 'all' ? 'bg-gray-900 text-white shadow-sm' :
                            'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition-colors">الكل</button>
                    @foreach ($statusMap as $key => $status)
                        <button @click="currentFilter = '{{ $key }}'"
                            :class="currentFilter === '{{ $key }}' ? 'bg-gray-900 text-white shadow-sm' :
                                'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                            class="px-3 py-1.5 rounded-lg text-[11px] font-bold transition-colors">{{ $status['label'] }}</button>
                    @endforeach
                </div>
            </div>

            {{-- 1. العرض الشاشات الكبيرة (Desktop Table View - Compact) --}}
            <div class="hidden md:block overflow-x-auto">
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
                            <tr class="hover:bg-blue-50/50 transition-colors group" x-data="{ liveStatus: '{{ $order->status }}', isUpdating: false }"
                                x-show="matchesSearch('{{ $order->order_number }}', '{{ addslashes($order->customer?->name ?? $order->customer_name) }}', '{{ $order->customer?->phone_1 ?? $order->phone_1 }}') && matchesFilter(liveStatus)"
                                x-transition>
                                <td class="px-4 py-2">
                                    <a href="{{ route('orders.show', $order->id) }}"
                                        class="font-bold text-gray-900 hover:text-blue-600 transition-colors">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex flex-col">
                                        <a href="{{ route('customers.index', ['q' => $order->customer?->phone_1 ?? ($order->phone_1 ?? ($order->customer?->name ?? $order->customer_name))]) }}"
                                            class="font-bold text-gray-800 text-sm hover:text-blue-600 hover:underline transition-colors"
                                            title="بحث عن العميل">{{ $order->customer?->name ?? $order->customer_name }}</a>
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
                                    {{-- القائمة المنسدلة الذكية للحالة (بضغطة واحدة) --}}
                                    <div class="relative w-full max-w-[140px]">
                                        <select x-model="liveStatus"
                                            @change="updateStatus({{ $order->id }}, liveStatus, $data)"
                                            :disabled="isUpdating" :class="getStatusClass(liveStatus)"
                                            class="status-select w-full cursor-pointer border-0 rounded-lg py-1.5 pl-2 pr-7 text-xs font-bold transition-all focus:ring-2 focus:ring-black/20 disabled:opacity-50">
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
                                    @php $shipment = $order->shipments ? $order->shipments->first() : null; @endphp
                                    @if ($shipment)
                                        <a href="{{ route('shipments.index', ['q' => $shipment->turbo_id ?: $order->order_number]) }}"
                                            class="mt-1 inline-flex items-center gap-1 text-[10px] text-amber-800 bg-amber-50 hover:bg-amber-100 px-2 py-0.5 rounded border border-amber-200 font-bold transition-all shadow-2xs"
                                            title="عرض في الشحنات">
                                            <i class="fa-solid fa-truck-fast text-[9px] text-amber-600"></i>
                                            <span>شحنة: {{ $shipment->turbo_id ?: 'Turbo' }}</span>
                                        </a>
                                    @endif
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
                                            class="w-7 h-7 rounded bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center transition-colors"
                                            title="عرض">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                                            class="w-7 h-7 rounded bg-gray-100 text-gray-700 hover:bg-purple-100 hover:text-purple-700 flex items-center justify-center transition-colors"
                                            title="طباعة">
                                            <i class="fa-solid fa-print text-xs"></i>
                                        </a>
                                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                            class="m-0 inline"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-7 h-7 rounded bg-gray-100 text-gray-700 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition-colors"
                                                title="حذف">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-16 text-center text-gray-400">
                                    <i class="fa-solid fa-inbox text-5xl mb-4 opacity-30"></i>
                                    <p class="text-base font-bold text-gray-500">لا توجد طلبات</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 2. عرض الموبايل (Mobile Cards - Optimized) --}}
            <div class="block md:hidden p-3 space-y-2 bg-gray-50">
                @forelse($orders as $order)
                    @php $phone = $order->customer?->phone_1 ?? $order->phone_1; @endphp
                    <div x-data="{ liveStatus: '{{ $order->status }}', isUpdating: false }"
                        x-show="matchesSearch('{{ $order->order_number }}', '{{ addslashes($order->customer?->name ?? $order->customer_name) }}', '{{ $phone }}') && matchesFilter(liveStatus)"
                        x-transition class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 flex flex-col gap-2">

                        {{-- رأس الكارت --}}
                        <div class="flex items-center justify-between">
                            <a href="{{ route('orders.show', $order->id) }}"
                                class="font-black text-gray-900 text-sm flex items-center gap-1 active:text-blue-600 transition">
                                <span class="text-gray-400">#</span>{{ $order->order_number }}
                            </a>
                            <div class="relative w-[125px]">
                                <select x-model="liveStatus"
                                    @change="updateStatus({{ $order->id }}, liveStatus, $data)" :disabled="isUpdating"
                                    :class="getStatusClass(liveStatus)"
                                    class="status-select w-full cursor-pointer border-0 rounded-lg py-1 pl-2 pr-6 text-[10px] font-bold focus:ring-2 focus:ring-black/10 disabled:opacity-50">
                                    @foreach ($statusMap as $key => $status)
                                        <option value="{{ $key }}">{{ $status['label'] }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-2 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-chevron-down text-[8px] opacity-60" x-show="!isUpdating"></i>
                                    <i class="fa-solid fa-spinner fa-spin text-[8px] opacity-80" x-show="isUpdating"></i>
                                </div>
                            </div>
                        </div>

                        @php $mobileShipment = $order->shipments ? $order->shipments->first() : null; @endphp
                        @if ($mobileShipment)
                            <div class="flex items-center justify-between bg-amber-50/70 border border-amber-200/80 px-2.5 py-1 rounded-lg text-[10px]">
                                <span class="text-amber-800 font-bold flex items-center gap-1.5">
                                    <i class="fa-solid fa-truck-fast text-amber-600"></i>
                                    <span>تمت الإضافة للشحنات ({{ $mobileShipment->turbo_id ?: 'Turbo' }})</span>
                                </span>
                                <a href="{{ route('shipments.index', ['q' => $mobileShipment->turbo_id ?: $order->order_number]) }}" class="text-blue-600 font-bold hover:underline">عرض</a>
                            </div>
                        @endif

                        {{-- العميل والعنوان --}}
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <a href="{{ route('customers.index', ['q' => $phone ?? ($order->customer?->name ?? $order->customer_name)]) }}"
                                    class="font-bold text-sm text-gray-800 hover:text-blue-600 hover:underline transition-colors"
                                    title="بحث عن العميل">{{ $order->customer?->name ?? $order->customer_name }}</a>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[11px] text-gray-500 font-mono"
                                        dir="ltr">{{ $phone }}</span>
                                    <span class="text-[10px] text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded"><i
                                            class="fa-solid fa-location-dot"></i>
                                        {{ $order->address?->governorate ?? 'غير محدد' }}</span>
                                </div>
                            </div>
                            @if ($phone)
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="tel:{{ $phone }}"
                                        class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs active:scale-90 transition">
                                        <i class="fa-solid fa-phone"></i>
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank"
                                        class="w-7 h-7 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-[13px] active:scale-90 transition">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- المالية والإجراءات --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <div>
                                <span class="text-sm font-black text-gray-900">{{ number_format($order->total_price, 0) }}
                                    <span class="text-[10px] text-gray-500">ج.م</span></span>
                                @if ($order->getRemainingAmount() > 0)
                                    <span class="text-[10px] text-rose-600 font-bold mr-1">متبقي:
                                        {{ number_format($order->getRemainingAmount(), 0) }}</span>
                                @endif
                            </div>
                            <div class="flex gap-1.5">

                                {{-- عرض --}}
                                <a href="{{ route('orders.show', $order->id) }}"
                                    class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-md text-gray-700 active:bg-gray-200 transition"
                                    title="عرض">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>

                                {{-- طباعة --}}
                                <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                                    class="w-8 h-8 flex items-center justify-center bg-purple-50 rounded-md text-purple-700 active:bg-purple-100 transition"
                                    title="طباعة">
                                    <i class="fa-solid fa-print text-[11px]"></i>
                                </a>

                                {{-- حذف --}}
                                <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                    class="m-0 inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center bg-rose-50 rounded-md text-rose-600 active:bg-rose-100 active:scale-95 transition"
                                        title="حذف">
                                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                                    </button>
                                </form>

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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('orderManager', () => ({
                searchQuery: '',
                currentFilter: 'all',
                filters: [{
                        label: 'الكل',
                        value: 'all'
                    },
                    @foreach ($statusMap as $key => $status)
                        {
                            label: '{{ $status['label'] }}',
                            value: '{{ $key }}'
                        },
                    @endforeach
                ],

                matchesSearch(orderNum, customerName, phone) {
                    if (this.searchQuery === '') return true;
                    const q = this.searchQuery.toLowerCase();
                    return orderNum.toLowerCase().includes(q) ||
                        customerName.toLowerCase().includes(q) ||
                        phone.toLowerCase().includes(q);
                },

                matchesFilter(status) {
                    if (this.currentFilter === 'all') return true;
                    return status === this.currentFilter;
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

                        if (!response.ok) {
                            const data = await response.json();
                            throw new Error(data.message || 'فشل التحديث');
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                        alert('حدث خطأ أثناء تحديث الحالة: ' + error.message);
                    } finally {
                        rowData.isUpdating = false;
                    }
                }
            }));
        });
    </script>
@endpush
