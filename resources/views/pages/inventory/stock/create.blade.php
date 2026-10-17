@extends('layouts.app')

@section('title', 'إضافة مخزون جديد')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6 pb-20">

        <!-- رسائل التنبيه والنجاح والخطأ -->
        <div id="alertsContainer" class="space-y-3">
            @if (session('success'))
                <div
                    class="p-4 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="font-bold text-xs sm:text-sm">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()"
                        class="text-emerald-700 hover:text-emerald-900">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div
                    class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-800 flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                        <span class="font-bold text-xs sm:text-sm">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div
                    class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-800 space-y-2 shadow-sm animate-fade-in">
                    <div class="flex items-center justify-between border-b border-rose-200 pb-2">
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm">
                            <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
                            <span>حدثت خطأ أثناء الحفظ (Validation Errors):</span>
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()"
                            class="text-rose-700 hover:text-rose-900">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 pr-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- الترويسة العلوية -->
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-secondaryCream shadow-sm">
            <div>
                <h2 class="text-xl font-bold text-darkText flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-[#8A704C]"></i>
                    إضافة شحنة/مخزون جديد
                </h2>
                <p class="text-xs text-gray-500 mt-1">قم بتعبئة بيانات الصنف والوحدات الواردة لتغذية المخزن بنجاح.</p>
            </div>
            <button type="button" id="toggleHelpBtn"
                class="flex items-center gap-2 bg-rose-50 hover:bg-rose-100 text-[#8A704C] font-semibold px-4 py-2 rounded-xl border border-rose-200 text-xs sm:text-sm transition-all cursor-pointer">
                <i class="fa-solid fa-circle-info text-base"></i>
                <span>شرح عملية الإضافة</span>
            </button>
        </div>

        <!-- شرح الإضافة -->
        <div id="helpSection"
            class="hidden bg-white border-2 border-dashed border-rose-200 rounded-2xl p-5 text-darkText space-y-3 animate-fade-in">
            <h3 class="font-bold text-[#8A704C] flex items-center gap-2 text-sm sm:text-base">
                <i class="fa-solid fa-lightbulb text-yellow-500"></i>
                دليل التتبع والتعامل مع المخزون:
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm text-gray-600">
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                    <strong class="text-gray-800 block mb-1">1. النمط الإجمالي (Bulk):</strong>
                    يُستخدم للمستلزمات التي لا تتطلب تتبع كل وحدة، يتم الإدخال بالكمية الكلية.
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                    <strong class="text-gray-800 block mb-1">2. منتج نهائي (Finished Product):</strong>
                    يُفعل قسم خاص لإدخال تفاصيل المنتجات المصنعة بمقاسات وأنماط مخصصة جاهزة للبيع.
                </div>
            </div>
        </div>

        <form id="inventoryForm" action="{{ route('inventory.stocks.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- حقول مخفية للتحكم بالقيم التلقائية -->
            <input type="hidden" name="unit_type" id="main_unit_type" value="{{ old('unit_type', 'meter') }}">
            <input type="hidden" name="stock_tracking_type" id="main_tracking_type"
                value="{{ old('stock_tracking_type', 'bulk') }}">

            <!-- القسم الأول: البيانات الأساسية -->
            <div
                class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm space-y-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-2 h-full bg-[#8A704C]"></div>
                <h3 class="font-bold text-gray-800 text-base border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#8A704C]"></i>
                    بيانات الصنف الرئيسية
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- اسم الصنف -->
                    <!-- اسم الصنف -->
                    <div class="lg:col-span-2 relative">

                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            اسم الصنف
                            <span class="text-red-500">*</span>
                        </label>

                        <!-- القيمة التي سيرسلها Laravel -->
                        <input type="hidden" name="existing_item_id" id="existing_item_id"
                            value="{{ old('existing_item_id') }}">

                        <!-- Input البحث -->
                        <div class="relative">

                            <input type="text" name="name" id="item_name" value="{{ old('name') }}" required
                                autocomplete="off" placeholder="ابحث عن صنف موجود أو اكتب اسم صنف جديد..."
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">

                            <div id="itemSearchIcon"
                                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>

                        </div>

                        <!-- نتائج البحث -->
                        <div id="itemSearchResults"
                            class="hidden absolute z-50 left-0 right-0 mt-2 bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden">

                            <div id="itemSearchResultsList" class="max-h-72 overflow-y-auto"></div>

                            <!-- رسالة عند عدم وجود نتائج -->
                            <div id="itemNoResults" class="hidden p-4 text-center text-sm text-gray-500">
                                <i class="fa-solid fa-box-open text-lg mb-2 block text-gray-400"></i>

                                <span>
                                    لا يوجد صنف بهذا الاسم
                                </span>

                                <div class="text-xs mt-1 text-emerald-600 font-bold">
                                    سيتم إنشاء صنف جديد عند الحفظ
                                </div>
                            </div>

                        </div>

                        <!-- حالة الصنف -->
                        <div id="itemSelectionStatus" class="hidden mt-2 text-xs font-bold"></div>

                    </div>

                    <!-- الفئة -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">الفئة <span
                                class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all font-bold text-[#8A704C]">
                            <option value="">اختر الفئة...</option>
                            <option value="fabric" {{ old('category') == 'fabric' ? 'selected' : '' }}>أقمشة (Fabric)
                            </option>
                            <option value="accessory" {{ old('category') == 'accessory' ? 'selected' : '' }}>إكسسوارات
                                (Accessory)</option>
                            <option value="finished_product" {{ old('category') == 'finished_product' ? 'selected' : '' }}>
                                منتج نهائي (Finished Product)</option>
                        </select>
                    </div>

                    <!-- اللون الرئيسي -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex justify-between">
                            <span>اللون الرئيسي <span class="text-red-500">*</span></span>
                            <button type="button"
                                onclick="document.getElementById('addColorModal').classList.remove('hidden')"
                                class="text-[#8A704C] hover:text-yellow-700 flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus"></i> إضافة لون
                            </button>
                        </label>
                        <select name="color_id" id="main_color_id" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">
                            <option value="">اختر اللون...</option>
                            @foreach ($colors ?? [] as $color)
                                <option value="{{ $color->id }}"
                                    {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                    {{ $color->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- أقل كمية -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">أقل كمية (حد الأمان) <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="minimum_stock" id="minimum_stock"
                            value="{{ old('minimum_stock', 1) }}" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">
                    </div>

                    <!-- إعدادات التتبع العادية -->
                    <div id="standardSettingsContainer">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة التعامل مع المخزون <span
                                class="text-red-500">*</span></label>
                        <select id="visible_tracking_type"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">
                            <option value="bulk" {{ old('stock_tracking_type') == 'bulk' ? 'selected' : '' }}>إجمالي
                                (Bulk)</option>
                            <option value="individual" {{ old('stock_tracking_type') == 'individual' ? 'selected' : '' }}>
                                منفرد / توبات (Individual)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- القسم الخاص بالمنتج النهائي -->
            <div id="finishedProductSection"
                class="hidden bg-gradient-to-br from-white to-[#fbfaf8] rounded-2xl p-5 md:p-6 border-2 border-[#8A704C]/30 shadow-lg space-y-5 animate-fade-in relative">
                <div class="absolute top-4 left-4">
                    <span class="bg-[#8A704C] text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm">
                        <i class="fa-solid fa-shirt ml-1"></i> قسم المصنع
                    </span>
                </div>

                <h3 class="font-bold text-[#8A704C] text-lg border-b border-[#8A704C]/20 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-tags"></i>
                    تفاصيل المنتج الجاهز (Finished Product)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">نوع البنطلون <span
                                class="text-red-500">*</span></label>
                        <select name="items[0][pant_type_id]" id="fp_pant_type"
                            class="fp-input w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر نوع البنطلون</option>
                            @foreach ($pantTypes ?? [] as $pantType)
                                <option value="{{ $pantType->id }}">{{ $pantType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">نوع المنتج <span
                                class="text-red-500">*</span></label>
                        <select name="items[0][type_id]" id="fp_product_type"
                            class="fp-input w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر نوع المنتج</option>
                            @foreach ($types ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">الستايل</label>
                        <select name="items[0][style_id]" id="fp_style"
                            class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر الستايل</option>
                            @foreach ($styles ?? [] as $style)
                                <option value="{{ $style->id }}">{{ $style->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">اللون (مربوط بالرئيسي)</label>
                        <select name="items[0][color_id]" id="fp_color_id"
                            class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر اللون</option>
                            @foreach ($colors ?? [] as $color)
                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">المقاس</label>
                        <select name="items[0][size_id]" id="fp_size"
                            class="fp-input w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر المقاس</option>
                            @foreach ($sizes ?? [] as $size)
                                <option value="{{ $size->id }}">{{ $size->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">نوع الكم</label>
                        <select name="items[0][sleeve_type_id]" id="fp_sleeve"
                            class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر نوع الكم</option>
                            @foreach ($sleeveType ?? [] as $sleeve)
                                <option value="{{ $sleeve->id }}">{{ $sleeve->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- الحسابات المدمجة الذكية -->
                    <div class="md:col-span-2 lg:col-span-3 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mt-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3"><i
                                class="fa-solid fa-calculator text-emerald-600 ml-1"></i> الحسابات (الكمية - السعر -
                            الإجمالي)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-2">الكمية (بالقطعة)</label>
                                <input type="number" name="items[0][quantity]" id="fp_qty" value="1"
                                    min="1"
                                    class="fp-calc w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-2">سعر القطعة (التكلفة)</label>
                                <input type="number" step="0.01" name="items[0][unit_cost]" id="fp_price"
                                    value="0" min="0"
                                    class="fp-calc w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                                    dir="ltr">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-emerald-600 mb-2">الإجمالي الكلي</label>
                                <div
                                    class="w-full h-[52px] flex items-center justify-center bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-lg font-black font-mono shadow-inner">
                                    <span id="fp_total_display">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 mb-2">ملاحظات خاصة ومقاسات مخصصة لهذه القطعة
                            (إن وجدت)</label>
                        <input type="text" name="items[0][notes]"
                            placeholder="أدخل أي ملاحظات للتفصيل أو التعديل هنا..."
                            class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm font-medium text-gray-800 shadow-sm">
                    </div>
                </div>
            </div>

            <!-- القسم 2 أ: Bulk (مخفي للجاهز) -->
            <div id="bulkSection" class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-800 text-base border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-cubes text-[#8A704C]"></i>
                    إدخال كمية إجمالية (Bulk)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">وحدة القياس <span
                                class="text-red-500">*</span></label>
                        <select id="bulk_unit_type"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                            <option value="meter">متر (Meter)</option>
                            <option value="kg">كيلو (Kg)</option>
                            <option value="piece">قطعة (Piece)</option>
                            <option value="quantity">كمية عامة (Quantity)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">الكمية <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.001" min="0" id="bulk_quantity" name="quantity"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">تكلفة الوحدة <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" id="bulk_unit_cost" name="unit_cost"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                    </div>
                </div>
            </div>

            <!-- Section 2B: Individual Units Entry -->
            <div id="individualSection"
                class="hidden bg-white rounded-2xl p-5 md:p-6 border border-secondaryCream shadow-sm space-y-4">
                <div
                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-secondaryCream/60 pb-3 gap-2">
                    <h3 class="font-bold text-darkText text-base flex items-center gap-2">
                        <i class="fa-solid fa-barcode text-primaryBrown"></i>
                        تفاصيل الوحدات الفعلية (التوبات / الأطراد)
                    </h3>
                    <button type="button" id="addUnitRowBtn"
                        class="bg-primaryBrown hover:bg-mutedBrown text-white font-semibold text-xs px-3.5 py-2 rounded-xl flex items-center gap-2 transition-all shadow-sm cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                        <span>إضافة وحدة جديدة</span>
                    </button>
                </div>

                <div class="space-y-3" id="unitsContainer">
                    <!-- Dynamic Rows inserted here -->
                </div>
            </div>

            <!-- Submit Button Trigger -->
            <div class="flex justify-end">
                <button type="button" id="openReviewModalBtn"
                    class="bg-primaryBrown hover:bg-mutedBrown text-white font-bold px-8 py-3 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center gap-2 text-sm cursor-pointer">
                    <i class="fa-solid fa-eye"></i>
                    <span>مراجعة وإتمام الإضافة</span>
                </button>
            </div>
        </form>
    </div>

    <!-- نافذة إضافة لون -->
    <div id="addColorModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div
            class="bg-white rounded-2xl max-w-md w-full border border-gray-200 shadow-2xl overflow-hidden flex flex-col animate-slide-up">
            <div class="bg-[#8A704C] text-white p-4 flex justify-between items-center">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-palette"></i> إضافة لون جديد
                </h3>
                <button type="button" onclick="document.getElementById('addColorModal').classList.add('hidden')"
                    class="text-white/80 hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark fa-lg"></i>
                </button>
            </div>

            <div id="addColorForm" class="p-6 flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">اسم اللون</label>
                    <input type="text" name="name" id="new_color_name" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">المعرف (Identifier)</label>
                    <input type="text" name="identifier" id="new_color_identifier" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                        placeholder="مثال: COLOR-RED">
                </div>
                <div class="mt-4 flex gap-3">
                    <button type="button" id="saveColorBtn"
                        class="flex-1 bg-[#8A704C] hover:bg-yellow-800 text-white font-bold py-3 rounded-xl transition-colors cursor-pointer">
                        حفظ
                    </button>
                    <button type="button" onclick="document.getElementById('addColorModal').classList.add('hidden')"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 rounded-xl transition-colors cursor-pointer">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- نافذة المراجعة (Review Modal) الذكية -->
    <div id="reviewModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div
            class="bg-white rounded-2xl max-w-2xl w-full border border-gray-200 shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-slide-up">

            <div class="bg-[#8A704C] text-white p-4 flex justify-between items-center">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-receipt"></i> معاينة الطلب قبل الاعتماد
                </h3>
                <button type="button" id="closeReviewModalBtn" class="text-white/80 hover:text-white text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-4 text-xs sm:text-sm" id="modalContentArea">
                <!-- سيتم حقن كارت المراجعة هنا بواسطة الجافاسكربت -->
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                <button type="button" id="cancelSubmitBtn"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-5 py-2.5 rounded-xl transition-colors">
                    تعديل البيانات
                </button>
                <button type="button" id="confirmSubmitBtn"
                    class="bg-[#8A704C] hover:bg-yellow-800 text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow flex items-center gap-2">
                    <i class="fa-solid fa-check" id="confirmBtnIcon"></i>
                    <span id="confirmBtnText">تأكيد وإتمام الحفظ</span>
                </button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* =========================================================
               DOM ELEMENTS
            ========================================================= */

            const inventoryForm = document.getElementById('inventoryForm');

            const categorySelect = document.getElementById('category');
            const visibleTrackingType = document.getElementById('visible_tracking_type');

            const mainUnitType = document.getElementById('main_unit_type');
            const mainTrackingType = document.getElementById('main_tracking_type');

            const bulkSection = document.getElementById('bulkSection');
            const individualSection = document.getElementById('individualSection');
            const finishedProductSection = document.getElementById('finishedProductSection');
            const standardSettingsContainer = document.getElementById('standardSettingsContainer');

            const bulkUnitSelect = document.getElementById('bulk_unit_type');
            const bulkQuantity = document.getElementById('bulk_quantity');
            const bulkUnitCost = document.getElementById('bulk_unit_cost');

            const unitsContainer = document.getElementById('unitsContainer');
            const addUnitRowBtn = document.getElementById('addUnitRowBtn');

            const mainColorSelect = document.getElementById('main_color_id');
            const fpColorSelect = document.getElementById('fp_color');

            const itemNameInput = document.getElementById('item_name');
            const existingItemIdInput = document.getElementById('existing_item_id');

            const itemSearchResults = document.getElementById('itemSearchResults');
            const itemSearchResultsList = document.getElementById('itemSearchResultsList');
            const itemNoResults = document.getElementById('itemNoResults');
            const itemSelectionStatus = document.getElementById('itemSelectionStatus');

            const fpSection = finishedProductSection;

            const fpPantType = document.getElementById('fp_pant_type');
            const fpProductType = document.getElementById('fp_product_type');
            const fpStyle = document.getElementById('fp_style');
            const fpColor = document.getElementById('fp_color');
            const fpSize = document.getElementById('fp_size');
            const fpSleeveType = document.getElementById('fp_sleeve_type');
            const fpQty = document.getElementById('fp_quantity');
            const fpPrice = document.getElementById('fp_price');
            const fpNotes = document.getElementById('fp_notes');
            const fpTotalDisplay = document.getElementById('fp_total_display');

            const reviewModal = document.getElementById('reviewModal');
            const modalContentArea = document.getElementById('modalContentArea');

            const addColorModal = document.getElementById('addColorModal');
            const addColorForm = document.getElementById('addColorForm');
            const saveColorBtn = document.getElementById('saveColorBtn');

            const openReviewModalBtn = document.getElementById('openReviewModalBtn');
            const closeReviewModalBtn = document.getElementById('closeReviewModalBtn');
            const cancelSubmitBtn = document.getElementById('cancelSubmitBtn');
            const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');

            const alertsContainer = document.getElementById('alertsContainer');

            let unitIndex = 0;
            let isSubmitting = false;

            let itemSearchTimer = null;
            let activeItemIndex = -1;



            const existingItems = @json($existingItemsForJs);

            /* =========================================================
               SAFETY
            ========================================================= */

            if (!inventoryForm) {
                console.error('inventoryForm not found.');
                return;
            }

            /* =========================================================
               ESCAPE HTML
            ========================================================= */

            function escapeHtml(value) {

                const div = document.createElement('div');

                div.textContent = value ?? '';

                return div.innerHTML;
            }


            /* =========================================================
               NORMALIZE SEARCH TEXT
            ========================================================= */

            function normalizeSearchText(value) {

                return String(value ?? '')
                    .trim()
                    .toLocaleLowerCase('ar');
            }


            /* =========================================================
               GET SELECT TEXT
            ========================================================= */

            function getSelectText(select, fallback = '-') {

                if (!select || select.selectedIndex < 0) {
                    return fallback;
                }

                return select.options[select.selectedIndex]?.text || fallback;
            }


            /* =========================================================
               CATEGORY LABEL
            ========================================================= */

            function getCategoryLabel(category) {

                const labels = {
                    fabric: 'أقمشة',
                    accessory: 'إكسسوارات',
                    finished_product: 'منتج نهائي',
                };

                return labels[category] || category || '';
            }


            /* =========================================================
               OPEN ITEM SEARCH
            ========================================================= */

            function openItemSearch() {

                if (!itemSearchResults) {
                    return;
                }

                itemSearchResults.classList.remove('hidden');
            }


            /* =========================================================
               CLOSE ITEM SEARCH
            ========================================================= */

            function closeItemSearch() {

                if (!itemSearchResults) {
                    return;
                }

                itemSearchResults.classList.add('hidden');

                activeItemIndex = -1;
            }


            /* =========================================================
               CLEAR EXISTING ITEM SELECTION
               
               If the user changes the name manually after selecting
               an existing item, the hidden ID MUST be cleared.
            ========================================================= */

            function clearExistingItemSelection() {

                if (existingItemIdInput) {
                    existingItemIdInput.value = '';
                }

                if (itemSelectionStatus) {

                    itemSelectionStatus.className =
                        'hidden mt-2 text-xs font-bold';

                    itemSelectionStatus.textContent = '';
                }
            }


            /* =========================================================
               SET EXISTING ITEM
            ========================================================= */

            function selectExistingItem(item) {

                if (!item) {
                    return;
                }

                /* Store existing item ID */
                if (existingItemIdInput) {
                    existingItemIdInput.value = item.id;
                }

                /* Store exact existing item name */
                if (itemNameInput) {
                    itemNameInput.value = item.name;
                }

                /* ---------------------------------------------------------
                   If category select exists, synchronize it with the
                   selected existing item.
                --------------------------------------------------------- */

                if (categorySelect && item.category) {

                    categorySelect.value = item.category;

                    handleCategoryChange();
                }

                /* ---------------------------------------------------------
                   Selected status
                --------------------------------------------------------- */

                if (itemSelectionStatus) {

                    itemSelectionStatus.className =
                        'mt-2 text-xs font-bold text-emerald-700 flex items-center gap-1';

                    itemSelectionStatus.innerHTML = `
                <i class="fa-solid fa-circle-check"></i>

                <span>
                    تم اختيار الصنف الموجود
                    (${escapeHtml(getCategoryLabel(item.category))})
                </span>
            `;
                }

                /* Clear results */
                if (itemSearchResultsList) {
                    itemSearchResultsList.innerHTML = '';
                }

                if (itemNoResults) {
                    itemNoResults.classList.add('hidden');
                }

                closeItemSearch();
            }


            /* =========================================================
               RENDER ITEM RESULT
            ========================================================= */

            function renderItemResult(item, index) {

                if (!itemSearchResultsList) {
                    return;
                }

                const button = document.createElement('button');

                button.type = 'button';

                button.dataset.index = index;

                button.className = `
            w-full
            text-right
            px-4
            py-3
            hover:bg-gray-50
            transition
            border-b
            border-gray-100
            last:border-b-0
            flex
            items-center
            justify-between
            gap-3
        `;

                button.innerHTML = `
            <div class="flex items-center gap-3 min-w-0">

                <div
                    class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center shrink-0"
                >
                    <i class="fa-solid fa-box"></i>
                </div>

                <div class="min-w-0">

                    <div class="font-bold text-sm text-gray-800 truncate">
                        ${escapeHtml(item.name)}
                    </div>

                    <div class="text-[11px] text-gray-500 mt-0.5">
                        ${escapeHtml(getCategoryLabel(item.category))}
                    </div>

                </div>

            </div>

            <div class="text-[#8A704C] text-xs font-bold shrink-0">

                اختيار

                <i class="fa-solid fa-chevron-left mr-1"></i>

            </div>
        `;

                button.addEventListener('click', function() {
                    selectExistingItem(item);
                });

                itemSearchResultsList.appendChild(button);
            }


            /* =========================================================
               RENDER "NEW ITEM" MESSAGE
            ========================================================= */

            function renderNewItemMessage(query) {

                if (!itemSearchResultsList) {
                    return;
                }

                itemSearchResultsList.innerHTML = '';

                const container = document.createElement('div');

                container.className =
                    'px-4 py-4 text-sm text-gray-500';

                container.innerHTML = `
            <div class="flex items-center gap-3">

                <div
                    class="w-10 h-10 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center shrink-0"
                >
                    <i class="fa-solid fa-plus"></i>
                </div>

                <div>

                    <div class="font-bold text-gray-700">
                        لا يوجد صنف مطابق
                    </div>

                    <div class="text-xs text-gray-500 mt-1">
                        سيتم إنشاء صنف جديد باسم
                        <span class="font-bold text-[#8A704C]">
                            "${escapeHtml(query)}"
                        </span>
                    </div>

                </div>

            </div>
        `;

                itemSearchResultsList.appendChild(container);
            }


            /* =========================================================
               SEARCH EXISTING ITEMS
            ========================================================= */

            function searchExistingItems() {

                if (!itemNameInput || !itemSearchResultsList) {
                    return;
                }

                const query = normalizeSearchText(itemNameInput.value);

                /*
                 * Every manual input means that the user is no longer
                 * relying on the previously selected existing item.
                 */
                clearExistingItemSelection();

                activeItemIndex = -1;

                itemSearchResultsList.innerHTML = '';

                /* Empty */
                if (!query) {

                    itemNoResults?.classList.add('hidden');

                    closeItemSearch();

                    return;
                }

                /* ---------------------------------------------------------
                   Filter
                --------------------------------------------------------- */

                const results = existingItems
                    .filter(item => {

                        const name = normalizeSearchText(item.name);

                        return name.includes(query);
                    })
                    .slice(0, 10);


                /* ---------------------------------------------------------
                   Results found
                --------------------------------------------------------- */

                if (results.length > 0) {

                    itemNoResults?.classList.add('hidden');

                    results.forEach((item, index) => {

                        renderItemResult(item, index);

                    });

                } else {

                    itemNoResults?.classList.add('hidden');

                    renderNewItemMessage(query);
                }

                openItemSearch();
            }


            /* =========================================================
               ITEM NAME INPUT
            ========================================================= */

            itemNameInput?.addEventListener('input', function() {

                clearTimeout(itemSearchTimer);

                itemSearchTimer = setTimeout(function() {
                    searchExistingItems();
                }, 120);
            });


            /* =========================================================
               ITEM NAME FOCUS
            ========================================================= */

            itemNameInput?.addEventListener('focus', function() {

                if (this.value.trim()) {
                    searchExistingItems();
                }
            });


            /* =========================================================
               ITEM NAME BLUR
            ========================================================= */

            itemNameInput?.addEventListener('blur', function() {

                /*
                 * Delay closing to allow click on result.
                 */
                setTimeout(function() {
                    closeItemSearch();
                }, 180);
            });


            /* =========================================================
               KEYBOARD NAVIGATION
               
               ArrowDown
               ArrowUp
               Enter
               Escape
            ========================================================= */

            itemNameInput?.addEventListener('keydown', function(event) {

                if (!itemSearchResults ||
                    itemSearchResults.classList.contains('hidden')) {
                    return;
                }

                const resultButtons =
                    itemSearchResultsList?.querySelectorAll('button') || [];

                if (!resultButtons.length) {
                    return;
                }


                /* ---------------------------------------------------------
                   Arrow Down
                --------------------------------------------------------- */

                if (event.key === 'ArrowDown') {

                    event.preventDefault();

                    activeItemIndex++;

                    if (activeItemIndex >= resultButtons.length) {
                        activeItemIndex = 0;
                    }

                    updateActiveItemResult(resultButtons);

                    return;
                }


                /* ---------------------------------------------------------
                   Arrow Up
                --------------------------------------------------------- */

                if (event.key === 'ArrowUp') {

                    event.preventDefault();

                    activeItemIndex--;

                    if (activeItemIndex < 0) {
                        activeItemIndex = resultButtons.length - 1;
                    }

                    updateActiveItemResult(resultButtons);

                    return;
                }


                /* ---------------------------------------------------------
                   Enter
                --------------------------------------------------------- */

                if (event.key === 'Enter') {

                    if (
                        activeItemIndex >= 0 &&
                        resultButtons[activeItemIndex]
                    ) {

                        event.preventDefault();

                        resultButtons[activeItemIndex].click();
                    }

                    return;
                }


                /* ---------------------------------------------------------
                   Escape
                --------------------------------------------------------- */

                if (event.key === 'Escape') {

                    closeItemSearch();

                    return;
                }
            });


            /* =========================================================
               UPDATE ACTIVE SEARCH RESULT
            ========================================================= */

            function updateActiveItemResult(buttons) {

                buttons.forEach((button, index) => {

                    if (index === activeItemIndex) {

                        button.classList.add('bg-gray-50');

                        button.scrollIntoView({
                            block: 'nearest'
                        });

                    } else {

                        button.classList.remove('bg-gray-50');
                    }

                });
            }


            /* =========================================================
               CATEGORY CHANGE
               
               If the user changes category after selecting an existing
               item, the existing selection is invalidated.
            ========================================================= */

            function handleCategoryChange() {

                const category = categorySelect?.value;

                if (!category) {
                    return;
                }


                /* ---------------------------------------------------------
                   Existing selected item
                --------------------------------------------------------- */

                if (
                    existingItemIdInput?.value &&
                    itemNameInput?.value
                ) {

                    const selectedItem =
                        existingItems.find(
                            item =>
                            String(item.id) ===
                            String(existingItemIdInput.value)
                        );

                    if (
                        selectedItem &&
                        selectedItem.category !== category
                    ) {

                        clearExistingItemSelection();
                    }
                }


                const isFinishedProduct =
                    category === 'finished_product';


                /* =========================================================
                   FINISHED PRODUCT
                ========================================================= */

                if (isFinishedProduct) {

                    finishedProductSection?.classList.remove('hidden');

                    bulkSection?.classList.add('hidden');

                    individualSection?.classList.add('hidden');

                    standardSettingsContainer?.classList.add('hidden');


                    /* Backend expected values */

                    if (mainUnitType) {
                        mainUnitType.value = 'piece';
                    }

                    if (mainTrackingType) {
                        mainTrackingType.value = 'bulk';
                    }


                    /* Finished product fields */

                    setFinishedProductFields(true);


                    /* Normal stock fields */

                    setBulkFieldsEnabled(false);

                    setIndividualFieldsEnabled(false);


                    /* Sync colors */

                    syncFinishedProductColor();

                    return;
                }


                /* =========================================================
                   NORMAL ITEMS
                ========================================================= */

                finishedProductSection?.classList.add('hidden');

                standardSettingsContainer?.classList.remove('hidden');

                setFinishedProductFields(false);

                setBulkFieldsEnabled(false);

                setIndividualFieldsEnabled(false);

                handleTrackingChange();
            }


            /* =========================================================
               FINISHED PRODUCT FIELD STATE
            ========================================================= */

            function setFinishedProductFields(enabled) {

                if (!fpSection) {
                    return;
                }

                const fields =
                    fpSection.querySelectorAll(
                        'input, select, textarea'
                    );

                fields.forEach(field => {

                    if (enabled) {

                        field.disabled = false;

                        const name =
                            field.getAttribute('name') || '';

                        const requiredFields = [
                            'items[0][pant_type_id]',
                            'items[0][type_id]',
                            'items[0][color_id]',
                            'items[0][quantity]',
                            'items[0][price]'
                        ];

                        field.required =
                            requiredFields.includes(name);

                    } else {

                        field.required = false;

                        field.disabled = true;
                    }

                });
            }


            /* =========================================================
               BULK FIELDS
            ========================================================= */

            function setBulkFieldsEnabled(enabled) {

                if (!bulkSection) {
                    return;
                }

                const fields =
                    bulkSection.querySelectorAll(
                        'input, select, textarea'
                    );

                fields.forEach(field => {

                    if (enabled) {

                        field.disabled = false;

                    } else {

                        field.disabled = true;

                        field.required = false;
                    }
                });
            }


            /* =========================================================
               INDIVIDUAL FIELDS
            ========================================================= */

            function setIndividualFieldsEnabled(enabled) {

                if (!unitsContainer) {
                    return;
                }

                unitsContainer
                    .querySelectorAll('input, select, textarea')
                    .forEach(field => {

                        field.disabled = !enabled;

                        if (!enabled) {
                            field.required = false;
                        }
                    });
            }


            /* =========================================================
               TRACKING TYPE CHANGE
            ========================================================= */

            function handleTrackingChange() {

                if (!categorySelect) {
                    return;
                }

                if (categorySelect.value === 'finished_product') {
                    return;
                }

                const trackingType =
                    visibleTrackingType?.value || 'bulk';


                /* Backend field */

                if (mainTrackingType) {
                    mainTrackingType.value = trackingType;
                }


                /* =====================================================
                   BULK
                ===================================================== */

                if (trackingType === 'bulk') {

                    bulkSection?.classList.remove('hidden');

                    individualSection?.classList.add('hidden');

                    setBulkFieldsEnabled(true);

                    setIndividualFieldsEnabled(false);


                    if (bulkUnitSelect) {
                        bulkUnitSelect.required = true;
                    }

                    if (bulkQuantity) {
                        bulkQuantity.required = true;
                    }

                    if (bulkUnitCost) {
                        bulkUnitCost.required = true;
                    }


                    if (mainUnitType && bulkUnitSelect) {

                        mainUnitType.value =
                            bulkUnitSelect.value || 'piece';
                    }


                    updateIndividualUnitTypes();

                    return;
                }


                /* =====================================================
                   INDIVIDUAL
                ===================================================== */

                if (trackingType === 'individual') {

                    bulkSection?.classList.add('hidden');

                    individualSection?.classList.remove('hidden');

                    setBulkFieldsEnabled(false);

                    setIndividualFieldsEnabled(true);


                    if (bulkUnitSelect) {
                        bulkUnitSelect.required = false;
                    }

                    if (bulkQuantity) {
                        bulkQuantity.required = false;
                    }

                    if (bulkUnitCost) {
                        bulkUnitCost.required = false;
                    }


                    if (mainUnitType && bulkUnitSelect) {

                        mainUnitType.value =
                            bulkUnitSelect.value || 'piece';
                    }


                    const rows =
                        unitsContainer?.querySelectorAll('.unit-row') || [];


                    if (rows.length === 0) {

                        const row =
                            createUnitRow(unitIndex);

                        if (row) {
                            unitIndex++;
                        }
                    }


                    updateIndividualUnitTypes();
                }
            }


            /* =========================================================
               BULK UNIT TYPE CHANGE
            ========================================================= */

            bulkUnitSelect?.addEventListener(
                'change',
                function() {

                    if (categorySelect?.value === 'finished_product') {
                        return;
                    }

                    if (mainUnitType) {
                        mainUnitType.value = this.value;
                    }

                    updateIndividualUnitTypes();
                }
            );


            /* =========================================================
               TRACKING SELECT
            ========================================================= */

            visibleTrackingType?.addEventListener(
                'change',
                handleTrackingChange
            );


            /* =========================================================
               CATEGORY SELECT
            ========================================================= */

            categorySelect?.addEventListener(
                'change',
                handleCategoryChange
            );


            /* =========================================================
               FINISHED PRODUCT COLOR SYNC
            ========================================================= */

            function syncFinishedProductColor() {

                if (!mainColorSelect || !fpColorSelect) {
                    return;
                }

                if (mainColorSelect.value) {

                    fpColorSelect.value =
                        mainColorSelect.value;
                }
            }


            mainColorSelect?.addEventListener(
                'change',
                function() {

                    if (
                        categorySelect?.value ===
                        'finished_product'
                    ) {

                        syncFinishedProductColor();
                    }
                }
            );


            fpColorSelect?.addEventListener(
                'change',
                function() {

                    if (
                        categorySelect?.value ===
                        'finished_product'
                    ) {

                        if (mainColorSelect) {

                            mainColorSelect.value =
                                this.value;
                        }
                    }
                }
            );


            /* =========================================================
               FINISHED PRODUCT TOTAL
            ========================================================= */

            function calculateFPTotal() {

                if (!fpQty || !fpPrice || !fpTotalDisplay) {
                    return;
                }

                const quantity =
                    parseFloat(fpQty.value) || 0;

                const price =
                    parseFloat(fpPrice.value) || 0;

                const total =
                    quantity * price;

                fpTotalDisplay.textContent =
                    total.toFixed(2);
            }


            [fpQty, fpPrice].forEach(input => {

                input?.addEventListener(
                    'input',
                    calculateFPTotal
                );

            });


            /* =========================================================
               UNIT TOTAL
            ========================================================= */

            function calculateUnitTotal(row) {

                if (!row) {
                    return;
                }

                const quantityInput =
                    row.querySelector('.unit-quantity');

                const costInput =
                    row.querySelector('.unit-cost');

                const totalDisplay =
                    row.querySelector('.unit-total');


                if (
                    !quantityInput ||
                    !costInput ||
                    !totalDisplay
                ) {
                    return;
                }


                const quantity =
                    parseFloat(quantityInput.value) || 0;

                const cost =
                    parseFloat(costInput.value) || 0;


                totalDisplay.textContent =
                    (quantity * cost).toFixed(2);
            }


            /* =========================================================
               CREATE INDIVIDUAL UNIT ROW
            ========================================================= */

            function createUnitRow(index, values = {}) {

                if (!unitsContainer) {
                    return null;
                }

                const row =
                    document.createElement('div');


                row.className =
                    'unit-row bg-gray-50 border border-gray-200 rounded-2xl p-4 shadow-sm animate-fade-in';


                row.dataset.index = index;


                const currentUnitType =
                    bulkUnitSelect?.value ||
                    mainUnitType?.value ||
                    'piece';


                row.innerHTML = `
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">

                <div class="flex items-center gap-2">

                    <div class="w-8 h-8 rounded-lg bg-[#8A704C] text-white flex items-center justify-center">
                        <i class="fa-solid fa-box"></i>
                    </div>

                    <div>

                        <h4 class="font-bold text-gray-800 text-sm">
                            الوحدة #${index + 1}
                        </h4>

                        <p class="text-[10px] text-gray-500">
                            بيانات الوحدة / التوبة
                        </p>

                    </div>

                </div>


                <button
                    type="button"
                    class="remove-unit-btn w-9 h-9 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition flex items-center justify-center"
                    title="حذف الوحدة"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>

            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Unit Type -->

                <input
                    type="hidden"
                    name="units[${index}][unit_type]"
                    class="unit-type-hidden"
                    value="${escapeHtml(values.unit_type || currentUnitType)}"
                >


                <!-- Unit Code -->

                <div>

                    <label class="block text-xs font-bold text-gray-700 mb-1.5">

                        كود الوحدة (الباركود)

                        <span class="text-red-500">*</span>

                    </label>


                    <input
                        type="text"
                        name="units[${index}][unit_code]"
                        value="${escapeHtml(values.unit_code || '')}"
                        required
                        maxlength="255"
                        autocomplete="off"
                        placeholder="مثال: TUBE-001"
                        class="unit-code w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                    >

                </div>


                <!-- Quantity -->

                <div>

                    <label class="block text-xs font-bold text-gray-700 mb-1.5">

                        الكمية

                        <span class="text-red-500">*</span>

                    </label>


                    <input
                        type="number"
                        name="units[${index}][quantity]"
                        value="${escapeHtml(values.quantity ?? '')}"
                        required
                        min="0.001"
                        step="0.001"
                        inputmode="decimal"
                        placeholder="0.000"
                        class="unit-quantity w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                    >

                </div>


                <!-- Unit Cost -->

                <div>

                    <label class="block text-xs font-bold text-gray-700 mb-1.5">

                        تكلفة الوحدة

                        <span class="text-red-500">*</span>

                    </label>


                    <input
                        type="number"
                        name="units[${index}][unit_cost]"
                        value="${escapeHtml(values.unit_cost ?? '')}"
                        required
                        min="0"
                        step="0.01"
                        inputmode="decimal"
                        placeholder="0.00"
                        class="unit-cost w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                    >

                </div>


                <!-- Total -->

                <div>

                    <label class="block text-xs font-bold text-emerald-600 mb-1.5">

                        إجمالي الوحدة

                    </label>


                    <div
                        class="unit-total w-full h-[46px] flex items-center justify-center bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 font-black font-mono"
                    >
                        0.00
                    </div>

                </div>


                <!-- Notes -->

                <div class="md:col-span-2 lg:col-span-4">

                    <label class="block text-xs font-bold text-gray-600 mb-1.5">

                        ملاحظات

                    </label>


                    <input
                        type="text"
                        name="units[${index}][notes]"
                        value="${escapeHtml(values.notes || '')}"
                        maxlength="5000"
                        placeholder="أي ملاحظات خاصة بهذه الوحدة..."
                        class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                    >

                </div>

            </div>
        `;


                unitsContainer.appendChild(row);


                /* =====================================================
                   EVENTS
                ===================================================== */

                const quantityInput =
                    row.querySelector('.unit-quantity');

                const costInput =
                    row.querySelector('.unit-cost');


                quantityInput?.addEventListener(
                    'input',
                    function() {
                        calculateUnitTotal(row);
                    }
                );


                costInput?.addEventListener(
                    'input',
                    function() {
                        calculateUnitTotal(row);
                    }
                );


                row.querySelector('.remove-unit-btn')
                    ?.addEventListener(
                        'click',
                        function() {

                            const rows =
                                unitsContainer.querySelectorAll('.unit-row');


                            if (rows.length === 1) {

                                if (
                                    !confirm(
                                        'هذه هي الوحدة الوحيدة الموجودة، هل تريد حذفها؟'
                                    )
                                ) {
                                    return;
                                }
                            }


                            row.remove();

                            renumberUnits();

                            updateIndividualUnitTypes();
                        }
                    );


                calculateUnitTotal(row);

                return row;
            }


            /* =========================================================
               UPDATE UNIT TYPES
            ========================================================= */

            function updateIndividualUnitTypes() {

                const selectedUnit =
                    bulkUnitSelect?.value ||
                    mainUnitType?.value ||
                    'piece';


                unitsContainer
                    ?.querySelectorAll('.unit-type-hidden')
                    .forEach(input => {

                        input.value =
                            selectedUnit;
                    });
            }


            /* =========================================================
               RENUMBER UNITS
            ========================================================= */

            function renumberUnits() {

                if (!unitsContainer) {
                    return;
                }


                const rows =
                    unitsContainer.querySelectorAll('.unit-row');


                rows.forEach((row, newIndex) => {

                    row.dataset.index =
                        newIndex;


                    const title =
                        row.querySelector('h4');


                    if (title) {

                        title.textContent =
                            `الوحدة #${newIndex + 1}`;
                    }


                    row.querySelectorAll('input')
                        .forEach(input => {

                            const name =
                                input.getAttribute('name');


                            if (!name) {
                                return;
                            }


                            input.setAttribute(
                                'name',
                                name.replace(
                                    /units\[\d+\]/,
                                    `units[${newIndex}]`
                                )
                            );
                        });
                });


                unitIndex =
                    rows.length;
            }


            /* =========================================================
               ADD UNIT
            ========================================================= */

            addUnitRowBtn?.addEventListener(
                'click',
                function() {

                    if (
                        categorySelect?.value ===
                        'finished_product'
                    ) {
                        return;
                    }


                    const row =
                        createUnitRow(unitIndex);


                    if (!row) {
                        return;
                    }


                    unitIndex++;


                    updateIndividualUnitTypes();


                    setTimeout(function() {

                        row.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });


                        row.querySelector('.unit-code')?.focus();

                    }, 100);
                }
            );


            /* =========================================================
               VALIDATE INDIVIDUAL UNITS
            ========================================================= */

            function validateIndividualUnits() {

                if (!unitsContainer) {
                    return true;
                }


                const rows =
                    unitsContainer.querySelectorAll('.unit-row');


                if (rows.length === 0) {

                    showAlert(
                        'يجب إضافة وحدة واحدة على الأقل.',
                        'error'
                    );

                    return false;
                }


                const codes =
                    new Set();


                for (let i = 0; i < rows.length; i++) {

                    const row =
                        rows[i];


                    const code =
                        row.querySelector('.unit-code')
                        ?.value
                        .trim();


                    const quantity =
                        parseFloat(
                            row.querySelector('.unit-quantity')
                            ?.value
                        );


                    const unitCost =
                        parseFloat(
                            row.querySelector('.unit-cost')
                            ?.value
                        );


                    if (!code) {

                        showAlert(
                            `يرجى إدخال كود الوحدة رقم ${i + 1}.`,
                            'error'
                        );

                        row.querySelector('.unit-code')?.focus();

                        return false;
                    }


                    if (
                        codes.has(
                            code.toLowerCase()
                        )
                    ) {

                        showAlert(
                            `كود الوحدة "${code}" مكرر. يجب أن يكون لكل وحدة كود مختلف.`,
                            'error'
                        );

                        row.querySelector('.unit-code')?.focus();

                        return false;
                    }


                    codes.add(
                        code.toLowerCase()
                    );


                    if (
                        !Number.isFinite(quantity) ||
                        quantity <= 0
                    ) {

                        showAlert(
                            `كمية الوحدة رقم ${i + 1} يجب أن تكون أكبر من صفر.`,
                            'error'
                        );

                        row.querySelector('.unit-quantity')?.focus();

                        return false;
                    }


                    if (
                        !Number.isFinite(unitCost) ||
                        unitCost < 0
                    ) {

                        showAlert(
                            `تكلفة الوحدة رقم ${i + 1} غير صحيحة.`,
                            'error'
                        );

                        row.querySelector('.unit-cost')?.focus();

                        return false;
                    }
                }


                return true;
            }


            /* =========================================================
               VALIDATE FINISHED PRODUCT
            ========================================================= */

            function validateFinishedProduct() {

                if (!fpSection) {
                    return false;
                }


                const requiredFields = [

                    {
                        element: fpPantType,
                        message: 'يرجى اختيار نوع البنطلون.'
                    },

                    {
                        element: fpProductType,
                        message: 'يرجى اختيار نوع المنتج.'
                    },

                    {
                        element: fpColor,
                        message: 'يرجى اختيار لون المنتج.'
                    },

                    {
                        element: fpQty,
                        message: 'يرجى إدخال كمية المنتج الجاهز.'
                    },

                    {
                        element: fpPrice,
                        message: 'يرجى إدخال تكلفة قطعة المنتج الجاهز.'
                    }

                ];


                for (const item of requiredFields) {

                    if (
                        !item.element ||
                        item.element.disabled
                    ) {
                        continue;
                    }


                    const value =
                        item.element.value?.trim();


                    if (!value) {

                        showAlert(
                            item.message,
                            'error'
                        );

                        item.element.focus();

                        return false;
                    }
                }


                const quantity =
                    parseFloat(fpQty?.value);


                const price =
                    parseFloat(fpPrice?.value);


                if (
                    !Number.isFinite(quantity) ||
                    quantity <= 0
                ) {

                    showAlert(
                        'كمية المنتج الجاهز يجب أن تكون أكبر من صفر.',
                        'error'
                    );

                    fpQty?.focus();

                    return false;
                }


                if (
                    !Number.isFinite(price) ||
                    price < 0
                ) {

                    showAlert(
                        'تكلفة قطعة المنتج الجاهز غير صحيحة.',
                        'error'
                    );

                    fpPrice?.focus();

                    return false;
                }


                return true;
            }


            /* =========================================================
               COMPLETE FORM VALIDATION
            ========================================================= */

            function validateForm() {

                const category =
                    categorySelect?.value;


                if (!category) {

                    showAlert(
                        'يرجى اختيار فئة الصنف.',
                        'error'
                    );

                    categorySelect?.focus();

                    return false;
                }


                /* -----------------------------------------------------
                   Common fields
                ----------------------------------------------------- */

                const commonFields = [

                    itemNameInput,

                    categorySelect,

                    mainColorSelect,

                    document.getElementById('minimum_stock')

                ];


                for (const field of commonFields) {

                    if (
                        !field ||
                        field.disabled
                    ) {
                        continue;
                    }


                    if (!field.checkValidity()) {

                        field.reportValidity();

                        return false;
                    }
                }


                /* =====================================================
                   FINISHED PRODUCT
                ===================================================== */

                if (category === 'finished_product') {

                    if (!validateFinishedProduct()) {
                        return false;
                    }


                    if (mainUnitType) {
                        mainUnitType.value = 'piece';
                    }


                    if (mainTrackingType) {
                        mainTrackingType.value = 'bulk';
                    }


                    syncFinishedProductColor();


                    return true;
                }


                /* =====================================================
                   NORMAL ITEM
                ===================================================== */

                const trackingType =
                    visibleTrackingType?.value;


                if (!trackingType) {

                    showAlert(
                        'يرجى اختيار طريقة تتبع المخزون.',
                        'error'
                    );

                    visibleTrackingType?.focus();

                    return false;
                }


                /* =====================================================
                   BULK
                ===================================================== */

                if (trackingType === 'bulk') {

                    const unit =
                        bulkUnitSelect?.value;


                    const quantity =
                        parseFloat(
                            bulkQuantity?.value
                        );


                    const cost =
                        parseFloat(
                            bulkUnitCost?.value
                        );


                    if (!unit) {

                        showAlert(
                            'يرجى اختيار وحدة القياس.',
                            'error'
                        );

                        bulkUnitSelect?.focus();

                        return false;
                    }


                    if (
                        !Number.isFinite(quantity) ||
                        quantity <= 0
                    ) {

                        showAlert(
                            'الكمية يجب أن تكون أكبر من صفر.',
                            'error'
                        );

                        bulkQuantity?.focus();

                        return false;
                    }


                    if (
                        !Number.isFinite(cost) ||
                        cost < 0
                    ) {

                        showAlert(
                            'تكلفة الوحدة غير صحيحة.',
                            'error'
                        );

                        bulkUnitCost?.focus();

                        return false;
                    }


                    if (mainUnitType) {
                        mainUnitType.value = unit;
                    }


                    if (mainTrackingType) {
                        mainTrackingType.value = 'bulk';
                    }


                    return true;
                }


                /* =====================================================
                   INDIVIDUAL
                ===================================================== */

                if (trackingType === 'individual') {

                    if (!validateIndividualUnits()) {
                        return false;
                    }


                    if (mainTrackingType) {
                        mainTrackingType.value = 'individual';
                    }


                    if (mainUnitType) {

                        mainUnitType.value =
                            bulkUnitSelect?.value ||
                            'piece';
                    }


                    updateIndividualUnitTypes();


                    return true;
                }


                return false;
            }


            /* =========================================================
               BUILD REVIEW - FINISHED PRODUCT
            ========================================================= */

            function buildFinishedProductReview() {

                const itemName =
                    itemNameInput?.value || '-';


                const quantity =
                    parseFloat(fpQty?.value) || 0;


                const price =
                    parseFloat(fpPrice?.value) || 0;


                const total =
                    quantity * price;


                return `
            <div class="bg-gradient-to-r from-[#8A704C] to-[#C7A977] p-6 rounded-2xl text-white shadow-xl relative overflow-hidden">

                <div class="absolute -right-4 -top-4 opacity-10 text-8xl">
                    <i class="fa-solid fa-shirt"></i>
                </div>


                <div class="relative z-10">

                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold mb-3 inline-block">
                        ✨ منتج مصنع جاهز
                    </span>


                    <h2 class="text-2xl font-black mb-1">
                        ${escapeHtml(itemName)}
                    </h2>


                    <p class="text-white/80 text-sm mb-4">

                        النوع:
                        ${escapeHtml(getSelectText(fpProductType))}

                        |

                        البنطلون:
                        ${escapeHtml(getSelectText(fpPantType))}

                        |

                        الستايل:
                        ${escapeHtml(getSelectText(fpStyle))}

                        |

                        المقاس:
                        ${escapeHtml(getSelectText(fpSize))}

                        |

                        الكم:
                        ${escapeHtml(getSelectText(fpSleeveType))}

                    </p>


                    <div class="grid grid-cols-2 gap-4 bg-black/20 p-4 rounded-xl">

                        <div>

                            <div class="text-white/60 text-[10px]">
                                الكمية × السعر
                            </div>


                            <div class="font-bold font-mono text-lg">

                                ${quantity}
                                قطعة ×
                                ${price.toFixed(2)}
                                ج.م

                            </div>

                        </div>


                        <div class="text-left">

                            <div class="text-white/60 text-[10px]">
                                الإجمالي
                            </div>


                            <div class="font-black text-2xl text-yellow-300 font-mono">

                                ${total.toFixed(2)} ج.م

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        `;
            }


            /* =========================================================
               BUILD REVIEW - INDIVIDUAL
            ========================================================= */

            function buildIndividualReview() {

                const rows =
                    Array.from(
                        unitsContainer?.querySelectorAll('.unit-row') || []
                    );


                let totalQuantity = 0;

                let totalCost = 0;


                const unitsHtml =
                    rows
                    .map((row, index) => {

                        const code =
                            row.querySelector('.unit-code')
                            ?.value || '-';


                        const quantity =
                            parseFloat(
                                row.querySelector('.unit-quantity')
                                ?.value
                            ) || 0;


                        const cost =
                            parseFloat(
                                row.querySelector('.unit-cost')
                                ?.value
                            ) || 0;


                        const total =
                            quantity * cost;


                        totalQuantity += quantity;

                        totalCost += total;


                        return `
                        <div class="flex items-center justify-between py-3 border-b last:border-0">

                            <div>

                                <div class="font-bold text-sm">
                                    #${index + 1}
                                </div>

                                <div class="text-[10px] text-gray-500">
                                    ${escapeHtml(code)}
                                </div>

                            </div>


                            <div class="text-left">

                                <div class="font-bold">
                                    ${quantity.toFixed(3)}
                                </div>

                                <div class="text-[10px] text-gray-500">
                                    ${cost.toFixed(2)} ج.م
                                </div>

                            </div>


                            <div class="text-left">

                                <div class="text-[10px] text-gray-500">
                                    الإجمالي
                                </div>

                                <div class="font-black text-emerald-700">
                                    ${total.toFixed(2)} ج.م
                                </div>

                            </div>

                        </div>
                    `;

                    })
                    .join('');


                return `
            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200">

                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">

                    <div>

                        <h4 class="font-black text-gray-800 text-xl">
                            ${escapeHtml(itemNameInput?.value || '-')}
                        </h4>

                        <p class="text-xs text-gray-500 mt-1">
                            مخزون منفرد / توبات
                        </p>

                    </div>


                    <div class="w-12 h-12 rounded-xl bg-[#8A704C] text-white flex items-center justify-center">

                        <i class="fa-solid fa-barcode text-lg"></i>

                    </div>

                </div>


                <div class="grid grid-cols-2 gap-3 mb-4">

                    <div class="bg-white p-3 rounded-xl border">

                        <span class="text-[10px] text-gray-500 block">
                            الفئة
                        </span>

                        <strong>
                            ${escapeHtml(
                                getSelectText(categorySelect)
                            )}
                        </strong>

                    </div>


                    <div class="bg-white p-3 rounded-xl border">

                        <span class="text-[10px] text-gray-500 block">
                            اللون
                        </span>

                        <strong>
                            ${escapeHtml(
                                getSelectText(mainColorSelect)
                            )}
                        </strong>

                    </div>


                    <div class="bg-white p-3 rounded-xl border">

                        <span class="text-[10px] text-gray-500 block">
                            عدد الوحدات
                        </span>

                        <strong>
                            ${rows.length}
                        </strong>

                    </div>


                    <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-200">

                        <span class="text-[10px] text-emerald-600 block">
                            إجمالي الكمية
                        </span>

                        <strong class="text-emerald-700">
                            ${totalQuantity.toFixed(3)}
                        </strong>

                    </div>

                </div>


                <div class="bg-white rounded-xl border p-4">

                    <div class="flex justify-between items-center mb-3">

                        <span class="font-bold">
                            الوحدات
                        </span>

                        <span class="text-emerald-600 font-black">
                            ${totalCost.toFixed(2)} ج.م
                        </span>

                    </div>


                    ${unitsHtml}

                </div>

            </div>
        `;
            }


            /* =========================================================
               BUILD REVIEW - BULK
            ========================================================= */

            function buildBulkReview() {

                const quantity =
                    parseFloat(
                        bulkQuantity?.value
                    ) || 0;


                const unitCost =
                    parseFloat(
                        bulkUnitCost?.value
                    ) || 0;


                const total =
                    quantity * unitCost;


                const unitName =
                    getSelectText(
                        bulkUnitSelect
                    );


                const categoryName =
                    getSelectText(
                        categorySelect
                    );


                return `
            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200">

                <div class="flex items-center gap-3 mb-5">

                    <div class="w-12 h-12 rounded-xl bg-[#8A704C] text-white flex items-center justify-center">

                        <i class="fa-solid fa-boxes-stacked"></i>

                    </div>


                    <div>

                        <h4 class="font-black text-gray-800 text-xl">

                            ${escapeHtml(
                                itemNameInput?.value || '-'
                            )}

                        </h4>


                        <p class="text-xs text-gray-500">
                            إدخال مخزون إجمالي
                        </p>

                    </div>

                </div>


                <div class="grid grid-cols-2 gap-3">

                    <div class="bg-white p-4 rounded-xl border">

                        <span class="text-[10px] text-gray-500 block">
                            الفئة
                        </span>

                        <strong>
                            ${escapeHtml(categoryName)}
                        </strong>

                    </div>


                    <div class="bg-white p-4 rounded-xl border">

                        <span class="text-[10px] text-gray-500 block">
                            اللون
                        </span>

                        <strong>

                            ${escapeHtml(
                                getSelectText(mainColorSelect)
                            )}

                        </strong>

                    </div>


                    <div class="bg-white p-4 rounded-xl border">

                        <span class="text-[10px] text-gray-500 block">
                            الكمية
                        </span>

                        <strong>

                            ${quantity.toFixed(3)}
                            ${escapeHtml(unitName)}

                        </strong>

                    </div>


                    <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200">

                        <span class="text-[10px] text-emerald-600 block">
                            الإجمالي
                        </span>

                        <strong class="text-emerald-700 text-lg">

                            ${total.toFixed(2)} ج.م

                        </strong>

                    </div>

                </div>


                <div class="mt-4 p-4 rounded-xl bg-white border">

                    <span class="text-xs text-gray-500">
                        طريقة التتبع
                    </span>


                    <div class="font-bold text-[#8A704C] mt-1">

                        ${escapeHtml(
                            getSelectText(
                                visibleTrackingType
                            )
                        )}

                    </div>

                </div>

            </div>
        `;
            }


            /* =========================================================
               OPEN REVIEW MODAL
            ========================================================= */

            openReviewModalBtn?.addEventListener(
                'click',
                function() {

                    if (!validateForm()) {
                        return;
                    }


                    const category =
                        categorySelect?.value;


                    let html = '';


                    if (
                        category ===
                        'finished_product'
                    ) {

                        html =
                            buildFinishedProductReview();

                    } else if (
                        visibleTrackingType?.value ===
                        'individual'
                    ) {

                        html =
                            buildIndividualReview();

                    } else {

                        html =
                            buildBulkReview();
                    }


                    if (modalContentArea) {

                        modalContentArea.innerHTML =
                            html;
                    }


                    showModal(reviewModal);
                }
            );


            /* =========================================================
               MODAL HELPERS
            ========================================================= */

            function showModal(modal) {

                if (!modal) {
                    return;
                }


                if (
                    typeof openModal ===
                    'function'
                ) {

                    openModal(modal);

                    return;
                }


                modal.classList.remove('hidden');

                document.body.classList.add(
                    'overflow-hidden'
                );
            }


            function hideModal(modal) {

                if (!modal) {
                    return;
                }


                if (
                    typeof closeModal ===
                    'function'
                ) {

                    closeModal(modal);

                    return;
                }


                modal.classList.add('hidden');

                document.body.classList.remove(
                    'overflow-hidden'
                );
            }


            /* =========================================================
               CLOSE REVIEW MODAL
            ========================================================= */

            closeReviewModalBtn?.addEventListener(
                'click',
                function() {

                    hideModal(reviewModal);
                }
            );


            cancelSubmitBtn?.addEventListener(
                'click',
                function() {

                    hideModal(reviewModal);
                }
            );


            /* =========================================================
               FINAL SUBMIT
            ========================================================= */

            confirmSubmitBtn?.addEventListener(
                'click',
                function() {

                    if (isSubmitting) {
                        return;
                    }


                    if (!validateForm()) {

                        hideModal(reviewModal);

                        return;
                    }


                    const category =
                        categorySelect?.value;


                    /* -----------------------------------------------------
                       Final synchronization
                    ----------------------------------------------------- */

                    if (
                        category ===
                        'finished_product'
                    ) {

                        if (mainUnitType) {
                            mainUnitType.value = 'piece';
                        }


                        if (mainTrackingType) {
                            mainTrackingType.value = 'bulk';
                        }


                        syncFinishedProductColor();

                    } else {

                        if (mainTrackingType) {

                            mainTrackingType.value =
                                visibleTrackingType?.value ||
                                'bulk';
                        }


                        if (mainUnitType) {

                            mainUnitType.value =
                                bulkUnitSelect?.value ||
                                'piece';
                        }


                        if (
                            mainTrackingType?.value ===
                            'individual'
                        ) {

                            updateIndividualUnitTypes();
                        }
                    }


                    /* -----------------------------------------------------
                       Submit
                    ----------------------------------------------------- */

                    isSubmitting = true;

                    this.disabled = true;


                    const icon =
                        document.getElementById(
                            'confirmBtnIcon'
                        );


                    const text =
                        document.getElementById(
                            'confirmBtnText'
                        );


                    if (icon) {

                        icon.className =
                            'fa-solid fa-spinner fa-spin';
                    }


                    if (text) {

                        text.textContent =
                            'جاري الحفظ...';
                    }


                    /*
                     * Native submit intentionally bypasses submit event.
                     */
                    inventoryForm.submit();
                }
            );


            /* =========================================================
               ENTER KEY
               
               Prevent accidental form submission.
            ========================================================= */

            inventoryForm.addEventListener(
                'keydown',
                function(event) {

                    if (
                        event.key === 'Enter' &&
                        event.target.tagName !== 'TEXTAREA'
                    ) {

                        if (
                            event.target.type !==
                            'submit'
                        ) {

                            event.preventDefault();
                        }
                    }
                }
            );


            /* =========================================================
               SAVE COLOR AJAX
            ========================================================= */

            saveColorBtn?.addEventListener(
                'click',
                async function() {

                    const btn = this;


                    const nameInput =
                        document.getElementById(
                            'new_color_name'
                        );


                    const identifierInput =
                        document.getElementById(
                            'new_color_identifier'
                        );


                    if (
                        !nameInput ||
                        !identifierInput
                    ) {
                        return;
                    }


                    const name =
                        nameInput.value.trim();


                    const identifier =
                        identifierInput.value.trim();


                    if (!name) {

                        showAlert(
                            'يرجى إدخال اسم اللون.',
                            'error'
                        );

                        nameInput.focus();

                        return;
                    }


                    if (!identifier) {

                        showAlert(
                            'يرجى إدخال معرف اللون.',
                            'error'
                        );

                        identifierInput.focus();

                        return;
                    }


                    btn.disabled = true;


                    const oldHtml =
                        btn.innerHTML;


                    btn.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                جاري الحفظ...
            `;


                    try {

                        const csrfInput =
                            inventoryForm.querySelector(
                                'input[name="_token"]'
                            );


                        const csrf =
                            csrfInput?.value;


                        if (!csrf) {

                            throw new Error(
                                'CSRF token not found.'
                            );
                        }


                        const response =
                            await fetch(
                                '{{ route('colors.store') }}', {
                                    method: 'POST',

                                    headers: {

                                        'Content-Type': 'application/json',

                                        'X-CSRF-TOKEN': csrf,

                                        'Accept': 'application/json',

                                        'X-Requested-With': 'XMLHttpRequest'
                                    },

                                    body: JSON.stringify({
                                        name: name,
                                        identifier: identifier
                                    })
                                }
                            );


                        let data = null;


                        try {

                            data =
                                await response.json();

                        } catch (jsonError) {

                            data = null;
                        }


                        /* =================================================
                           SUCCESS
                        ================================================= */

                        if (
                            response.ok &&
                            data?.success &&
                            data?.color
                        ) {

                            addColorOption(
                                mainColorSelect,
                                data.color
                            );


                            addColorOption(
                                fpColorSelect,
                                data.color
                            );


                            if (mainColorSelect) {

                                mainColorSelect.value =
                                    data.color.id;
                            }


                            if (fpColorSelect) {

                                fpColorSelect.value =
                                    data.color.id;
                            }


                            nameInput.value = '';

                            identifierInput.value = '';


                            hideModal(addColorModal);


                            showAlert(
                                'تم إضافة اللون بنجاح.',
                                'success'
                            );


                            return;
                        }


                        /* =================================================
                           VALIDATION ERROR
                        ================================================= */

                        if (
                            response.status === 422 &&
                            data?.errors
                        ) {

                            const messages = [];


                            Object.values(
                                    data.errors
                                )
                                .flat()
                                .forEach(message => {

                                    messages.push(message);

                                });


                            showAlert(
                                messages.join(' - ') ||
                                'بيانات اللون غير صحيحة.',
                                'error'
                            );


                            return;
                        }


                        showAlert(
                            data?.message ||
                            'تعذر حفظ اللون.',
                            'error'
                        );

                    } catch (error) {

                        console.error(
                            'Color save error:',
                            error
                        );


                        showAlert(
                            'حدث خطأ أثناء الاتصال بالخادم.',
                            'error'
                        );

                    } finally {

                        btn.disabled = false;

                        btn.innerHTML =
                            oldHtml;
                    }
                }
            );


            /* =========================================================
               ADD COLOR OPTION
            ========================================================= */

            function addColorOption(select, color) {

                if (!select || !color) {
                    return;
                }


                const existing =
                    Array.from(select.options)
                    .find(
                        option =>
                        String(option.value) ===
                        String(color.id)
                    );


                if (existing) {

                    existing.textContent =
                        color.name;

                    return;
                }


                const option =
                    document.createElement('option');


                option.value =
                    color.id;


                option.textContent =
                    color.name;


                select.appendChild(option);
            }


            /* =========================================================
               ALERT SYSTEM
            ========================================================= */

            function showAlert(
                message,
                type = 'success'
            ) {

                if (!alertsContainer) {
                    return;
                }


                const isSuccess =
                    type === 'success';


                const alert =
                    document.createElement('div');


                alert.className = `
            p-4
            rounded-xl
            border
            flex
            items-center
            justify-between
            shadow-sm
            animate-fade-in
            ${
                isSuccess
                    ? 'bg-emerald-100 border-emerald-300 text-emerald-800'
                    : 'bg-rose-100 border-rose-300 text-rose-800'
            }
        `;


                alert.innerHTML = `
            <div class="flex items-center gap-2">

                <i class="fa-solid ${
                    isSuccess
                        ? 'fa-circle-check text-emerald-600'
                        : 'fa-triangle-exclamation text-rose-600'
                } text-lg"></i>


                <span class="font-bold text-xs sm:text-sm">

                    ${escapeHtml(message)}

                </span>

            </div>


            <button
                type="button"
                class="alert-close text-gray-600 hover:text-gray-900"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>
        `;


                alertsContainer.appendChild(alert);


                alert.querySelector('.alert-close')
                    ?.addEventListener(
                        'click',
                        function() {
                            alert.remove();
                        }
                    );


                setTimeout(function() {

                    if (alert.isConnected) {
                        alert.remove();
                    }

                }, 5000);
            }


            /* =========================================================
               CLICK OUTSIDE MODALS
            ========================================================= */

            [addColorModal, reviewModal]
            .forEach(modal => {

                modal?.addEventListener(
                    'click',
                    function(event) {

                        if (
                            event.target ===
                            modal
                        ) {

                            hideModal(modal);
                        }
                    }
                );

            });


            /* =========================================================
               ESC KEY - MODALS
            ========================================================= */

            document.addEventListener(
                'keydown',
                function(event) {

                    if (event.key !== 'Escape') {
                        return;
                    }


                    if (
                        addColorModal &&
                        !addColorModal.classList.contains(
                            'hidden'
                        )
                    ) {

                        hideModal(addColorModal);

                        return;
                    }


                    if (
                        reviewModal &&
                        !reviewModal.classList.contains(
                            'hidden'
                        )
                    ) {

                        hideModal(reviewModal);
                    }
                }
            );


            /* =========================================================
               DEBUG FORM PAYLOAD
            ========================================================= */

            function debugFormPayload() {

                const formData =
                    new FormData(inventoryForm);


                const payload = {};


                for (
                    const [key, value] of formData.entries()
                ) {

                    if (
                        Object.prototype.hasOwnProperty.call(
                            payload,
                            key
                        )
                    ) {

                        if (
                            !Array.isArray(
                                payload[key]
                            )
                        ) {

                            payload[key] = [
                                payload[key]
                            ];
                        }


                        payload[key].push(value);

                    } else {

                        payload[key] = value;
                    }
                }


                console.log(
                    'Inventory Form Payload:',
                    payload
                );
            }


            /* =========================================================
               DEBUG BEFORE SUBMIT
            ========================================================= */

            inventoryForm.addEventListener(
                'submit',
                function() {

                    debugFormPayload();

                }
            );


            /* =========================================================
               INITIALIZATION
            ========================================================= */

            handleCategoryChange();

            calculateFPTotal();


            /* ---------------------------------------------------------
               Unit index
            --------------------------------------------------------- */

            unitIndex =
                unitsContainer ?
                unitsContainer.querySelectorAll(
                    '.unit-row'
                ).length :
                0;


            /* ---------------------------------------------------------
               Individual mode initial row
            --------------------------------------------------------- */

            if (
                categorySelect?.value !==
                'finished_product' &&
                visibleTrackingType?.value ===
                'individual' &&
                unitsContainer &&
                unitsContainer.querySelectorAll(
                    '.unit-row'
                ).length === 0
            ) {

                createUnitRow(unitIndex);

                unitIndex++;

                updateIndividualUnitTypes();
            }


            /* ---------------------------------------------------------
               Restore existing selected item after validation error
               using old('existing_item_id')
            --------------------------------------------------------- */

            if (
                existingItemIdInput?.value &&
                itemNameInput?.value
            ) {

                const selectedItem =
                    existingItems.find(
                        item =>
                        String(item.id) ===
                        String(existingItemIdInput.value)
                    );


                if (selectedItem) {

                    if (itemSelectionStatus) {

                        itemSelectionStatus.className =
                            'mt-2 text-xs font-bold text-emerald-700 flex items-center gap-1';


                        itemSelectionStatus.innerHTML = `
                    <i class="fa-solid fa-circle-check"></i>

                    <span>
                        تم اختيار الصنف الموجود
                        (${escapeHtml(
                            getCategoryLabel(
                                selectedItem.category
                            )
                        )})
                    </span>
                `;
                    }
                }
            }

        });
    </script>
@endsection
