@extends('layouts.app')

@section('title', 'مركز عمليات Turbo')

@section('content')
    <div x-data="turboApp()" x-init="initData()" class="space-y-6 max-w-7xl mx-auto pb-12 transition-all">

        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#31006f] via-[#430099] to-[#111827] text-white p-6 md:p-8 shadow-xl border border-purple-900/40">
            <div class="absolute -right-10 -top-10 w-60 h-60 bg-purple-500/20 rounded-full blur-3xl pointer-events-none">
            </div>
            <div class="absolute right-1/3 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none">
            </div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-purple-200 text-xs font-semibold backdrop-blur-md border border-white/10">
                        <i class="fa-solid fa-truck-fast text-xs"></i>
                        <span data-ar="مركز عمليات الشحن والطلبات" data-en="Shipping & Orders Ops Center">مركز عمليات الشحن
                            والطلبات</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight flex items-center gap-3">
                        <span data-ar="مركز عمليات Turbo" data-en="Turbo Operations Center">مركز عمليات Turbo</span>
                    </h1>
                    <p class="text-purple-100/80 text-sm md:text-base max-w-xl"
                        data-ar="تابع الشحنات والطلبات وتواصل مع فريق دعم شركة Turbo لحل أي معوقات مباشرة."
                        data-en="Track shipments, orders, and communicate directly with Turbo support team to resolve issues.">
                        تابع الشحنات والطلبات وتواصل مع فريق دعم شركة Turbo لحل أي معوقات مباشرة.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    <button @click="fetchData(true)" :disabled="refreshing"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur-md border border-white/15 active:scale-95 disabled:opacity-50">
                        <i class="fa-solid fa-rotate text-xs" :class="{ 'fa-spin': refreshing }"></i>
                        <span data-ar="تحديث البيانات" data-en="Refresh Data">تحديث البيانات</span>
                    </button>

                    <button @click="instructionsModalOpen = true"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur-md border border-white/15 active:scale-95">
                        <i class="fa-solid fa-circle-info text-xs"></i>
                        <span data-ar="الإرشادات" data-en="Instructions">الإرشادات</span>
                    </button>

                    <button @click="createModalOpen = true"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/20 transition active:scale-95">
                        <i class="fa-solid fa-plus"></i>
                        <span data-ar="فتح طلب دعم" data-en="New Support Turbo">فتح طلب دعم</span>
                    </button>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-purple-200/70">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span data-ar="ربط خارجي نشط مع Turbo API" data-en="Active Sync with Turbo API">ربط خارجي نشط مع Turbo
                        API</span>
                </div>
                <div>
                    <span data-ar="آخر مزامنة: " data-en="Last Sync: ">آخر مزامنة: </span>
                    <span x-text="lastSyncTime">الآن</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider" data-ar="إجمالي الطلبات"
                        data-en="Total Turbo">إجمالي الطلبات</span>
                    <div
                        class="w-10 h-10 rounded-xl bg-purple-50 text-[#31006f] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-gray-900" x-text="statistics.total">0</span>
                    <span class="text-xs text-gray-400" data-ar="تذكرة" data-en="turbo">تذكرة</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                        data-ar="مفتوحة (تحتاج متابعة)" data-en="Open (Needs Action)">مفتوحة (تحتاج متابعة)</span>
                    <div
                        class="w-10 h-10 rounded-xl bg-purple-100 text-[#31006f] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-[#31006f]" x-text="statistics.open">0</span>
                    <span class="text-xs text-purple-600 font-medium" data-ar="تطلب اتخاذ إجراء"
                        data-en="Requires Action">تطلب اتخاذ إجراء</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                        data-ar="قيد المتابعة مع Turbo" data-en="In Progress">قيد المتابعة مع Turbo</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-amber-600" x-text="statistics.pending">0</span>
                    <span class="text-xs text-amber-600 font-medium" data-ar="معالجة جارية" data-en="Under Review">معالجة
                        جارية</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider" data-ar="مغلقة / تم حلها"
                        data-en="Resolved / Closed">مغلقة / تم حلها</span>
                    <div
                        class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-[#10B981] fa-circle-check"></i>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-emerald-600" x-text="statistics.closed">0</span>
                    <span class="text-xs text-emerald-600 font-medium" data-ar="مكتملة" data-en="Completed">مكتملة</span>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col md:flex-row items-stretch md:items-center gap-4">
            <div class="relative flex-1">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3 rtl:right-3 rtl:left-auto top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" x-model="search" placeholder="ابحث برقم التذكرة، كود الطلب، أو تفاصيل المشكلة..."
                    data-ar-placeholder="ابحث برقم التذكرة، كود الطلب، أو تفاصيل المشكلة..."
                    data-en-placeholder="Search by Turbo ID, Order Code, or Details..."
                    class="w-full pl-10 rtl:pr-10 rtl:pl-4 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#31006f] focus:bg-white transition">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <select x-model="statusFilter"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#31006f] transition">
                    <option value="" data-ar="كل الحالات" data-en="All Statuses">كل الحالات</option>
                    <template x-for="st in statuses" :key="st.id">
                        <option :value="st.id" x-text="st.name"></option>
                    </template>
                </select>

                <select x-model="categoryFilter"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-[#31006f] transition">
                    <option value="" data-ar="كل أنواع المشاكل" data-en="All Categories">كل أنواع المشاكل</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>

                <button x-show="search || statusFilter || categoryFilter" @click="resetFilters()"
                    class="px-3 py-2.5 text-xs text-red-600 hover:bg-red-50 rounded-xl font-medium transition"
                    data-ar="إعادة ضبط" data-en="Reset">
                    إعادة ضبط
                </button>
            </div>
        </div>

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
                            <div class="flex justify-between items-center pt-2">
                                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                                <div class="h-8 bg-gray-200 rounded-lg w-1/4"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!loading && filteredTurbo.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="turbo in filteredTurbo" :key="turbo.id">
                        <div
                            class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                    <span
                                        class="font-mono text-xs font-bold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md"
                                        x-text="'#TK-' + turbo.id"></span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="getStatusBadgeClass(turbo.status_id)"
                                        x-text="getStatusName(turbo.status_id)">
                                    </span>
                                </div>

                                <div>
                                    <h3 class="font-bold text-gray-900 line-clamp-1 group-hover:text-[#31006f] transition"
                                        x-text="turbo.title || getCategoryName(turbo.category_id)"></h3>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2" x-text="turbo.content"></p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-[#31006f] font-semibold rounded-lg">
                                        <i class="fa-solid fa-box text-[10px]"></i>
                                        <span x-text="turbo.order_code || 'بدون طلب'"></span>
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg">
                                        <i class="fa-solid fa-tag text-[10px]"></i>
                                        <span x-text="getCategoryName(turbo.category_id)"></span>
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                <span class="text-gray-400" x-text="turbo.updated_at_human || 'مؤخراً'"></span>
                                <button @click="openDrawer(turbo)"
                                    class="inline-flex items-center gap-1.5 text-[#31006f] font-bold hover:underline transition">
                                    <span data-ar="عرض التفاصيل والمحادثة" data-en="View Details">عرض التفاصيل
                                        والمحادثة</span>
                                    <i class="fa-solid fa-arrow-left rtl:rotate-0 rotate-180 text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!loading && filteredTurbo.length === 0">
                <div class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-sm">
                    <div
                        class="w-16 h-16 bg-purple-50 text-[#31006f] rounded-2xl flex items-center justify-center mx-auto text-2xl mb-4">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900" data-ar="لا توجد طلبات دعم حالياً"
                        data-en="No Support Turbo Found">لا توجد طلبات دعم حالياً</h3>
                    <p class="text-gray-500 text-sm mt-1 max-w-md mx-auto"
                        data-ar="لم يتم العثور على أي طلبات مطابقة للبحث أو أنه لم يتم فتح تذاكر مع شركة Turbo بعد."
                        data-en="No turbo match your filter criteria or no support turbo have been opened with Turbo yet.">
                        لم يتم العثور على أي طلبات مطابقة للبحث أو أنه لم يتم فتح تذاكر مع شركة Turbo بعد.</p>
                    <button @click="createModalOpen = true"
                        class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-[#31006f] text-white rounded-xl font-semibold text-sm hover:bg-purple-900 transition">
                        <i class="fa-solid fa-plus"></i>
                        <span data-ar="فتح طلب دعم جديد" data-en="Open New Support Turbo">فتح طلب دعم جديد</span>
                    </button>
                </div>
            </template>
        </div>
     

    </div>

    <script>
        function turboApp() {
            return {
                loading: true,
                refreshing: false,
                submitting: false,
                sendingMsg: false,
                createModalOpen: false,
                detailsDrawerOpen: false,
                instructionsModalOpen: false,

                search: '',
                statusFilter: '',
                categoryFilter: '',

                turbo: [],
                categories: [],
                statuses: [],
                statistics: {
                    total: 0,
                    open: 0,
                    pending: 0,
                    closed: 0
                },

                selectedTurbo: null,
                newMessage: '',
                currentRating: 0,
                lastSyncTime: 'الآن',

                newTurbo: {
                    category_id: '',
                    order_code: '',
                    content: ''
                },

                async fetchData(isRefresh = false) {
                    if (isRefresh) this.refreshing = true;
                    else this.loading = true;

                    try {
                        const res = await fetch('/turbo/data', {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        });
                        const data = await res.json();

                        this.turbo = data.turbo || [];
                        this.categories = data.categories || [];
                        this.statuses = data.statuses || [];
                        this.calculateStatistics();

                        const now = new Date();
                        this.lastSyncTime = now.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    } catch (err) {
                        console.error("Error fetching Turbo data:", err);
                    } finally {
                        this.loading = false;
                        this.refreshing = false;
                    }
                },

                initData() {
                    this.fetchData();
                },

                calculateStatistics() {
                    this.statistics.total = this.turbo.length;
                    this.statistics.open = this.turbo.filter(t => t.status_id === 1 || t.status === 'open').length;
                    this.statistics.pending = this.turbo.filter(t => t.status_id === 2 || t.status === 'pending').length;
                    this.statistics.closed = this.turbo.filter(t => t.status_id === 3 || t.status === 'closed').length;
                },

                get filteredTurbo() {
                    return this.turbo.filter(turbo => {
                        const matchSearch = !this.search ||
                            (turbo.id && turbo.id.toString().includes(this.search)) ||
                            (turbo.order_code && turbo.order_code.toLowerCase().includes(this.search
                                .toLowerCase())) ||
                            (turbo.content && turbo.content.toLowerCase().includes(this.search
                        .toLowerCase()));

                        const matchStatus = !this.statusFilter || turbo.status_id == this.statusFilter;
                        const matchCat = !this.categoryFilter || turbo.category_id == this.categoryFilter;

                        return matchSearch && matchStatus && matchCat;
                    });
                },

                resetFilters() {
                    this.search = '';
                    this.statusFilter = '';
                    this.categoryFilter = '';
                },

                getStatusName(id) {
                    const st = this.statuses.find(s => s.id == id);
                    return st ? st.name : 'قيد المتابعة';
                },

                getCategoryName(id) {
                    const cat = this.categories.find(c => c.id == id);
                    return cat ? cat.name : 'عام';
                },

                getStatusBadgeClass(id) {
                    if (id == 1) return 'bg-purple-100 text-[#31006f] border border-purple-200';
                    if (id == 2) return 'bg-amber-100 text-amber-800 border border-amber-200';
                    return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                },

                isClosedStatus(id) {
                    return id == 3 || id == 'closed';
                },

                openDrawer(turbo) {
                    this.selectedTurbo = turbo;
                    this.detailsDrawerOpen = true;
                },

                async submitCreateTurbo() {
                    this.submitting = true;
                    try {
                        const res = await fetch('/turbo/turbo', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify(this.newTurbo)
                        });
                        if (res.ok) {
                            this.createModalOpen = false;
                            this.newTurbo = {
                                category_id: '',
                                order_code: '',
                                content: ''
                            };
                            this.fetchData(true);
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.submitting = false;
                    }
                },

                async sendMessage() {
                    if (!this.newMessage.trim() || !this.selectedTurbo) return;
                    this.sendingMsg = true;

                    try {
                        const res = await fetch(`/turbo/turbo/${this.selectedTurbo.id}/messages`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                message: this.newMessage
                            })
                        });

                        if (res.ok) {
                            if (!this.selectedTurbo.messages) this.selectedTurbo.messages = [];
                            this.selectedTurbo.messages.push({
                                id: Date.now(),
                                message: this.newMessage,
                                is_sender_me: true,
                                created_at: 'الآن'
                            });
                            this.newMessage = '';
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.sendingMsg = false;
                    }
                },

                async rateTurbo(stars) {
                    this.currentRating = stars;
                    await fetch(`/turbo/turbo/${this.selectedTurbo.id}/rate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            rating: stars
                        })
                    });
                }
            };
        }
    </script>
@endsection
