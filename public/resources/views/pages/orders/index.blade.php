@extends('layouts.app')

@section('title', 'الطلبات')
@section('page_title', 'إدارة الطلبات الذكية')

@section('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* تصميم سكرول بار أنيق للجدول والفلاتر */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
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
    </style>

    <div dir="rtl" class="space-y-6 pb-10 font-sans" x-data="orderManager()" x-init="initData()">

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms
                class="bg-emerald-50 border-r-4 border-emerald-500 p-4 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                    </div>
                    <p class="text-emerald-800 font-medium text-sm md:text-base">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        {{-- إحصائيات الأدمن --}}
        @if (auth()->user() && auth()->user()->role == 'admin')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-5">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium mb-1">إجمالي الطلبات</p>
                        <h3 class="text-2xl font-black text-gray-800">{{ \App\Models\Order::count() ?? 0 }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600">
                        <i class="fa-solid fa-layer-group text-xl"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium mb-1">قيد التجهيز</p>
                        <h3 class="text-2xl font-black text-gray-800">
                            {{ \App\Models\Order::whereIn('status', ['confirmed', 'cutting', 'sewing', 'finishing', 'ready-to-ship'])->count() ?? 0 }}
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="fa-solid fa-box-open text-xl"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium mb-1">إجمالي الإيرادات</p>
                        <h3 class="text-xl font-black text-gray-800">
                            {{ number_format(\App\Models\Order::sum('total_price') ?? 0, 0) }} <span class="text-xs text-gray-400 font-normal">ج.م</span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="fa-solid fa-wallet text-xl"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium mb-1">إجمالي المدفوع</p>
                        <h3 class="text-xl font-black text-gray-800">
                            {{ number_format($stats['paid_amount'], 0) }} <span class="text-xs text-gray-400 font-normal">ج.م</span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center text-green-600">
                        <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium mb-1">المتبقي للتحصيل</p>
                        <h3 class="text-xl font-black text-gray-800">
                            {{ number_format($stats['remaining_payments'], 0) }} <span class="text-xs text-gray-400 font-normal">ج.م</span>
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                        <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                    </div>
                </div>
            </div>
        @endif

        {{-- الحاوية الرئيسية --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden flex flex-col">

            {{-- شريط الإجراءات والبحث والفلترة --}}
            <div class="p-4 md:p-5 border-b border-gray-100 bg-gray-50/50 space-y-3 md:space-y-0 md:flex md:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('orders.create') }}"
                        class="bg-gray-900 hover:bg-gray-800 active:scale-95 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> طلب جديد
                    </a>

                    {{-- الفلاتر السريعة (سحب أفقية للموبايل) --}}
                    <div class="flex overflow-x-auto no-scrollbar py-1 gap-1.5 bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                        <template x-for="filter in filters" :key="filter.value">
                            <button @click="currentFilter = filter.value"
                                :class="currentFilter === filter.value ? 'bg-gray-900 text-white font-bold' : 'text-gray-600 hover:bg-gray-100'"
                                class="px-3 py-1.5 rounded-lg text-xs whitespace-nowrap transition-all shrink-0"
                                x-text="filter.label"></button>
                        </template>
                    </div>
                </div>

                {{-- حقل البحث --}}
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="ابحث برقم الطلب، الاسم، أو الهاتف..."
                        class="block w-full pr-10 pl-9 py-2.5 border border-gray-200 rounded-xl text-xs md:text-sm focus:ring-2 focus:ring-gray-900/10 focus:border-gray-900 bg-white shadow-sm placeholder-gray-400 transition-all">

                    <button x-show="searchQuery.length > 0" @click="searchQuery = ''"
                        class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            </div>

            {{-- 1. العرض الشاشات الكبيرة (Desktop Table View) --}}
            <div class="hidden md:block overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 65vh;">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-100 text-gray-600 font-bold sticky top-0 z-10 shadow-sm backdrop-blur-md bg-opacity-90">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">#</th>
                            <th class="px-6 py-4 whitespace-nowrap">رقم الطلب</th>
                            <th class="px-6 py-4 whitespace-nowrap">العميل</th>
                            <th class="px-6 py-4 whitespace-nowrap">الحالة (انقر للتعديل)</th>
                            <th class="px-6 py-4 whitespace-nowrap">الإجمالي</th>
                            <th class="px-6 py-4 whitespace-nowrap">المالية</th>
                            <th class="px-6 py-4 text-center whitespace-nowrap">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($orders as $order)
                            <tr class="hover:bg-blue-50/30 transition-colors group" x-data="{ liveStatus: '{{ $order->status }}' }"
                                x-show="matchesSearch('{{ $order->order_number }}', '{{ addslashes($order->customer?->name ?? $order->customer_name) }}', '{{ $order->customer?->phone_1 ?? $order->phone_1 }}') && matchesFilter(liveStatus)"
                                x-transition>
                                <td class="px-6 py-4 text-gray-400 font-medium">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    <a href="{{ route('orders.show', $order->id) }}" class="hover:text-amber-600 transition-colors flex items-center gap-2">
                                        <i class="fa-solid fa-hashtag text-gray-300 text-xs"></i>{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold shrink-0">
                                            {{ mb_substr($order->customer?->name ?? $order->customer_name, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800">{{ $order->customer?->name ?? $order->customer_name }}</span>
                                            <span class="text-xs text-gray-500 font-mono tracking-wider" dir="ltr">{{ $order->customer?->phone_1 ?? $order->phone_1 }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4" x-data="{ editMode: false, currentStatus: liveStatus, isUpdating: false }">
                                    <div x-show="!editMode" @click="editMode = true"
                                        class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border transition-all hover:shadow-md hover:scale-105"
                                        :class="getStatusClass(currentStatus)" title="انقر لتعديل الحالة">
                                        <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                        <span x-text="getStatusLabel(currentStatus)"></span>
                                        <i class="fa-solid fa-pen text-[10px] opacity-50 ml-1"></i>
                                    </div>
                                    <div x-show="editMode" @click.away="editMode = false" x-transition class="relative flex items-center gap-2">
                                        <select x-model="currentStatus"
                                            @change="liveStatus = currentStatus; updateStatus({{ $order->id }}, currentStatus); editMode = false;"
                                            class="border border-amber-500 ring-2 ring-amber-500/20 rounded-lg p-1.5 text-xs font-bold bg-white text-gray-700 shadow-lg cursor-pointer focus:outline-none">
                                            @foreach ($statusMap as $key => $status)
                                                <option value="{{ $key }}">{{ $status['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <button @click="editMode = false" class="text-gray-400 hover:text-red-500 transition">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-black text-gray-900">
                                    {{ number_format($order->total_price, 0) }} <span class="text-xs text-gray-400 font-normal">ج.م</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col text-xs font-bold gap-1">
                                        <div class="flex justify-between items-center bg-emerald-50 text-emerald-700 px-2 py-1 rounded">
                                            <span>مدفوع:</span>
                                            <span>{{ number_format($order->getTotalPaid(), 0) }}</span>
                                        </div>
                                        @if ($order->getRemainingAmount() > 0)
                                            <div class="flex justify-between items-center bg-rose-50 text-rose-700 px-2 py-1 rounded">
                                                <span>متبقي:</span>
                                                <span>{{ number_format($order->getRemainingAmount(), 0) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('orders.show', $order->id) }}" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-blue-50 hover:text-blue-600 flex items-center justify-center transition-colors" title="عرض">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-purple-50 hover:text-purple-600 flex items-center justify-center transition-colors" title="طباعة">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="m-0 inline" onsubmit="return confirmDeleteOrder();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition-colors" title="حذف">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                    <i class="fa-solid fa-folder-open text-6xl mb-4 opacity-50"></i>
                                    <p class="text-lg font-medium text-gray-500">لا توجد طلبات مسجلة حالياً</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 2. عرض الموبايل (Mobile Responsive Cards View) --}}
            <div class="block md:hidden p-4 space-y-4 bg-gray-50/50">
                @forelse($orders as $order)
                    @php $phone = $order->customer?->phone_1 ?? $order->phone_1; @endphp
                    <div x-data="{ liveStatus: '{{ $order->status }}', editMode: false, currentStatus: '{{ $order->status }}', isUpdating: false }"
                        x-show="matchesSearch('{{ $order->order_number }}', '{{ addslashes($order->customer?->name ?? $order->customer_name) }}', '{{ $phone }}') && matchesFilter(liveStatus)"
                        x-transition
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3 hover:shadow-md transition-all relative">

                        {{-- الهيدر: رقم الطلب + الحالة --}}
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <a href="{{ route('orders.show', $order->id) }}" class="font-black text-gray-900 text-base flex items-center gap-1.5 active:text-amber-600">
                                <i class="fa-solid fa-hashtag text-gray-300 text-xs"></i>
                                <span>{{ $order->order_number }}</span>
                            </a>

                            {{-- حالة الطلب للتعديل باللمس --}}
                            <div class="relative">
                                <div x-show="!editMode" @click="editMode = true"
                                    class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border transition-all active:scale-95"
                                    :class="getStatusClass(currentStatus)">
                                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                    <span x-text="getStatusLabel(currentStatus)"></span>
                                    <i class="fa-solid fa-pen text-[9px] opacity-60 mr-1"></i>
                                </div>

                                <div x-show="editMode" @click.away="editMode = false" x-transition
                                    class="absolute left-0 top-0 z-20 flex items-center gap-1 bg-white p-1 rounded-xl shadow-xl border border-gray-200 min-w-[140px]">
                                    <select x-model="currentStatus"
                                        @change="liveStatus = currentStatus; updateStatus({{ $order->id }}, currentStatus); editMode = false;"
                                        class="w-full border border-amber-500 rounded-lg p-1.5 text-xs font-bold bg-white text-gray-800 focus:outline-none">
                                        @foreach ($statusMap as $key => $status)
                                            <option value="{{ $key }}">{{ $status['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <button @click="editMode = false" class="p-1 text-gray-400 hover:text-red-500">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- بيانات العميل والاتصال السريع --}}
                        <div class="flex items-center justify-between py-1">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 font-bold flex items-center justify-center shrink-0 text-sm">
                                    {{ mb_substr($order->customer?->name ?? $order->customer_name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900 text-sm">{{ $order->customer?->name ?? $order->customer_name }}</span>
                                    <span class="text-xs text-gray-500 font-mono" dir="ltr">{{ $phone }}</span>
                                </div>
                            </div>

                            @if($phone)
                                <div class="flex items-center gap-2">
                                    <a href="tel:{{ $phone }}" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs active:scale-90 transition">
                                        <i class="fa-solid fa-phone"></i>
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" target="_blank" class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xs active:scale-90 transition">
                                        <i class="fa-brands fa-whatsapp text-sm"></i>
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- المبالغ والمالية --}}
                        <div class="bg-gray-50 p-3 rounded-xl flex items-center justify-between text-xs">
                            <div>
                                <span class="text-gray-400 block text-[10px] font-medium">الإجمالي</span>
                                <span class="font-black text-gray-900 text-base">{{ number_format($order->total_price, 0) }} <span class="text-xs font-normal text-gray-400">ج.م</span></span>
                            </div>
                            <div class="flex gap-1.5 text-[11px] font-bold">
                                <span class="bg-emerald-100/80 text-emerald-800 px-2 py-1 rounded-md">
                                    مدفوع: {{ number_format($order->getTotalPaid(), 0) }}
                                </span>
                                @if ($order->getRemainingAmount() > 0)
                                    <span class="bg-rose-100/80 text-rose-800 px-2 py-1 rounded-md">
                                        متبقي: {{ number_format($order->getRemainingAmount(), 0) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- أزرار التحكم السريعة للموبايل --}}
                        <div class="pt-2 flex items-center gap-2 border-t border-gray-100">
                            <a href="{{ route('orders.show', $order->id) }}" class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 active:scale-95 text-gray-700 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                                <i class="fa-regular fa-eye"></i> عرض
                            </a>
                            <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="flex-1 py-2 bg-purple-50 hover:bg-purple-100 active:scale-95 text-purple-700 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                                <i class="fa-solid fa-print"></i> طباعة
                            </a>
                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="m-0" onsubmit="return confirmDeleteOrder();">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 active:scale-95 text-rose-600 rounded-xl text-xs font-bold flex items-center justify-center transition" title="حذف">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
                        <i class="fa-solid fa-folder-open text-5xl mb-3 opacity-40"></i>
                        <p class="text-base font-medium text-gray-500">لا توجد طلبات مسجلة حالياً</p>
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-400 font-medium">
                نهاية قائمة الطلبات
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
                filters: [
                    { label: 'الكل', value: 'all' },
                    @foreach ($statusMap as $key => $status)
                        { label: '{{ $status['label'] }}', value: '{{ $key }}' },
                    @endforeach
                ],

                initData() {},

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
                    return classes[status] || 'bg-gray-50 text-gray-700 border-gray-200';
                },

                getStatusLabel(status) {
                    const labels = {
                        @foreach ($statusMap as $key => $status)
                            '{{ $key }}': '{{ $status['label'] }}',
                        @endforeach
                    };
                    return labels[status] || status;
                },

                async updateStatus(orderId, newStatus) {
                    if (this.isUpdating) return;
                    this.isUpdating = true;

                    try {
                        const response = await fetch(`/orders/${orderId}/change-status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'فشل التحديث');

                    } catch (error) {
                        console.error('Error updating status:', error);
                        alert('حدث خطأ أثناء تحديث الحالة: ' + error.message);
                    } finally {
                        this.isUpdating = false;
                    }
                }
            }));
        });

        function confirmDeleteOrder() {
            return confirm(`⚠️ تحذير\n\nسيتم حذف هذا الطلب وكافة الدفعات المرتبطة به.\nهل أنت متأكد من المتابعة؟`);
        }
    </script>
@endpush