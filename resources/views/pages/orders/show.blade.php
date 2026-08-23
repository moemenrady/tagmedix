@extends('layouts.app')

@section('title', 'تفاصيل الطلب | Order Details')

@section('content')
    <!-- تضمين Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @php
        // تجهيز البيانات بشكل آمن لـ JavaScript لتجنب مشكلة اختفاء البيانات
        $customer = $order->customer;
        $address = $order->address;

        $initialData = [
            'orderId' => $order->id,
            'totalPrice' => $order->total_price,
            'paidAmount' => (float) $order->getTotalPaid(),
            'remainingAmount' => (float) $order->getRemainingAmount(),
            'status' => $order->status,
            'customer' => [
                'name' => $customer?->name ?? $order->customer_name ?? 'غير محدد',
                'phone1' => $customer?->phone_1 ?? $order->phone_1 ?? '',
                'phone2' => $customer?->phone_2 ?? $order->phone_2 ?? '',
                'gov' => $address?->governorate ?? $order->governorate ?? 'مصر',
                'address' => $address?->full_address ?? $order->full_address ?? '',
                'insta' => $customer?->instagram_profile ?? $order->instagram_profile ?? ''
            ]
        ];
    @endphp

    <div class="font-sans antialiased text-gray-900 pb-12 space-y-6" x-data="orderDashboard(@js($initialData))">
        
        {{-- إشعار التحديث الذكي --}}
        <div x-show="notification.show" x-transition.opacity.duration.300ms x-cloak
            class="fixed top-5 left-1/2 transform -translate-x-1/2 z-50 bg-gray-900/95 backdrop-blur-sm text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 border"
            :class="notification.type === 'success' ? 'border-green-500' : 'border-red-500'">
            <i class="fa-solid text-lg" :class="notification.type === 'success' ? 'fa-circle-check text-green-400' : 'fa-triangle-exclamation text-red-400'"></i>
            <span x-text="notification.message" class="text-sm font-bold"></span>
        </div>

        {{-- شريط العنوان والإجراءات --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col md:flex-row items-center justify-between gap-4 transition-all hover:shadow-md">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <a href="{{ route('orders.index') }}" class="w-10 h-10 shrink-0 rounded-xl bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-900 transition-all rtl:rotate-0 ltr:rotate-180">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-gray-800 tracking-tight">{{ $order->order_number }}</h1>
                        
                        {{-- تعديل الحالة --}}
                        <div class="relative" @click.away="statusOpen = false">
                            <button @click="statusOpen = !statusOpen" class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-bold border transition-all hover:shadow-md cursor-pointer" :class="currentStatusObj.color">
                                <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                <span x-text="currentStatusObj.labelAr"></span>
                                <i class="fa-solid fa-chevron-down text-[10px] ml-1 opacity-70"></i>
                            </button>
                            <div x-show="statusOpen" x-transition x-cloak class="absolute top-full rtl:right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-30 overflow-hidden">
                                <template x-for="st in statuses" :key="st.value">
                                    <button @click="updateStatus(st.value)" class="w-full text-start px-4 py-2.5 text-xs font-bold hover:bg-gray-50 transition-colors border-b border-gray-50 flex items-center gap-2" :class="status === st.value ? 'bg-amber-50 text-amber-700' : 'text-gray-600'">
                                        <div class="w-2 h-2 rounded-full" :class="st.color.split(' ')[0]"></div>
                                        <span x-text="st.labelAr"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                        <i class="fa-regular fa-clock"></i>
                        <span>تاريخ الطلب: {{ $order->created_at->format('Y-m-d h:i A') }}</span>
                    </p>
                </div>
            </div>
            <div class="flex w-full md:w-auto gap-2">
                <button class="w-full md:w-auto bg-gray-50 hover:bg-gray-100 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 border border-gray-200 shadow-sm">
                    <i class="fa-solid fa-print"></i> <span>طباعة الفاتورة</span>
                </button>
            </div>
        </div>

        {{-- شبكة المعلومات --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- 1. كارت بيانات العميل --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-user-tie text-lg"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">بيانات العميل</h3>
                    </div>
                    <button @click="editMode.customer = !editMode.customer" class="text-gray-400 hover:text-blue-600 transition p-2 bg-gray-50 rounded-lg">
                        <i class="fa-solid" :class="editMode.customer ? 'fa-xmark text-red-500' : 'fa-pen'"></i>
                    </button>
                </div>

                {{-- وضع العرض --}}
                <div x-show="!editMode.customer" class="space-y-4">
                    <div>
                        <p class="text-[11px] text-gray-400 font-bold mb-1">الاسم</p>
                        <p class="text-sm font-black text-gray-800" x-text="customer.name"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                            <p class="text-[10px] text-gray-400 font-bold mb-1">الهاتف الأساسي</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-gray-700 font-mono" x-text="customer.phone1" dir="ltr"></p>
                                <a x-show="customer.phone1" :href="'https://wa.me/' + customer.phone1.replace(/\D/g, '')" target="_blank" class="text-green-500 hover:text-green-600"><i class="fa-brands fa-whatsapp text-lg"></i></a>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100" x-show="customer.phone2">
                            <p class="text-[10px] text-gray-400 font-bold mb-1">الهاتف البديل</p>
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-gray-700 font-mono" x-text="customer.phone2" dir="ltr"></p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 items-start bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <i class="fa-solid fa-location-dot mt-0.5 text-gray-400"></i>
                        <div>
                            <p class="text-[11px] text-gray-400 font-bold mb-0.5">العنوان</p>
                            <p class="text-xs font-bold text-gray-700 leading-relaxed">
                                <span x-text="customer.gov" class="text-blue-600"></span> - <span x-text="customer.address"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- وضع التعديل --}}
                <div x-show="editMode.customer" x-cloak class="space-y-3">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500">الاسم</label>
                        <input type="text" x-model="customer.name" class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500">هاتف 1</label>
                            <input type="text" x-model="customer.phone1" class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-500">هاتف 2</label>
                            <input type="text" x-model="customer.phone2" class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-1">
                            <label class="text-[10px] font-bold text-gray-500">المحافظة</label>
                            <input type="text" x-model="customer.gov" class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500">
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] font-bold text-gray-500">العنوان التفصيلي</label>
                            <input type="text" x-model="customer.address" class="w-full text-sm border-gray-200 rounded-lg focus:ring-blue-500">
                        </div>
                    </div>
                    <button @click="saveCustomerInfo()" class="w-full bg-blue-600 text-white rounded-lg py-2.5 text-sm font-bold hover:bg-blue-700 transition shadow-md flex items-center justify-center gap-2 mt-2">
                        <i class="fa-solid fa-floppy-disk"></i> <span>حفظ التعديلات</span>
                    </button>
                </div>
            </div>

            {{-- 2. كارت الحسابات والمالية --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-wallet text-lg"></i>
                            </div>
                            <h3 class="font-bold text-gray-800">الملخص المالي</h3>
                        </div>
                        <button @click="editMode.financials = !editMode.financials" class="text-gray-400 hover:text-emerald-600 transition p-2 bg-gray-50 rounded-lg">
                            <i class="fa-solid" :class="editMode.financials ? 'fa-xmark text-red-500' : 'fa-pen'"></i>
                        </button>
                    </div>

                    <div class="space-y-3" x-show="!editMode.financials">
                        <div class="flex justify-between items-center p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-xs font-bold text-gray-500">إجمالي الفاتورة</span>
                            <span class="text-lg font-black text-gray-900 font-mono" x-text="formatMoney(totalPrice) + ' ج.م'"></span>
                        </div>
                        <div class="flex justify-between items-center p-3.5 bg-green-50 rounded-xl border border-green-200">
                            <span class="text-xs font-bold text-green-700"><i class="fa-solid fa-money-bill-wave ml-1"></i> إجمالي المدفوع</span>
                            <span class="text-lg font-black text-green-700 font-mono" x-text="formatMoney(paidAmount) + ' ج.م'"></span>
                        </div>
                    </div>

                    {{-- وضع تعديل الماليات --}}
                    <div x-show="editMode.financials" x-cloak class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <div>
                            <label class="text-[10px] font-bold text-gray-500">تعديل الإجمالي المدفوع (جنيه)</label>
                            <input type="number" x-model="paidAmountEdit" class="w-full text-sm border-gray-200 rounded-lg focus:ring-emerald-500 mt-1">
                        </div>
                        <button @click="saveFinancials()" class="w-full bg-emerald-600 text-white rounded-lg py-2 text-sm font-bold hover:bg-emerald-700 transition">
                            حفظ المدفوعات
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex justify-between items-center p-4 rounded-xl border transition-colors" :class="remainingAmount > 0 ? 'bg-rose-50 border-rose-200' : 'bg-gray-50 border-gray-200'">
                    <span class="text-sm font-black" :class="remainingAmount > 0 ? 'text-rose-700' : 'text-gray-500'">المتبقي للتحصيل</span>
                    <span class="text-xl font-black font-mono" :class="remainingAmount > 0 ? 'text-rose-700' : 'text-gray-900'" x-text="formatMoney(remainingAmount) + ' ج.م'"></span>
                </div>
            </div>

            {{-- 3. معلومات الطلب --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fa-solid fa-circle-info text-lg"></i>
                    </div>
                    <h3 class="font-bold text-gray-800">معلومات الطلب</h3>
                </div>
                <div class="space-y-6">
                    <div>
                        <p class="text-xs text-gray-400 font-bold mb-2">مصدر الطلب</p>
                        @php
                            $sourceConfig = [
                                'new' => ['color' => 'bg-amber-100 text-amber-800 border-amber-200', 'ar' => 'طلب جديد / تفصيل'],
                                'from_stock' => ['color' => 'bg-blue-100 text-blue-800 border-blue-200', 'ar' => 'من المخزون مباشر'],
                                'replacement' => ['color' => 'bg-purple-100 text-purple-800 border-purple-200', 'ar' => 'استبدال'],
                            ];
                            $config = $sourceConfig[$order->source] ?? ['color' => 'bg-gray-100 text-gray-800', 'ar' => $order->source];
                        @endphp
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold border {{ $config['color'] }}">{{ $config['ar'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                <i class="fa-solid fa-boxes-stacked text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-gray-600">إجمالي القطع</span>
                        </div>
                        <span class="text-2xl font-black text-gray-900">{{ $order->items->sum('quantity') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- محتويات الطلب (المنتجات) --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-900 text-white flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-shirt text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">محتويات الطلب</h3>
                        <p class="text-xs text-gray-400">اضغط على أي منتج للتعديل أو عرض التفاصيل</p>
                    </div>
                </div>
            </div>

            <div class="p-4 space-y-3">
                @forelse ($order->items as $item)
                    <div x-data="{
                        expanded: false,
                        isEditing: false,
                        itemData: {
                            id: {{ $item->id }},
                            productName: '{{ addslashes($item->product_name ?? 'منتج') }}',
                            qty: {{ $item->quantity }},
                            price: {{ $item->price }},
                            notes: '{{ addslashes($item->notes ?? '') }}'
                        }
                    }" class="bg-white border border-gray-200 rounded-xl shadow-sm transition-all duration-300 hover:border-gray-400">
                        
                        {{-- وضع القراءة (السطر) --}}
                        <div @click="expanded = !expanded" class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer hover:bg-gray-50/50 transition">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 font-bold shrink-0">
                                    {{ mb_substr($item->product_name ?? 'م', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 mb-1" x-text="itemData.productName"></p>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($item->type)<span class="bg-violet-50 text-violet-700 px-2 py-0.5 rounded text-[10px] font-bold border border-violet-100">{{ $item->type->name }}</span>@endif
                                        @if ($item->style)<span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold">{{ $item->style->name }}</span>@endif
                                        @if ($item->color)<span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded text-[10px] font-bold">{{ $item->color->name }}</span>@endif
                                        @if ($item->size)<span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold border border-blue-100">{{ $item->size->name }}</span>@endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between md:justify-end gap-6 border-t md:border-0 pt-3 md:pt-0">
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold">الكمية</p>
                                    <p class="font-black text-gray-800" x-text="itemData.qty"></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold">سعر الوحدة</p>
                                    <p class="font-bold text-gray-600 font-mono" x-text="formatMoney(itemData.price)"></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold">الإجمالي</p>
                                    <p class="font-black text-green-600 font-mono text-lg" x-text="formatMoney(itemData.qty * itemData.price)"></p>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="expanded ? 'rotate-180' : ''"></i>
                                </div>
                            </div>
                        </div>

                        {{-- التفاصيل والتعديل --}}
                        <div x-show="expanded" x-collapse class="border-t border-gray-100 bg-gray-50/50">
                            
                            <div x-show="!isEditing" class="p-5 flex justify-between items-start">
                                <div>
                                    <p class="text-xs text-gray-400 font-bold mb-1">ملاحظات التصنيع</p>
                                    <p class="text-sm text-gray-800" x-text="itemData.notes || 'لا توجد ملاحظات.'"></p>
                                </div>
                                <button @click="isEditing = true" class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-lg hover:bg-gray-800 transition flex items-center gap-2">
                                    <i class="fa-solid fa-pen-to-square"></i> تعديل المنتج
                                </button>
                            </div>

                            <div x-show="isEditing" class="p-5 space-y-4 bg-white m-3 rounded-xl border border-gray-200">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-3">
                                        <label class="text-[10px] font-bold text-gray-500">اسم المنتج</label>
                                        <input type="text" x-model="itemData.productName" class="w-full text-sm border-gray-200 rounded-lg focus:ring-gray-900">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-gray-500">الكمية</label>
                                        <input type="number" x-model="itemData.qty" class="w-full text-sm border-gray-200 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-gray-500">السعر</label>
                                        <input type="number" step="0.01" x-model="itemData.price" class="w-full text-sm border-gray-200 rounded-lg">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-500">ملاحظات</label>
                                    <textarea x-model="itemData.notes" rows="2" class="w-full text-sm border-gray-200 rounded-lg"></textarea>
                                </div>
                                <div class="flex justify-end gap-2 mt-3">
                                    <button @click="isEditing = false" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-300">إلغاء</button>
                                    <button @click="updateItem(itemData); isEditing = false" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700">حفظ المنتج</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        <i class="fa-solid fa-box-open text-4xl mb-3 opacity-20"></i>
                        <p>لا توجد منتجات مسجلة في هذا الطلب.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('orderDashboard', (initData) => ({
            orderId: initData.orderId,
            totalPrice: initData.totalPrice,
            paidAmount: initData.paidAmount,
            remainingAmount: initData.remainingAmount,
            status: initData.status,
            customer: initData.customer,
            
            statusOpen: false,
            editMode: {
                customer: false,
                financials: false,
            },
            paidAmountEdit: initData.paidAmount,

            statuses: [
                { value: 'new', labelAr: 'جديد', color: 'bg-blue-100 text-blue-800' },
                { value: 'confirmed', labelAr: 'مؤكد', color: 'bg-indigo-100 text-indigo-800' },
                { value: 'cutting', labelAr: 'قص', color: 'bg-purple-100 text-purple-800' },
                { value: 'sewing', labelAr: 'خياطة', color: 'bg-pink-100 text-pink-800' },
                { value: 'finishing', labelAr: 'تشطيب', color: 'bg-orange-100 text-orange-800' },
                { value: 'ready-to-ship', labelAr: 'جاهز للشحن', color: 'bg-teal-100 text-teal-800' },
                { value: 'shipped', labelAr: 'تم الشحن', color: 'bg-amber-100 text-amber-800' },
                { value: 'delivered', labelAr: 'تم التسليم', color: 'bg-green-100 text-green-800' },
                { value: 'canceled', labelAr: 'ملغي', color: 'bg-red-100 text-red-800' }
            ],

            notification: { show: false, message: '', type: 'success' },

            get currentStatusObj() {
                return this.statuses.find(s => s.value === this.status) || { labelAr: this.status, color: 'bg-gray-100 text-gray-800' };
            },

            formatMoney(value) {
                return Number(value).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            },

            showToast(msg, type = 'success') {
                this.notification.message = msg;
                this.notification.type = type;
                this.notification.show = true;
                setTimeout(() => this.notification.show = false, 3000);
            },

            async apiCall(payload) {
                try {
                    const response = await fetch(`/orders/${this.orderId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'حدث خطأ أثناء الحفظ');
                    
                    this.showToast('تم حفظ التعديلات بنجاح!', 'success');

                    // تحديث الإجماليات في حال عادت من الباك إند
                    if (data.order) {
                        this.totalPrice = data.order.total_price;
                        this.paidAmount = data.paid_amount ?? this.paidAmount; 
                        this.remainingAmount = data.order.remaining_amount;
                    }
                } catch (error) {
                    this.showToast(error.message, 'error');
                }
            },

            updateStatus(newStatus) {
                this.status = newStatus;
                this.statusOpen = false;
                this.apiCall({ status: newStatus });
            },

            saveCustomerInfo() {
                this.editMode.customer = false;
                this.apiCall({ customer_info: this.customer });
            },

            saveFinancials() {
                this.editMode.financials = false;
                this.paidAmount = parseFloat(this.paidAmountEdit);
                this.remainingAmount = this.totalPrice - this.paidAmount;
                // إرسال طلب التحديث للكنترولر
                this.apiCall({ update_financials: true, paid_amount: this.paidAmount });
            },

            updateItem(itemData) {
                this.apiCall({ order_item: itemData });
            }
        }));
    });
</script>
@endpush