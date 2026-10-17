@extends('layouts.app')

@section('title', 'إعدادات واستعلام Turbo')

@section('content')
    <!-- Alpine.js library -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div x-data="turboStatusApp()" class="space-y-6 max-w-7xl mx-auto pb-12 transition-all">

        <!-- Banner Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#31006f] via-[#430099] to-[#111827] text-white p-6 md:p-8 shadow-xl border border-purple-900/40">
            <div class="absolute -right-10 -top-10 w-60 h-60 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-purple-200 text-xs font-semibold backdrop-blur-md border border-white/10">
                        <i class="fa-solid fa-bolt text-xs text-yellow-300"></i>
                        <span>Turbo API Integration</span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight flex items-center gap-3">
                        <span>إعدادات Turbo والاستعلام المباشر</span>
                    </h1>
                    <p class="text-purple-100/80 text-sm md:text-base max-w-xl">
                        عرض إعدادات الـ Webhook لتلقي التحديثات، بالإضافة إلى الاستعلام المباشر عن حالة الشحنات عبر الـ API.
                    </p>
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

        <!-- Tabs Navigation -->
        <div class="flex items-center gap-2 border-b border-gray-200">
            <button @click="activeTab = 'webhook'" 
                :class="activeTab === 'webhook' ? 'border-[#31006f] text-[#31006f] bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" 
                class="px-6 py-3 font-bold border-b-2 transition rounded-t-lg">
                <i class="fa-solid fa-satellite-dish ml-1"></i> إعدادات الـ Webhook
            </button>
            <button @click="activeTab = 'status'" 
                :class="activeTab === 'status' ? 'border-[#31006f] text-[#31006f] bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'" 
                class="px-6 py-3 font-bold border-b-2 transition rounded-t-lg">
                <i class="fa-solid fa-magnifying-glass-chart ml-1"></i> الاستعلام عن حالة الشحنات
            </button>
        </div>

        <!-- Tab 1: Webhook Configuration Info -->
        <div x-show="activeTab === 'webhook'" x-cloak class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
            <div class="space-y-4 flex-1">
                <div class="flex items-center gap-3 text-[#31006f]">
                    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-satellite-dish"></i>
                    </div>
                    <h3 class="font-bold text-xl">رابط الـ Webhook الخاص بك</h3>
                </div>
                <p class="text-sm text-gray-600 max-w-2xl leading-relaxed">
                    تسمح لك روابط الـ Webhook بتلقي إشعارات فورية عند حدوث أحداث معينة في حسابك (مثل تغيير حالة الشحنة). 
                    <br>
                    أدخل رابط الخادم الخاص بك الموضح أدناه في <strong>لوحة تحكم Turbo</strong> للبدء في تلقي التحديثات التلقائية ومزامنة البيانات بشكل لحظي.
                </p>
            </div>
            
            <div class="w-full lg:w-auto shrink-0 bg-gray-50 p-5 rounded-xl border border-gray-200 space-y-4">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-500">رابط الخادم (الـ Webhook URL):</label>
                    <div class="relative w-full md:w-96">
                        <input type="text" readonly value="{{ url('/api/turbo/webhook') }}" id="webhookUrlInput"
                            class="w-full ltr text-left pl-4 pr-12 py-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#31006f]/20 font-mono shadow-sm">
                        <button onclick="navigator.clipboard.writeText(document.getElementById('webhookUrlInput').value); alert('تم نسخ الرابط بنجاح!');" 
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center text-gray-400 hover:text-[#31006f] hover:bg-purple-50 rounded-md transition cursor-pointer" title="نسخ الرابط">
                            <i class="fa-regular fa-copy text-lg"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs text-emerald-600 font-bold bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>النظام جاهز لتلقي استجابات Turbo (نموذج JSON)</span>
                </div>
            </div>
        </div>

        <!-- Tab 2: Get Status -->
        <div x-show="activeTab === 'status'" x-cloak class="space-y-6">
            
            <!-- Search Form -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <form @submit.prevent="fetchStatusData" class="flex flex-col md:flex-row items-end gap-4">
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">من تاريخ <span class="text-red-500">*</span></label>
                        <input type="date" x-model="fromDate" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#31006f] focus:ring-1 focus:ring-[#31006f] transition">
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">إلى تاريخ <span class="text-red-500">*</span></label>
                        <input type="date" x-model="toDate" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#31006f] focus:ring-1 focus:ring-[#31006f] transition">
                    </div>
                    <button type="submit" :disabled="loading" class="w-full md:w-auto px-8 py-3 bg-[#31006f] hover:bg-[#430099] text-white rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-md cursor-pointer disabled:opacity-70">
                        <i class="fa-solid fa-search" x-show="!loading"></i>
                        <i class="fa-solid fa-spinner fa-spin" x-show="loading" x-cloak></i>
                        <span>استعلام الآن</span>
                    </button>
                </form>
            </div>

            <!-- Results Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden relative">
                
                <!-- Loading Overlay -->
                <div x-show="loading" x-cloak class="absolute inset-0 z-10 bg-white/60 backdrop-blur-sm flex flex-col items-center justify-center">
                    <i class="fa-solid fa-circle-notch fa-spin text-4xl text-[#31006f] mb-3"></i>
                    <p class="font-bold text-gray-700 mt-2">جاري جلب البيانات من Turbo...</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 text-gray-600 font-bold border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-4">كود الشحنة (Tracking)</th>
                                <th class="px-5 py-4">كابتن التوصيل</th>
                                <th class="px-5 py-4">الفرع الحالي</th>
                                <th class="px-5 py-4">المبلغ المحصل</th>
                                <th class="px-5 py-4">حالة الشحنة</th>
                                <th class="px-5 py-4">ملاحظات والتأخير</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="item in apiResults" :key="item.code || item.id || Math.random()">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3 font-mono font-bold text-[#31006f]" x-text="item.code || '-'"></td>
                                    <td class="px-5 py-3 font-semibold text-gray-800">
                                        <div x-show="item.captain">
                                            <span x-text="item.captain"></span>
                                            <div class="text-xs text-gray-500" x-show="item.captain_number1" x-text="item.captain_number1"></div>
                                        </div>
                                        <span x-show="!item.captain" class="text-gray-400">غير محدد</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="text-gray-800" x-text="item.branch || '-'"></div>
                                        <div class="text-xs text-gray-500" x-show="item.expected_branch">المتوقع: <span x-text="item.expected_branch"></span></div>
                                    </td>
                                    <td class="px-5 py-3 font-bold text-emerald-600" x-text="(item.received_amount || 0) + ' ج.م'"></td>
                                    <td class="px-5 py-3">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold" 
                                            :class="getStatusColor(item.status_code || item.status)" 
                                            x-text="item.status || 'قيد الانتظار'"></span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-500 text-xs">
                                        <div x-show="item.delay_reason" class="text-amber-600"><i class="fa-solid fa-clock mr-1"></i> <span x-text="item.delay_reason"></span></div>
                                        <div x-show="item.return_reason" class="text-red-600"><i class="fa-solid fa-rotate-left mr-1"></i> <span x-text="item.return_reason"></span></div>
                                        <span x-show="!item.delay_reason && !item.return_reason">-</span>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Empty State -->
                            <tr x-show="apiResults.length === 0 && hasSearched && !loading" x-cloak>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-3">
                                        <i class="fa-solid fa-box-open text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold text-lg">لا توجد شحنات</h3>
                                    <p class="text-gray-500 mt-1">لم يتم العثور على أي شحنات مسجلة في هذا النطاق الزمني.</p>
                                </td>
                            </tr>

                            <!-- Initial State -->
                            <tr x-show="!hasSearched && !loading" x-cloak>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-3 text-blue-500">
                                        <i class="fa-solid fa-calendar-days text-2xl"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold text-lg">حدد النطاق الزمني</h3>
                                    <p class="text-gray-500 mt-1">قم بتحديد تاريخ البداية والنهاية واضغط على استعلام لجلب البيانات من Turbo.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        function turboStatusApp() {
            return {
                activeTab: 'webhook', // 'webhook' | 'status'
                loading: false,
                hasSearched: false,
                
                alertMessage: '',
                alertSuccess: true,

                // Dates for status search (default to last 7 days)
                fromDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
                toDate: new Date().toISOString().split('T')[0],
                
                apiResults: [],

                async fetchStatusData() {
                    if (!this.fromDate || !this.toDate) {
                        this.showAlert('يرجى تحديد تاريخ البداية والنهاية', false);
                        return;
                    }

                    if (this.fromDate > this.toDate) {
                        this.showAlert('تاريخ البداية يجب أن يكون قبل أو يساوي تاريخ النهاية', false);
                        return;
                    }

                    this.loading = true;
                    this.hasSearched = true;
                    this.apiResults = [];

                    try {
                        const res = await fetch('{{ route('turbo.api_status') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                from: this.fromDate,
                                to: this.toDate
                            })
                        });
                        
                        const data = await res.json();

                        if (data.success) {
                            this.apiResults = data.turbo || [];
                            if(this.apiResults.length > 0) {
                                this.showAlert(`تم استرجاع ${this.apiResults.length} شحنة بنجاح`, true);
                            }
                        } else {
                            this.showAlert(data.message || 'حدث خطأ في تحميل البيانات', false);
                        }
                    } catch (err) {
                        console.error("Error fetching Turbo API status:", err);
                        this.showAlert('حدث خطأ في الاتصال بالخادم', false);
                    } finally {
                        this.loading = false;
                    }
                },

                showAlert(msg, isSuccess = true) {
                    this.alertMessage = msg;
                    this.alertSuccess = isSuccess;
                    setTimeout(() => { this.alertMessage = ''; }, 4500);
                },

                getStatusColor(status) {
                    const statusStr = String(status).toLowerCase();
                    if (['pending', '1', 'قيد الانتظار'].includes(statusStr)) return 'bg-yellow-50 text-yellow-700 border border-yellow-200';
                    if (['in_transit', '2', 'جاري التوصيل'].includes(statusStr)) return 'bg-blue-50 text-blue-700 border border-blue-200';
                    if (['delivered', '3', 'تم التسليم'].includes(statusStr)) return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                    if (['canceled', '4', 'ملغي'].includes(statusStr)) return 'bg-red-50 text-red-700 border border-red-200';
                    return 'bg-gray-100 text-gray-700 border border-gray-200';
                }
            };
        }
    </script>
@endsection