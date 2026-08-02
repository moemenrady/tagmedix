@extends('layouts.app')

@section('title', 'الطلبات')
@section('page_title', 'إدارة الطلبات الذكية')

@section('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* تصميم سكرول بار أنيق للجدول ليتناسب مع عرض كل البيانات */
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
    </style>

    <div dir="rtl" class="space-y-6 pb-10 font-sans" x-data="orderManager()" x-init="initData()">

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms
                class="bg-emerald-50 border-r-4 border-emerald-500 p-4 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                    </div>
                    <p class="text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif
        @if (auth()->user() && auth()->user()->role == 'admin')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div
                    class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-1">إجمالي الطلبات</p>
                        <h3 class="text-3xl font-black text-gray-800">{{ \App\Models\Order::count() ?? 0 }}</h3>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center text-purple-600 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-layer-group text-2xl"></i>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-1">قيد التجهيز</p>
                        <h3 class="text-3xl font-black text-gray-800">
                            {{ \App\Models\Order::whereIn('status', ['confirmed', 'cutting', 'sewing', 'finishing', 'ready-to-ship'])->count() ?? 0 }}
                        </h3>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-box-open text-2xl"></i>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-1">إجمالي الإيرادات</p>
                        <h3 class="text-2xl font-black text-gray-800">
                            {{ number_format(\App\Models\Order::sum('total_price') ?? 0, 0) }} <span
                                class="text-sm text-gray-400 font-normal">ج.م</span></h3>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-wallet text-2xl"></i>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-1">المتبقي للتحصيل</p>
                        <h3 class="text-2xl font-black text-gray-800">
                            {{ number_format($stats['remaining_payments'], 0) }} <span
                                class="text-sm text-gray-400 font-normal">ج.م</span></h3>
                    </div>
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-50 to-rose-100 flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-invoice-dollar text-2xl"></i>
                    </div>
                </div>
                <div
                    class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">

                    <div>
                        <p class="text-gray-500 text-sm font-medium mb-1">إجمالي المدفوع</p>

                        <h3 class="text-2xl font-black text-gray-800">
                            {{ number_format($stats['paid_amount'], 0) }}
                            <span class="text-sm text-gray-400 font-normal">ج.م</span>
                        </h3>
                    </div>

                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center text-green-600 group-hover:scale-110 transition-transform">

                        <i class="fa-solid fa-hand-holding-dollar text-2xl"></i>
                    </div>
                </div>
            </div>
        @endif
        <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden flex flex-col">

            <div
                class="p-5 border-b border-gray-100 bg-gray-50/50 space-y-4 md:space-y-0 md:flex md:items-center justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('orders.create') }}"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> طلب جديد
                    </a>

                    <div class="hidden lg:flex bg-white border border-gray-200 rounded-xl p-1 shadow-sm">
                        <template x-for="filter in filters" :key="filter.value">
                            <button @click="currentFilter = filter.value"
                                :class="currentFilter === filter.value ? 'bg-gray-100 text-gray-900 font-bold' :
                                    'text-gray-500 hover:bg-gray-50'"
                                class="px-4 py-1.5 rounded-lg text-sm transition-colors" x-text="filter.label"></button>
                        </template>
                    </div>
                </div>

                <div class="relative w-full md:w-96 group">
                    <div
                        class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none transition-colors group-focus-within:text-activeGold">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-activeGold"></i>
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="ابحث برقم الطلب، اسم العميل، أو الهاتف..."
                        class="block w-full pr-11 pl-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold bg-white shadow-sm placeholder-gray-400 transition-all">

                    <button x-show="searchQuery.length > 0" @click="searchQuery = ''"
                        class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 65vh;">
                <table class="w-full text-right text-sm">
                    <thead
                        class="bg-gray-100 text-gray-600 font-bold sticky top-0 z-10 shadow-sm backdrop-blur-md bg-opacity-90">
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
                                x-show="matchesSearch('{{ $order->order_number }}', '{{ $order->customer_name }}', '{{ $order->phone_1 }}') && matchesFilter(liveStatus)"
                                x-transition>
                                <td class="px-6 py-4 text-gray-400 font-medium">{{ $loop->iteration }}</td>

                                <td class="px-6 py-4 font-bold text-gray-900">
                                    <a href="{{ route('orders.show', $order->id) }}"
                                        class="hover:text-activeGold transition-colors flex items-center gap-2">
                                        <i class="fa-solid fa-hashtag text-gray-300 text-xs"></i>{{ $order->order_number }}
                                    </a>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold shrink-0">
                                            {{ mb_substr($order->customer_name, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800">{{ $order->customer_name }}</span>
                                            <span class="text-xs text-gray-500 font-mono tracking-wider"
                                                dir="ltr">{{ $order->phone_1 }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4" x-data="{
                                    editMode: false,
                                    currentStatus: liveStatus,
                                    isUpdating: false
                                }">

                                    <div x-show="!editMode" @click="editMode = true"
                                        class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border transition-all hover:shadow-md hover:scale-105"
                                        :class="getStatusClass(currentStatus)" title="انقر لتعديل الحالة">
                                        <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                        <span x-text="getStatusLabel(currentStatus)"></span>
                                        <i class="fa-solid fa-pen text-[10px] opacity-50 ml-1"></i>
                                    </div>

                                    <div x-show="editMode" @click.away="editMode = false" x-transition
                                        class="relative flex items-center gap-2">
                                        <select x-model="currentStatus"
                                            @change="
                                                liveStatus = currentStatus;
                                                updateStatus({{ $order->id }}, currentStatus);
                                                editMode = false;
                                            "
                                            class="border border-activeGold ring-2 ring-activeGold/20 rounded-lg p-1.5 text-xs font-bold bg-white text-gray-700 shadow-lg cursor-pointer focus:outline-none pr-8">
                                            @foreach ($statusMap as $key => $status)
                                                <option value="{{ $key }}">
                                                    {{ $status['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button @click="editMode = false"
                                            class="text-gray-400 hover:text-red-500 transition">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-black text-gray-900">
                                    {{ number_format($order->total_price, 0) }} <span
                                        class="text-xs text-gray-400 font-normal">ج.م</span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col text-xs font-bold gap-1">
                                        <div
                                            class="flex justify-between items-center bg-emerald-50 text-emerald-700 px-2 py-1 rounded">
                                            <span>مدفوع:</span>
                                            <span>{{ number_format($order->getTotalPaid(), 0) }}</span>
                                        </div>
                                        @if ($order->getRemainingAmount() > 0)
                                            <div
                                                class="flex justify-between items-center bg-rose-50 text-rose-700 px-2 py-1 rounded">
                                                <span>متبقي:</span>
                                                <span>{{ number_format($order->getRemainingAmount(), 0) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('orders.show', $order->id) }}"
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-blue-50 hover:text-blue-600 flex items-center justify-center transition-colors"
                                            title="عرض">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-purple-50 hover:text-purple-600 flex items-center justify-center transition-colors"
                                            title="طباعة">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                            class="m-0 inline" onsubmit="return confirmDeleteOrder();">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition-colors"
                                                title="حذف الطلب">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fa-solid fa-folder-open text-6xl mb-4 opacity-50"></i>
                                        <p class="text-lg font-medium text-gray-500">لا توجد طلبات مسجلة حالياً</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div x-show="searchQuery !== '' && document.querySelectorAll('tbody tr[style*=\'display: none\']').length === {{ count($orders) }}"
                    style="display: none;" class="py-10 text-center">
                    <p class="text-gray-500">لا توجد نتائج تطابق بحثك عن: <strong x-text="searchQuery"
                            class="text-gray-800"></strong></p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-400 font-medium">
                نهاية قائمة الطلبات
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // تهيئة تطبيق Alpine.js للبحث الذكي وتعديل الحالات
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

                initData() {
                    // أي تهيئة إضافية يمكن وضعها هنا
                },

                // دالة الفلترة بالبحث النصي
                matchesSearch(orderNum, customerName, phone) {
                    if (this.searchQuery === '') return true;
                    const q = this.searchQuery.toLowerCase();
                    return orderNum.toLowerCase().includes(q) ||
                        customerName.toLowerCase().includes(q) ||
                        phone.toLowerCase().includes(q);
                },

                // دالة الفلترة بالحالة
                matchesFilter(status) {
                    if (this.currentFilter === 'all') return true;
                    // هنا نقوم بمقارنة الحالة الحالية (سواء الأصلية أو التي تم تعديلها عبر alpine)
                    return status === this.currentFilter;
                },

                // خريطة ألوان الحالات (للتحديث المباشر)
                getStatusClass(status) {

                    const classes = {
                        @foreach ($statusMap as $key => $status)
                            '{{ $key }}': '{{ $status['class'] }}',
                        @endforeach
                    };

                    return classes[status] || 'bg-gray-50 text-gray-700 border-gray-200';
                },

                // خريطة أسماء الحالات بالعربية
                getStatusLabel(status) {

                    const labels = {
                        @foreach ($statusMap as $key => $status)
                            '{{ $key }}': '{{ $status['label'] }}',
                        @endforeach
                    };

                    return labels[status] || status;
                },

                // دالة إرسال التحديث للسيرفر (AJAX)
                // دالة إرسال التحديث للسيرفر (AJAX)
                async updateStatus(orderId, newStatus) {
                    if (this.isUpdating) return; // منع التكرار
                    this.isUpdating = true;

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

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'فشل التحديث');
                        }

                        // يمكنك استبدال هذا بإشعار Toast حقيقي
                        console.log(data.message);

                    } catch (error) {
                        console.error('Error updating status:', error);
                        alert('حدث خطأ أثناء تحديث الحالة: ' + error.message);

                        // إرجاع الحالة إلى ما كانت عليه في حالة الفشل
                        // this.currentStatus = this.liveStatus; (اختياري)
                    } finally {
                        this.isUpdating = false;
                    }
                }
            }));
        });
    </script>
    <script>
        function confirmDeleteOrder() {
            return confirm(
                `⚠️ تحذير

سيتم حذف هذا الطلب.

وسيتم حذف جميع الدفعات المرتبطة به.

وقد يؤثر ذلك على:
• التقارير المالية.
• الأرباح.
• الإحصائيات.
• التحليلات المالية.

هل أنت متأكد من متابعة عملية الحذف؟`
            );
        }
    </script>
@endpush
