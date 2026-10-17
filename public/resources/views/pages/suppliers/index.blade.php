@extends('layouts.app')

@section('title', 'الموردون | Suppliers')

@section('content')
    <!-- استدعاء المكتبات المساعدة للتفاعل والتنبيهات الاحترافية -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* سكرول بار أنيق مخصص يتناسب مع روح النظام */
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

        /* دعم اتجاه العناصر ديناميكياً حسب لغة السيستم الحالية */
        html[dir="ltr"] .rtl-flip {
            transform: scaleX(-1);
        }

        html[dir="ltr"] .text-right-lang {
            text-align: left !important;
        }

        html[dir="rtl"] .text-right-lang {
            text-align: right !important;
        }
    </style>

    @php
        // تجهيز بيانات الموردين من السيرفر لتمريرها مباشرة كـ JSON إلى AlpineJS لسرعة الاستجابة والبحث الفوري
        $suppliersData = $suppliers->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'phone' => $s->phone ?? '',
                'email' => $s->email ?? '',
                'address' => $s->address ?? '',
                'notes' => $s->notes ?? '',
                'is_active' => (bool) $s->is_active,
                'created_at' => $s->created_at ? $s->created_at->format('Y-m-d') : '',
            ];
        });
    @endphp

    <div class="space-y-6 pb-10 font-sans w-full" x-data="supplierManager({{ json_encode($suppliersData) }})" x-init="initData()">

        <!-- ========================================== -->
        <!--  نافذة الإضافة والتعديل المنبثقة (Modal)  -->
        <!-- ========================================== -->
        <div x-show="isModalOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="closeModal()"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-xl transform transition-all"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <!-- رأس النافذة المنبثقة -->
                <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid text-activeGold" :class="isEditMode ? 'fa-user-pen' : 'fa-user-plus'"></i>
                        <span x-show="!isEditMode" data-ar="إضافة مورد جديد" data-en="Add New Supplier">إضافة مورد
                            جديد</span>
                        <span x-show="isEditMode" data-ar="تعديل بيانات المورد" data-en="Edit Supplier Details">تعديل بيانات
                            المورد</span>
                    </h3>
                    <button @click="closeModal()"
                        class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- نموذج الإدخال -->
                <form @submit.prevent="submitForm()" class="p-6 space-y-4">
                    <div class="space-y-4">
                        <!-- الاسم -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <span data-ar="اسم المورد" data-en="Supplier Name">اسم المورد</span> <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="text" required x-model="formData.name"
                                class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold transition outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- الهاتف -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    <span data-ar="رقم الهاتف" data-en="Phone Number">رقم الهاتف</span>
                                </label>
                                <input type="tel" x-model="formData.phone"
                                    class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold transition outline-none">
                            </div>

                            <!-- البريد الإلكتروني -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    <span data-ar="البريد الإلكتروني" data-en="Email Address">البريد الإلكتروني</span>
                                </label>
                                <input type="email" x-model="formData.email"
                                    class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold transition outline-none">
                            </div>
                        </div>

                        <!-- العنوان -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <span data-ar="العنوان" data-en="Address">العنوان</span>
                            </label>
                            <input type="text" x-model="formData.address"
                                class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold transition outline-none">
                        </div>

                        <!-- الملاحظات -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <span data-ar="ملاحظات" data-en="Notes">ملاحظات</span>
                            </label>
                            <textarea rows="3" x-model="formData.notes"
                                class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold transition outline-none resize-none"></textarea>
                        </div>

                        <!-- حالة التفعيل -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-700" data-ar="حالة الحساب نشط"
                                    data-en="Account Status Active">حالة الحساب نشط</span>
                                <span class="text-xs text-gray-400" data-ar="تمكين المورد للتعامل الفوري ضمن الطلبات"
                                    data-en="Enable supplier for transactions">تمكين المورد للتعامل الفوري ضمن
                                    الطلبات</span>
                            </div>
                            <label class="relative inline-flex items-center定位 cursor-pointer select-none">
                                <input type="checkbox" x-model="formData.is_active" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:horizontal-align after:left-[2px] rtl:after:left-auto rtl:after:right-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-activeGold">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- أزرار التحكم بالنافذة -->
                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                            <span data-ar="إلغاء" data-en="Cancel">إلغاء</span>
                        </button>
                        <button type="submit" :disabled="isSaving"
                            class="px-5 py-2.5 rounded-lg bg-activeGold text-white font-medium hover:bg-[#735A3A] shadow-md transition flex items-center gap-2 disabled:opacity-50">
                            <i class="fa-solid" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                            <span
                                x-text="isSaving ? getTranslation('saving', 'جاري الحفظ...') : getTranslation('save', 'حفظ البيانات')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!--     نافذة عرض تفاصيل المورد (Show Profile)   -->
        <!-- ========================================== -->
        <div x-show="isViewOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="isViewOpen = false"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden transform transition-all"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="relative bg-gradient-to-r from-gray-900 to-sidebar p-6 text-white text-center">
                    <button @click="isViewOpen = false"
                        class="absolute top-4 right-4 rtl:right-auto rtl:left-4 text-white/70 hover:text-white transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                    <div class="w-20 h-20 bg-activeGold/20 border-2 border-activeGold text-activeGold rounded-full flex items-center justify-center text-3xl font-black mx-auto mb-3 shadow-md"
                        x-text="viewData.name ? viewData.name.substring(0, 1).toUpperCase() : 'S'"></div>
                    <h4 class="text-xl font-bold truncate" x-text="viewData.name"></h4>
                    <span class="inline-block mt-2 px-3 py-1 text-xs font-semibold rounded-full shadow-inner"
                        :class="viewData.is_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' :
                            'bg-rose-500/20 text-rose-300 border border-rose-500/30'">
                        <i class="fa-solid fa-circle text-[8px] mx-1"
                            :class="viewData.is_active ? 'text-emerald-400' : 'text-rose-400'"></i>
                        <span x-show="viewData.is_active" data-ar="حساب نشط" data-en="Active Account">نشط</span>
                        <span x-show="!viewData.is_active" data-ar="حساب معطل" data-en="Inactive Account">غير نشط</span>
                    </span>
                </div>

                <div class="p-6 space-y-4 bg-white text-right-lang">
                    <div class="flex items-center gap-3 border-b border-gray-50 pb-3">
                        <div class="w-9 h-9 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium" data-ar="رقم الهاتف" data-en="Phone Number"></p>
                            <p class="text-sm font-bold text-gray-800" x-text="viewData.phone || '-'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-b border-gray-50 pb-3">
                        <div class="w-9 h-9 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium" data-ar="البريد الإلكتروني"
                                data-en="Email Address"></p>
                            <p class="text-sm font-bold text-gray-800 break-all" x-text="viewData.email || '-'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-b border-gray-50 pb-3">
                        <div class="w-9 h-9 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium" data-ar="العنوان الفعلي"
                                data-en="Physical Address"></p>
                            <p class="text-sm font-bold text-gray-800" x-text="viewData.address || '-'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 border-b border-gray-50 pb-3">
                        <div class="w-9 h-9 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center shrink-0">
                            <i class="fa-regular fa-comment-dots"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium" data-ar="ملاحظات وسجل العميل"
                                data-en="Notes & Logs"></p>
                            <p class="text-sm font-medium text-gray-600 whitespace-pre-line"
                                x-text="viewData.notes || '-'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-1">
                        <div class="w-9 h-9 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center shrink-0">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium" data-ar="تاريخ التسجيل بالسيستم"
                                data-en="Registration Date"></p>
                            <p class="text-sm font-bold text-gray-800" x-text="viewData.created_at || '-'"></p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button @click="isViewOpen = false"
                        class="px-5 py-2 rounded-lg bg-gray-800 text-white font-medium hover:bg-gray-700 transition text-sm shadow-sm"
                        data-ar="إغلاق العرض" data-en="Close Profile"></button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!--             رأس الصفحة والعناوين             -->
        <!-- ========================================== -->
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-truck-field text-activeGold"></i>
                    <span data-ar="إدارة الموردين " data-en="Suppliers Management">إدارة الموردين
                    </span>
                </h2>
                <p class="text-gray-500 text-sm mt-1" data-ar="سجل الموردين المعتمدين وعناوين التواصل الخاصة بـ Tagmedix"
                    data-en="Authorized suppliers directory and contact endpoints for Tagmedix"></p>
            </div>
        </div>

        <!-- ========================================== -->
        <!--          قسم بطاقات الإحصائيات (KPIs)       -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div
                class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group">
                <div>
                    <p class="text-gray-400 text-xs font-semibold mb-1" data-ar="إجمالي الموردين"
                        data-en="Total Suppliers"></p>
                    <h3 class="text-3xl font-black text-gray-800" x-text="suppliers.length">0</h3>
                </div>
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group">
                <div>
                    <p class="text-gray-400 text-xs font-semibold mb-1" data-ar="الحسابات النشطة"
                        data-en="Active Suppliers"></p>
                    <h3 class="text-3xl font-black text-emerald-600" x-text="suppliers.filter(s => s.is_active).length">0
                    </h3>
                </div>
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-check text-xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group">
                <div>
                    <p class="text-gray-400 text-xs font-semibold mb-1" data-ar="الحسابات المعطلة"
                        data-en="Inactive Suppliers"></p>
                    <h3 class="text-3xl font-black text-rose-600" x-text="suppliers.filter(s => !s.is_active).length">0
                    </h3>
                </div>
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-50 to-rose-100 flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-slash text-xl"></i>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!--        أدوات التحكم والبحث والفلترة          -->
        <!-- ========================================== -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden flex flex-col">
            <div
                class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button @click="openCreateModal()"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span data-ar="إضافة مورد جديد" data-en="Add Supplier">مورد جديد</span>
                    </button>
                </div>

                <div class="relative w-full md:w-[380px] group">
                    <div
                        class="absolute inset-y-0 ltr:right-4 rtl:left-4 flex items-center pointer-events-none transition-colors group-focus-within:text-activeGold">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-activeGold"></i>
                    </div>
                    <input type="text" x-model="searchQuery"
                        placeholder="ابحث باسم المورد، الهاتف أو البريد... | Search..."
                        class="block w-full rtl:pr-10 ltr:pl-10 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold bg-white shadow-sm placeholder-gray-400 transition-all text-right-lang">
                    <button x-show="searchQuery.length > 0" @click="searchQuery = ''" style="display: none;"
                        class="absolute inset-y-0 ltr:right-10 rtl:left-4 flex items-center text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!--   جدول العرض المخصص للديسكتوب (Desktop Table) -->
            <!-- ========================================== -->
            <div class="hidden md:block overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 60vh;">
                <table class="w-full text-right-lang text-sm">
                    <thead
                        class="bg-gray-100 text-gray-600 font-bold sticky top-0 z-10 shadow-sm backdrop-blur-md bg-opacity-90">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap w-12">#</th>
                            <th class="px-6 py-4 whitespace-nowrap" data-ar="المورد" data-en="Supplier Name">المورد</th>
                            <th class="px-6 py-4 whitespace-nowrap" data-ar="بيانات التواصل" data-en="Contact Metadata">
                                بيانات التواصل</th>
                            <th class="px-6 py-4 whitespace-nowrap" data-ar="العنوان الفعلي" data-en="Address">العنوان
                                الفعلي</th>
                            <th class="px-6 py-4 whitespace-nowrap text-center" data-ar="الحالة" data-en="Status">الحالة
                            </th>
                            <th class="px-6 py-4 text-center whitespace-nowrap" data-ar="إجراءات الحساب"
                                data-en="Actions">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="(supplier, index) in filteredSuppliers" :key="supplier.id">
                            <tr class="hover:bg-blue-50/20 transition-colors group">
                                <td class="px-6 py-4 text-gray-400 font-medium" x-text="index + 1"></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3 cursor-pointer"
                                        @click="openViewProfile(supplier)">
                                        <div class="w-9 h-9 rounded-full bg-gray-100 text-gray-700 font-bold flex items-center justify-center border border-gray-200 group-hover:border-activeGold group-hover:text-activeGold transition"
                                            x-text="supplier.name.substring(0, 1).toUpperCase()"></div>
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 group-hover:text-activeGold transition"
                                                x-text="supplier.name"></span>
                                            <span class="text-[11px] text-gray-400 font-medium mt-0.5"
                                                x-text="supplier.created_at"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 space-y-0.5">
                                    <div class="flex items-center gap-1.5 text-gray-700 font-medium">
                                        <i class="fa-solid fa-phone text-xs text-gray-400"></i>
                                        <span x-text="supplier.phone || '-'"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-400 text-xs">
                                        <i class="fa-regular fa-envelope text-xs"></i>
                                        <span class="truncate max-w-[200px]" x-text="supplier.email || '-'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 max-w-[220px] truncate" :title="supplier.address"
                                    x-text="supplier.address || '-'"></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                        :class="supplier.is_active ?
                                            'bg-emerald-50 text-emerald-700 border border-emerald-100' :
                                            'bg-rose-50 text-rose-700 border border-rose-100'">
                                        <i class="fa-solid fa-circle text-[6px] mx-1"
                                            :class="supplier.is_active ? 'text-emerald-500' : 'text-rose-500'"></i>
                                        <span x-show="supplier.is_active" data-ar="نشط" data-en="Active">نشط</span>
                                        <span x-show="!supplier.is_active" data-ar="غير نشط" data-en="Inactive">غير
                                            نشط</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openViewProfile(supplier)"
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-900 hover:text-white flex items-center justify-center transition-colors shadow-sm"
                                            title="عرض الملف">
                                            <i class="fa-regular fa-eye text-xs"></i>
                                        </button>
                                        <button @click="openEditModal(supplier)"
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-activeGold hover:text-white flex items-center justify-center transition-colors shadow-sm"
                                            title="تعديل">
                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                        </button>
                                        <button @click="deleteSupplier(supplier.id)"
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition-colors shadow-sm"
                                            title="حذف">
                                            <i class="fa-regular fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- ========================================== -->
            <!--     قائمة بطاقات الموبايل والتابلت المتجاوبة   -->
            <!-- ========================================== -->
            <div class="block md:hidden p-4 space-y-4 overflow-y-auto" style="max-height: 65vh;">
                <template x-for="(supplier, index) in filteredSuppliers" :key="supplier.id">
                    <div
                        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 space-y-3 group hover:border-activeGold/30 transition-all">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3" @click="openViewProfile(supplier)">
                                <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-800 font-bold flex items-center justify-center text-base border border-gray-200"
                                    x-text="supplier.name.substring(0, 1).toUpperCase()"></div>
                                <div>
                                    <h4 class="font-bold text-gray-900 group-hover:text-activeGold transition"
                                        x-text="supplier.name"></h4>
                                    <span class="text-[10px] text-gray-400" x-text="supplier.created_at"></span>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                :class="supplier.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' :
                                    'bg-rose-50 text-rose-700 border border-rose-100'">
                                <span x-show="supplier.is_active" data-ar="نشط" data-en="Active">نشط</span>
                                <span x-show="!supplier.is_active" data-ar="غير نشط" data-en="Inactive">غير نشط</span>
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-2.5 text-xs space-y-1 text-right-lang">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="fa-solid fa-phone w-4 text-gray-400"></i>
                                <span x-text="supplier.phone || '-'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-500 break-all">
                                <i class="fa-regular fa-envelope w-4 text-gray-400"></i>
                                <span x-text="supplier.email || '-'"></span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-500 truncate">
                                <i class="fa-solid fa-location-dot w-4 text-gray-400"></i>
                                <span x-text="supplier.address || '-'"></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-50">
                            <button @click="openViewProfile(supplier)"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                <i class="fa-regular fa-eye"></i> <span data-ar="عرض" data-en="View">عرض</span>
                            </button>
                            <button @click="openEditModal(supplier)"
                                class="px-3 py-1.5 bg-gray-100 hover:bg-activeGold hover:text-white text-gray-700 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                <i class="fa-regular fa-pen-to-square"></i> <span data-ar="تعديل"
                                    data-en="Edit">تعديل</span>
                            </button>
                            <button @click="deleteSupplier(supplier.id)"
                                class="px-3 py-1.5 bg-gray-50 hover:bg-rose-50 hover:text-rose-600 text-gray-400 rounded-lg text-xs font-semibold transition flex items-center gap-1">
                                <i class="fa-regular fa-trash-can"></i> <span data-ar="حذف" data-en="Delete">حذف</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- رسالة عدم وجود نتائج مطابقة للبحث -->
            <div x-show="filteredSuppliers.length === 0" style="display: none;" class="p-16 text-center">
                <div class="flex flex-col items-center justify-center text-gray-400">
                    <i class="fa-solid fa-truck-ramp-box text-5xl mb-4 opacity-40"></i>
                    <p class="text-base font-bold text-gray-500" data-ar="لا توجد نتائج مطابقة لبحثك الحركي"
                        data-en="No matching suppliers found"></p>
                </div>
            </div>

            <!-- فوتر الجدول التوضيحي -->
            <div class="p-3 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-400 font-medium">
                <span data-ar="نهاية قائمة موردي ومصانع الأنظمة" data-en="End of structural suppliers log">نهاية قائمة
                    الموردين</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('supplierManager', (initialSuppliers) => ({
                searchQuery: '',
                suppliers: initialSuppliers,

                // حالات النوافذ المنبثقة والتحكم بالبيانات
                isModalOpen: false,
                isEditMode: false,
                isSaving: false,
                isViewOpen: false,

                // المتغيرات الهيكلية
                formData: {
                    id: '',
                    name: '',
                    phone: '',
                    email: '',
                    address: '',
                    notes: '',
                    is_active: true
                },
                viewData: {},

                initData() {
                    // مراقبة تغيير لغة النظام من خلال زر تبديل اللغات لضمان توافق العناصر الفورية
                    const langBtn = document.getElementById("languageBtn");
                    if (langBtn) {
                        langBtn.addEventListener('click', () => {
                            this.$nextTick(() => {
                                this.updateDynamicPlaceholders();
                            });
                        });
                    }
                },

                // الفلترة والبحث الديناميكي الفوري
                get filteredSuppliers() {
                    if (this.searchQuery.trim() === '') return this.suppliers;
                    const q = this.searchQuery.toLowerCase();
                    return this.suppliers.filter(s => {
                        return (s.name && s.name.toLowerCase().includes(q)) ||
                            (s.phone && s.phone.toLowerCase().includes(q)) ||
                            (s.email && s.email.toLowerCase().includes(q));
                    });
                },

                // استخراج النصوص المترجمة يدوياً داخل الجافا سكريبت بناءً على لغة السيستم الحالية للـ Layout
                getTranslation(key, fallback) {
                    const currentLang = document.documentElement.lang || 'ar';
                    const dict = {
                        saving: {
                            ar: 'جاري الحفظ...',
                            en: 'Saving Data...'
                        },
                        save: {
                            ar: 'حفظ البيانات',
                            en: 'Save Data'
                        },
                        confirmTitle: {
                            ar: 'هل أنت متأكد؟',
                            en: 'Are you sure?'
                        },
                        confirmText: {
                            ar: 'سيتم حذف المورد نهائياً من قاعدة البيانات!',
                            en: 'This will completely drop the supplier profile!'
                        },
                        deleteBtn: {
                            ar: 'نعم، احذفه',
                            en: 'Yes, delete'
                        },
                        cancelBtn: {
                            ar: 'إلغاء',
                            en: 'Cancel'
                        },
                        successTitle: {
                            ar: 'تم بنجاح!',
                            en: 'Success!'
                        },
                        errorTitle: {
                            ar: 'خطأ!',
                            en: 'Error!'
                        }
                    };
                    return dict[key] ? dict[key][currentLang] : fallback;
                },

                openCreateModal() {
                    this.isEditMode = false;
                    this.formData = {
                        id: '',
                        name: '',
                        phone: '',
                        email: '',
                        address: '',
                        notes: '',
                        is_active: true
                    };
                    this.isModalOpen = true;
                },

                openEditModal(supplier) {
                    this.isEditMode = true;
                    // نسخ البيانات لعدم تعديل الجدول الأصلي إلا بعد الحفظ في السيرفر بنجاح
                    this.formData = Object.assign({}, supplier);
                    this.isModalOpen = true;
                },

                openViewProfile(supplier) {
                    this.viewData = supplier;
                    this.isViewOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                // معالجة وحفظ البيانات (الإضافة والتعديل عبر AJAX) ومتوافقة تماماً مع الـ Controller والـ Routes الخاصة بك
                async submitForm() {
                    console.log('submitForm called');
                    this.isSaving = true;
                    const currentLang = document.documentElement.lang || 'ar';

                    let url = '/suppliers';
                    let method = 'POST';

                    if (this.isEditMode) {
                        url = `/suppliers/${this.formData.id}`;
                        method = 'PUT'; // يطابق دالة update بـ Controller
                    }

                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.formData)
                        });

                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: this.getTranslation('successTitle', 'تم بنجاح!'),
                                text: currentLang === 'ar' ?
                                    'تم تحديث بيانات المورد بنجاح في قاعدة البيانات.' :
                                    'Supplier logs committed successfully.',
                                confirmButtonColor: '#8A704C',
                                confirmButtonText: currentLang === 'ar' ? 'حسناً' : 'OK'
                            }).then(() => {
                                window.location
                                    .reload(); // إعادة التحميل لمزامنة الإحصائيات الفوقية من السيرفر
                            });
                        } else {
                            throw new Error('Server Validation Failed');
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: this.getTranslation('errorTitle', 'خطأ!'),
                            text: currentLang === 'ar' ?
                                'حدث خطأ أثناء معالجة الطلب، يرجى مراجعة الحقول المطلوبة.' :
                                'An error occurred during communication.',
                            confirmButtonColor: '#8A704C'
                        });
                    } finally {
                        this.isSaving = false;
                    }
                },

                // دالة الحذف الفوري AJAX المتكاملة مع دالة destroy بالـ Controller الخاص بك
                deleteSupplier(id) {
                    const currentLang = document.documentElement.lang || 'ar';

                    Swal.fire({
                        title: this.getTranslation('confirmTitle', 'هل أنت متأكد؟'),
                        text: this.getTranslation('confirmText', 'سيتم حذف السجل بشكل نهائي!'),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: this.getTranslation('deleteBtn', 'حذف'),
                        cancelButtonText: this.getTranslation('cancelBtn', 'إلغاء')
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const response = await fetch(`/suppliers/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    }
                                });

                                if (response.ok || response.status === 204) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: this.getTranslation('successTitle',
                                            'تم بنجاح!'),
                                        text: currentLang === 'ar' ?
                                            'تم حذف ملف المورد بالكامل.' :
                                            'Supplier removed successfully.',
                                        confirmButtonColor: '#8A704C'
                                    }).then(() => {
                                        // فلترة الحساب محلياً فوراً لحذفه من الجدول بدون الحاجة لإعادة تحميل الصفحة بالكامل
                                        this.suppliers = this.suppliers.filter(s =>
                                            s.id !== id);
                                    });
                                } else {
                                    throw new Error('Deletion Failed');
                                }
                            } catch (e) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطأ',
                                    text: 'فشل حذف المورد، قد يكون مرتبطاً ببيانات مخزنية أخرى.',
                                    confirmButtonColor: '#8A704C'
                                });
                            }
                        }
                    });
                }
            }));
        });
    </script>
@endpush
