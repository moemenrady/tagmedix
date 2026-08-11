@extends('layouts.app')

@section('title', 'تتبع شحنات Turbo | Sync DB & API')

@section('content')
    <!-- Alpine.js library -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div x-data="turboTrackingApp()" x-init="initData()" class="space-y-6 max-w-7xl mx-auto pb-12 transition-all">

        <!-- Banner Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#31006f] via-[#430099] to-[#111827] text-white p-6 md:p-8 shadow-xl border border-purple-900/40">
            <div class="absolute -right-10 -top-10 w-60 h-60 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-purple-200 text-xs font-semibold backdrop-blur-md border border-white/10">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        <span>مزامنة قاعدة البيانات وحالة السيرفر التفاعلي</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight flex items-center gap-3">
                        <span>متابعة وتتبع شحنات Turbo (DB & API)</span>
                    </h1>
                    <p class="text-purple-100/80 text-sm md:text-base max-w-xl">
                        عرض جميع الشحنات سواء كانت مسجلة محلياً فقط في قاعدة البيانات، أو على سيرفر Turbo فقط، أو متزامنة بين الطرفين.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <button @click="fetchData(true)" :disabled="refreshing"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-semibold transition backdrop-blur-md border border-white/15 active:scale-95 disabled:opacity-50 cursor-pointer">
                        <i class="fa-solid fa-rotate text-xs" :class="{ 'fa-spin': refreshing }"></i>
                        <span>تحديث ومطابقة البيانات</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-purple-200/70">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>اتصال مباشر بنظام Turbo وقاعدة البيانات</span>
                </div>
                <div>
                    <span>آخر مزامنة: </span>
                    <span x-text="lastSyncTime">الآن</span>
                </div>
            </div>
        </div>

        <!-- Notification Alert -->
        <div x-show="alertMessage" x-cloak x-transition
            class="p-4 rounded-xl text-sm font-semibold flex items-center justify-between shadow-sm border"
            :class="alertSuccess ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'">
            <span x-text="alertMessage"></span>
            <button @click="alertMessage = ''" class="text-xs opacity-60 hover:opacity-100 cursor-pointer">&times;</button>
        </div>

        <!-- Filter By Data Source (DB / API / BOTH) -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 space-y-4">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                
                <!-- Search Box -->
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 rtl:right-3 rtl:left-auto top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model="search" placeholder="ابحث برقم الشحنة، كود الطلب، اسم العميل أو الهاتف..."
                        class="w-full pl-10 rtl:pr-10 rtl:pl-4 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#31006f] focus:bg-white transition">
                </div>

                <!-- Status Filter -->
                <div class="flex flex-wrap items-center gap-3">
                    <select x-model="statusFilter"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#31006f] transition">
                        <option value="">كل حالات الشحن</option>
                        <option value="1">قيد الانتظار (Pending)</option>
                        <option value="2">جاري التوصيل (In Transit)</option>
                        <option value="3">تم التسليم (Delivered)</option>
                        <option value="4">ملغي (Canceled)</option>
                    </select>

                    <button x-show="search || statusFilter || sourceFilter" @click="search = ''; statusFilter = ''; sourceFilter = '';"
                        class="px-3 py-2.5 text-xs text-red-600 hover:bg-red-50 rounded-xl font-medium transition cursor-pointer">
                        إعادة ضبط
                    </button>
                </div>
            </div>

            <!-- Source Filter Pills -->
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100">
                <span class="text-xs font-bold text-gray-500 ml-2">مصدر البيانات:</span>

                <button @click="sourceFilter = ''" 
                    :class="sourceFilter === '' ? 'bg-[#31006f] text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <span>الكل</span>
                    <span class="px-1.5 py-0.2 bg-white/20 rounded-md text-[10px]" x-text="turbo.length"></span>
                </button>

                <button @click="sourceFilter = 'db_only'" 
                    :class="sourceFilter === 'db_only' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-database"></i>
                    <span>قاعدة البيانات فقط (DB Only)</span>
                    <span class="px-1.5 py-0.2 bg-blue-200/50 rounded-md text-[10px]" x-text="turbo.filter(i => i.source_type === 'db_only').length"></span>
                </button>

                <button @click="sourceFilter = 'api_only'" 
                    :class="sourceFilter === 'api_only' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-cloud"></i>
                    <span>Turbo API فقط (API Only)</span>
                    <span class="px-1.5 py-0.2 bg-emerald-200/50 rounded-md text-[10px]" x-text="turbo.filter(i => i.source_type === 'api_only').length"></span>
                </button>

                <button @click="sourceFilter = 'both'" 
                    :class="sourceFilter === 'both' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-link"></i>
                    <span>متزامن (DB & API)</span>
                    <span class="px-1.5 py-0.2 bg-indigo-200/50 rounded-md text-[10px]" x-text="turbo.filter(i => i.source_type === 'both').length"></span>
                </button>
            </div>
        </div>

        <!-- Shipments Grid -->
        <div class="relative">
            <template x-if="loading">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="i in 6" :key="i">
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm animate-pulse space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                                <div class="h-6 bg-gray-200 rounded-full w-1/5"></div>
                            </div>
                            <div class="h-5 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-12 bg-gray-100 rounded"></div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!loading && filteredTurbo.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="item in filteredTurbo" :key="item.id">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between group relative">
                            <div class="space-y-3">
                                
                                <!-- Top Bar & Source Badges -->
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                                    <span class="font-mono text-xs font-bold text-gray-700 bg-purple-50 px-2.5 py-1 rounded-md border border-purple-100"
                                        x-text="item.order_code"></span>

                                    <!-- Status Badges for Source Types -->
                                    <div class="flex items-center gap-1">
                                        <!-- DB Only Badge -->
                                        <template x-if="item.source_type === 'db_only'">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1"
                                                title="هذه الشحنة موجودة في قاعدة البيانات المحلية فقط ولم تصدر لـ API بعد">
                                                <i class="fa-solid fa-database text-[10px]"></i> DB Only
                                            </span>
                                        </template>

                                        <!-- API Only Badge -->
                                        <template x-if="item.source_type === 'api_only'">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1"
                                                title="هذه الشحنة جُلبت مباشرة من سيرفر Turbo وتفتقر لسجل محلي">
                                                <i class="fa-solid fa-cloud text-[10px]"></i> Turbo API Only
                                            </span>
                                        </template>

                                        <!-- DB & API Badge -->
                                        <template x-if="item.source_type === 'both'">
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1"
                                                title="الشحنة موجودة في قاعدة البيانات ومربوطة بنجاح مع سيرفر Turbo">
                                                <i class="fa-solid fa-link text-[10px]"></i> DB & API Sync
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#31006f] transition"
                                        x-text="item.receiver"></h3>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="item.phone || 'بدون هاتف'"></p>
                                </div>

                                <!-- Shipping Details -->
                                <div class="grid grid-cols-2 gap-2 pt-2 text-xs bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                    <div>
                                        <span class="text-gray-400 block text-[10px]">كود Turbo:</span>
                                        <span class="font-mono font-bold text-purple-900" x-text="item.tracking_number"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block text-[10px]">مبلغ التحصيل:</span>
                                        <span class="font-bold text-emerald-600" x-text="(item.amount || 0) + ' ج.م'"></span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-gray-400 block text-[10px]">حالة التوصيل:</span>
                                        <span class="font-bold text-purple-900" x-text="item.status_name"></span>
                                    </div>
                                </div>

                                <!-- Actions Bar -->
                                <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                    <button @click="resendRequest(item)" title="إعادة إرسال الشحنة"
                                        class="flex-1 py-1.5 px-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-rotate-right text-[10px]"></i>
                                        <span>إعادة إرسال</span>
                                    </button>
                                    <button @click="cancelRequest(item)" title="إلغاء الشحنة"
                                        class="flex-1 py-1.5 px-2 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-ban text-[10px]"></i>
                                        <span>إلغاء</span>
                                    </button>
                                    <button @click="deleteOrder(item)" title="حذف الشحنة"
                                        class="py-1.5 px-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition cursor-pointer">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                <span class="text-gray-400" x-text="item.updated_at_human"></span>
                                <button @click="openDrawer(item)"
                                    class="inline-flex items-center gap-1.5 text-[#31006f] font-bold hover:underline transition cursor-pointer">
                                    <span>التفاصيل</span>
                                    <i class="fa-solid fa-arrow-left rtl:rotate-0 rotate-180 text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Empty State -->
            <template x-if="!loading && filteredTurbo.length === 0">
                <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
                    <div class="w-16 h-16 bg-purple-50 text-[#31006f] rounded-2xl flex items-center justify-center mx-auto text-2xl mb-4">
                        <i class="fa-solid fa-magnifying-glass-location"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">لا توجد شحنات مطابقة للبحث أو الملاحظات الحالية</h3>
                    <p class="text-gray-500 text-sm mt-1 max-w-md mx-auto">تأكد من عدم وجود خيارات فلترة نشطة تمنع ظهور الشحنات.</p>
                </div>
            </template>
        </div>

        <!-- Details Drawer -->
        <div x-show="detailsDrawerOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden">
            <div @click="detailsDrawerOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

            <div class="fixed inset-y-0 right-0 rtl:right-auto rtl:left-0 max-w-full flex z-10">
                <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between h-full">
                    <div class="p-4 bg-[#31006f] text-white flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base">تفاصيل تتبع الشحنة</h2>
                            <p class="text-xs text-purple-200" x-text="selectedTurbo ? ('كود الطلب: ' + selectedTurbo.order_code) : ''"></p>
                        </div>
                        <button @click="detailsDrawerOpen = false" class="text-white text-xl font-bold hover:text-gray-300 cursor-pointer">&times;</button>
                    </div>

                    <div class="flex-1 p-4 overflow-y-auto space-y-4 text-xs" x-show="selectedTurbo">
                        
                        <!-- Source Notification in Drawer -->
                        <div class="p-3 rounded-xl font-semibold flex items-center gap-2 border"
                            :class="{
                                'bg-blue-50 text-blue-800 border-blue-200': selectedTurbo?.source_type === 'db_only',
                                'bg-emerald-50 text-emerald-800 border-emerald-200': selectedTurbo?.source_type === 'api_only',
                                'bg-indigo-50 text-indigo-800 border-indigo-200': selectedTurbo?.source_type === 'both'
                            }">
                            <i class="fa-solid" :class="{
                                'fa-database': selectedTurbo?.source_type === 'db_only',
                                'fa-cloud': selectedTurbo?.source_type === 'api_only',
                                'fa-link': selectedTurbo?.source_type === 'both'
                            }"></i>
                            <span x-text="
                                selectedTurbo?.source_type === 'db_only' ? 'البيانات مسجلة في قاعدة البيانات فقط' :
                                (selectedTurbo?.source_type === 'api_only' ? 'البيانات مجلوبة مباشرة من سيرفر Turbo' : 'البيانات متزامنة بين قاعدة البيانات وسيرفر Turbo')
                            "></span>
                        </div>

                        <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 space-y-2">
                            <h4 class="font-bold text-gray-800 border-b pb-1">تفاصيل المستلم:</h4>
                            <div class="flex justify-between text-gray-700">
                                <span>العميل:</span>
                                <span class="font-semibold text-gray-900" x-text="selectedTurbo?.receiver"></span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>الهاتف:</span>
                                <span class="font-semibold text-gray-900" x-text="selectedTurbo?.phone"></span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>المحافظة والعنوان:</span>
                                <span class="font-semibold text-gray-900 text-left max-w-[200px] truncate" x-text="(selectedTurbo?.government || '') + ' - ' + (selectedTurbo?.address || '')"></span>
                            </div>
                            <div class="flex justify-between text-gray-700 pt-1 border-t">
                                <span>مبلغ التحصيل:</span>
                                <span class="font-extrabold text-emerald-600 text-sm" x-text="(selectedTurbo?.amount || 0) + ' ج.م'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t">
                            <button @click="resendRequest(selectedTurbo)" class="flex-1 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold rounded-xl text-center cursor-pointer">
                                🔄 إعادة إرسال الشحنة
                            </button>
                            <button @click="cancelRequest(selectedTurbo)" class="flex-1 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-xl text-center cursor-pointer">
                                🚫 إلغاء الشحنة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function turboTrackingApp() {
            return {
                loading: true,
                refreshing: false,
                detailsDrawerOpen: false,

                alertMessage: '',
                alertSuccess: true,

                search: '',
                statusFilter: '',
                sourceFilter: '', // db_only | api_only | both | ''

                turbo: [],
                selectedTurbo: null,
                lastSyncTime: 'الآن',

                async fetchData(isRefresh = false) {
                    if (isRefresh) this.refreshing = true;
                    else this.loading = true;

                    try {
                        const res = await fetch('/turbo/data', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const data = await res.json();

                        if (data.success) {
                            this.turbo = data.turbo || [];
                            const now = new Date();
                            this.lastSyncTime = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        } else {
                            this.showAlert(data.message || 'حدث خطأ في تحميل البيانات', false);
                        }
                    } catch (err) {
                        console.error("Error fetching Turbo data:", err);
                        this.showAlert('حدث خطأ أثناء جلب البيانات من الخادم', false);
                    } finally {
                        this.loading = false;
                        this.refreshing = false;
                    }
                },

                initData() {
                    this.fetchData();
                },

                showAlert(msg, isSuccess = true) {
                    this.alertMessage = msg;
                    this.alertSuccess = isSuccess;
                    setTimeout(() => { this.alertMessage = ''; }, 4500);
                },

                get filteredTurbo() {
                    return this.turbo.filter(item => {
                        const matchSearch = !this.search ||
                            (item.id && item.id.toString().includes(this.search)) ||
                            (item.order_code && item.order_code.toLowerCase().includes(this.search.toLowerCase())) ||
                            (item.receiver && item.receiver.toLowerCase().includes(this.search.toLowerCase())) ||
                            (item.phone && item.phone.includes(this.search)) ||
                            (item.tracking_number && item.tracking_number.toLowerCase().includes(this.search.toLowerCase()));

                        const matchStatus = !this.statusFilter || item.status_id == this.statusFilter;
                        const matchSource = !this.sourceFilter || item.source_type === this.sourceFilter;

                        return matchSearch && matchStatus && matchSource;
                    });
                },

                openDrawer(item) {
                    this.selectedTurbo = item;
                    this.detailsDrawerOpen = true;
                },

                async resendRequest(item) {
                    try {
                        const res = await fetch('/turbo/resend-request', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ id: item.id })
                        });
                        const data = await res.json();
                        this.showAlert(data.message || 'تم إعادة الإرسال بنجاح', data.success);
                        this.fetchData(true);
                    } catch (e) {
                        this.showAlert('تعذر إعادة الإرسال', false);
                    }
                },

                async cancelRequest(item) {
                    if (!confirm('هل أنت تأكد من إلغاء الشحنة؟')) return;
                    try {
                        const res = await fetch('/turbo/cancel-request', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ id: item.id })
                        });
                        const data = await res.json();
                        this.showAlert(data.message || 'تم الإلغاء بنجاح', data.success);
                        this.fetchData(true);
                    } catch (e) {
                        this.showAlert('تعذر إلغاء الشحنة', false);
                    }
                },

                async deleteOrder(item) {
                    if (!confirm('هل أنت تأكد من حذف الشحنة نهائياً؟')) return;
                    try {
                        const res = await fetch('/turbo/delete-order', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ remote_order_id: item.order_code || item.id })
                        });
                        const data = await res.json();
                        this.showAlert(data.message || 'تم الحذف بنجاح', data.success);
                        this.fetchData(true);
                    } catch (e) {
                        this.showAlert('تعذر حذف الشحنة', false);
                    }
                }
            };
        }
    </script>
@endsection