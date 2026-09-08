@extends('layouts.app')

@section('title', 'إضافة مخزون جديد')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6 pb-20">

        <!-- رسائل التنبيه والنجاح والخطأ -->
        <div id="alertsContainer" class="space-y-3">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="font-bold text-xs sm:text-sm">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-800 flex items-center justify-between shadow-sm animate-fade-in">
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
                <div class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-800 space-y-2 shadow-sm animate-fade-in">
                    <div class="flex items-center justify-between border-b border-rose-200 pb-2">
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm">
                            <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
                            <span>حدثت خطأ أثناء الحفظ (Validation Errors):</span>
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-rose-700 hover:text-rose-900">
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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-secondaryCream shadow-sm">
            <div>
                <h2 class="text-xl font-bold text-darkText flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-[#8A704C]"></i>
                    إضافة شحنة/مخزون جديد
                </h2>
                <p class="text-xs text-gray-500 mt-1">قم بتعبئة بيانات الصنف والوحدات الواردة لتغذية المخزن بنجاح.</p>
            </div>
            <button type="button" id="toggleHelpBtn" class="flex items-center gap-2 bg-rose-50 hover:bg-rose-100 text-[#8A704C] font-semibold px-4 py-2 rounded-xl border border-rose-200 text-xs sm:text-sm transition-all cursor-pointer">
                <i class="fa-solid fa-circle-info text-base"></i>
                <span>شرح عملية الإضافة</span>
            </button>
        </div>

        <!-- شرح الإضافة -->
        <div id="helpSection" class="hidden bg-white border-2 border-dashed border-rose-200 rounded-2xl p-5 text-darkText space-y-3 animate-fade-in">
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

        <!-- الفورم الأساسي -->
        <form id="inventoryForm" action="{{ route('inventory.stocks.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- حقول مخفية للتحكم بالقيم التلقائية -->
            <input type="hidden" name="unit_type" id="main_unit_type" value="{{ old('unit_type', 'meter') }}">
            <input type="hidden" name="stock_tracking_type" id="main_tracking_type" value="{{ old('stock_tracking_type', 'bulk') }}">

            <!-- القسم الأول: البيانات الأساسية -->
            <div class="bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm space-y-4 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-2 h-full bg-[#8A704C]"></div>
                <h3 class="font-bold text-gray-800 text-base border-b border-gray-100 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-[#8A704C]"></i>
                    بيانات الصنف الرئيسية
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- اسم الصنف -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">اسم الصنف <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="item_name" value="{{ old('name') }}" required
                            placeholder="مثال: بنطلون جينز أزرق سليم فيت..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">
                    </div>

                    <!-- الفئة (المفتاح السحري) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">الفئة <span class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all font-bold text-[#8A704C]">
                            <option value="">اختر الفئة...</option>
                            <option value="fabric" {{ old('category') == 'fabric' ? 'selected' : '' }}>أقمشة (Fabric)</option>
                            <option value="accessory" {{ old('category') == 'accessory' ? 'selected' : '' }}>إكسسوارات (Accessory)</option>
                            <option value="finished_product" {{ old('category') == 'finished_product' ? 'selected' : '' }}>منتج نهائي (Finished Product)</option>
                        </select>
                    </div>

                    <!-- اللون الرئيسي -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5 flex justify-between">
                            <span>اللون الرئيسي <span class="text-red-500">*</span></span>
                            <button type="button" onclick="document.getElementById('addColorModal').classList.remove('hidden')"
                                class="text-[#8A704C] hover:text-yellow-700 flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus"></i> إضافة لون
                            </button>
                        </label>
                        <select name="color_id" id="main_color_id" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">
                            <option value="">اختر اللون...</option>
                            @foreach ($colors ?? [] as $color)
                                <option value="{{ $color->id }}" {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                    {{ $color->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- أقل كمية -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">أقل كمية (حد الأمان) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="minimum_stock" id="minimum_stock" value="{{ old('minimum_stock', 1) }}" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">
                    </div>

                    <!-- إعدادات التتبع العادية (ستختفي إذا كان منتج نهائي) -->
                    <div id="standardSettingsContainer">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة التعامل مع المخزون <span class="text-red-500">*</span></label>
                        <select id="visible_tracking_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all">
                            <option value="bulk" {{ old('stock_tracking_type') == 'bulk' ? 'selected' : '' }}>إجمالي (Bulk)</option>
                            <option value="individual" {{ old('stock_tracking_type') == 'individual' ? 'selected' : '' }}>منفرد / توبات (Individual)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- القسم الخاص بالمنتج النهائي (يظهر فقط عند اختيار Finished Product) -->
            <div id="finishedProductSection" class="hidden bg-gradient-to-br from-white to-[#fbfaf8] rounded-2xl p-5 md:p-6 border-2 border-[#8A704C]/30 shadow-lg space-y-5 animate-fade-in relative">
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
                        <label class="block text-xs font-bold text-gray-600 mb-2">نوع البنطلون <span class="text-red-500">*</span></label>
                        <select name="items[0][pant_type_id]" id="fp_pant_type" class="fp-input w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر نوع البنطلون</option>
                            @foreach ($pantTypes ?? [] as $pantType)
                                <option value="{{ $pantType->id }}">{{ $pantType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">نوع المنتج <span class="text-red-500">*</span></label>
                        <select name="items[0][type_id]" id="fp_product_type" class="fp-input w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر نوع المنتج</option>
                            @foreach ($types ?? [] as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">الستايل</label>
                        <select name="items[0][style_id]" id="fp_style" class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر الستايل</option>
                            @foreach ($styles ?? [] as $style)
                                <option value="{{ $style->id }}">{{ $style->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">اللون (مربوط بالرئيسي)</label>
                        <select name="items[0][color_id]" id="fp_color_id" class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر اللون</option>
                            @foreach ($colors ?? [] as $color)
                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">المقاس</label>
                        <select name="items[0][size_id]" id="fp_size" class="fp-input w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر المقاس</option>
                            @foreach ($sizes ?? [] as $size)
                                <option value="{{ $size->id }}">{{ $size->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2">نوع الكم</label>
                        <select name="items[0][sleeve_type_id]" id="fp_sleeve" class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800 shadow-sm">
                            <option value="">اختر نوع الكم</option>
                            @foreach ($sleeveType ?? [] as $sleeve)
                                <option value="{{ $sleeve->id }}">{{ $sleeve->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- الحسابات المدمجة الذكية -->
                    <div class="md:col-span-2 lg:col-span-3 bg-white p-4 rounded-xl border border-gray-200 shadow-sm mt-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3"><i class="fa-solid fa-calculator text-emerald-600 ml-1"></i> الحسابات (الكمية - السعر - الإجمالي)</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-2">الكمية (بالقطعة)</label>
                                <input type="number" name="items[0][quantity]" id="fp_qty" value="1" min="1" class="fp-calc w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 mb-2">سعر القطعة (التكلفة)</label>
                                <input type="number" step="0.01" name="items[0][price]" id="fp_price" value="0" min="0" class="fp-calc w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]" dir="ltr">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-emerald-600 mb-2">الإجمالي الكلي</label>
                                <div class="w-full h-[52px] flex items-center justify-center bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-lg font-black font-mono shadow-inner">
                                    <span id="fp_total_display">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 mb-2">ملاحظات خاصة ومقاسات مخصصة لهذه القطعة (إن وجدت)</label>
                        <input type="text" name="items[0][notes]" placeholder="أدخل أي ملاحظات للتفصيل أو التعديل هنا..." class="w-full h-[46px] px-4 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm font-medium text-gray-800 shadow-sm">
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
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">وحدة القياس <span class="text-red-500">*</span></label>
                        <select id="bulk_unit_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                            <option value="meter">متر (Meter)</option>
                            <option value="kg">كيلو (Kg)</option>
                            <option value="piece">قطعة (Piece)</option>
                            <option value="quantity">كمية عامة (Quantity)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">الكمية <span class="text-red-500">*</span></label>
                        <input type="number" step="0.001" min="0" id="bulk_quantity" name="quantity" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">تكلفة الوحدة <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" id="bulk_unit_cost" name="unit_cost" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                    </div>
                </div>
            </div>

            <!-- القسم 2 ب: Individual (مخفي للجاهز) -->
            <div id="individualSection" class="hidden bg-white rounded-2xl p-5 md:p-6 border border-gray-200 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-barcode text-[#8A704C]"></i> تفاصيل التوبات
                    </h3>
                    <button type="button" id="addUnitRowBtn" class="bg-[#8A704C] text-white text-xs px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-yellow-700 transition-all shadow">
                        <i class="fa-solid fa-plus"></i> إضافة وحدة
                    </button>
                </div>
                <div class="space-y-3" id="unitsContainer"></div>
            </div>

            <!-- زر الإرسال / المراجعة -->
            <div class="flex justify-end pt-4">
                <button type="button" id="openReviewModalBtn" class="bg-[#8A704C] hover:bg-yellow-800 text-white font-bold px-8 py-3.5 rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center gap-2 text-sm sm:text-base cursor-pointer">
                    <i class="fa-solid fa-eye"></i>
                    <span>مراجعة وإتمام الإضافة</span>
                </button>
            </div>
        </form>
    </div>

    <!-- نافذة المراجعة (Review Modal) الذكية -->
    <div id="reviewModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full border border-gray-200 shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-slide-up">
            
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
                <button type="button" id="cancelSubmitBtn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold px-5 py-2.5 rounded-xl transition-colors">
                    تعديل البيانات
                </button>
                <button type="button" id="confirmSubmitBtn" class="bg-[#8A704C] hover:bg-yellow-800 text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow flex items-center gap-2">
                    <i class="fa-solid fa-check" id="confirmBtnIcon"></i>
                    <span id="confirmBtnText">تأكيد وإتمام الحفظ</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // العناصر الرئيسية
            const categorySelect = document.getElementById('category');
            const mainColorSelect = document.getElementById('main_color_id');
            const fpColorSelect = document.getElementById('fp_color_id');
            
            const fpSection = document.getElementById('finishedProductSection');
            const bulkSection = document.getElementById('bulkSection');
            const individualSection = document.getElementById('individualSection');
            const standardSettingsContainer = document.getElementById('standardSettingsContainer');
            
            const visibleTrackingType = document.getElementById('visible_tracking_type');
            const mainTrackingType = document.getElementById('main_tracking_type');
            const mainUnitType = document.getElementById('main_unit_type');
            const bulkUnitSelect = document.getElementById('bulk_unit_type');
            
            // عناصر حساب السعر الجاهز
            const fpQty = document.getElementById('fp_qty');
            const fpPrice = document.getElementById('fp_price');
            const fpTotalDisplay = document.getElementById('fp_total_display');

            // 1. مزامنة الألوان السحرية (فوق يسمع تحت، وتحت يسمع فوق)
            mainColorSelect.addEventListener('change', (e) => {
                fpColorSelect.value = e.target.value;
            });
            fpColorSelect.addEventListener('change', (e) => {
                mainColorSelect.value = e.target.value;
            });

            // 2. حساب إجمالي سعر المنتج الجاهز تلقائياً
            function calculateFPTotal() {
                const qty = parseFloat(fpQty.value) || 0;
                const price = parseFloat(fpPrice.value) || 0;
                fpTotalDisplay.textContent = (qty * price).toFixed(2);
            }
            fpQty.addEventListener('input', calculateFPTotal);
            fpPrice.addEventListener('input', calculateFPTotal);

            // 3. المنطق السحري للتحكم بظهور الأقسام والقيم التلقائية
            function handleCategoryChange() {
                const cat = categorySelect.value;
                
                // تفعيل / تعطيل Required Inputs للأقسام المخفية
                const fpInputs = fpSection.querySelectorAll('.fp-input');
                const bulkInputs = bulkSection.querySelectorAll('input, select');
                
                if (cat === 'finished_product') {
                    // إظهار قسم المصنع وإخفاء الباقي
                    fpSection.classList.remove('hidden');
                    bulkSection.classList.add('hidden');
                    individualSection.classList.add('hidden');
                    standardSettingsContainer.classList.add('hidden');
                    
                    // تعيين القيم التلقائية (تلقائيا كما طلب العميل)
                    mainUnitType.value = 'piece'; // الوحدة تلقائيا قطعة
                    mainTrackingType.value = 'bulk'; // التتبع للكمية
                    
                    // تفعيل التحقق
                    fpInputs.forEach(i => i.setAttribute('required', 'true'));
                    bulkInputs.forEach(i => i.removeAttribute('required'));
                } else {
                    // الأقسام العادية (قماش أو إكسسوار)
                    fpSection.classList.add('hidden');
                    standardSettingsContainer.classList.remove('hidden');
                    
                    fpInputs.forEach(i => i.removeAttribute('required'));
                    handleTrackingChange(); // يعيد التوجيه حسب اختيار التتبع
                }
            }

            function handleTrackingChange() {
                if(categorySelect.value === 'finished_product') return; // لا تنفذ إذا كان منتج نهائي
                
                const type = visibleTrackingType.value;
                mainTrackingType.value = type;
                mainUnitType.value = bulkUnitSelect.value;
                
                if (type === 'bulk') {
                    bulkSection.classList.remove('hidden');
                    individualSection.classList.add('hidden');
                } else {
                    bulkSection.classList.add('hidden');
                    individualSection.classList.remove('hidden');
                }
            }

            categorySelect.addEventListener('change', handleCategoryChange);
            visibleTrackingType.addEventListener('change', handleTrackingChange);
            bulkUnitSelect.addEventListener('change', () => {
                if(categorySelect.value !== 'finished_product') mainUnitType.value = bulkUnitSelect.value;
            });

            // 4. نافذة المراجعة الذكية والمميزة
            document.getElementById('openReviewModalBtn').addEventListener('click', () => {
                const form = document.getElementById('inventoryForm');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const cat = categorySelect.value;
                const modalArea = document.getElementById('modalContentArea');
                let html = '';

                const itemName = document.getElementById('item_name').value;
                const colorName = mainColorSelect.options[mainColorSelect.selectedIndex]?.text || '-';

                if (cat === 'finished_product') {
                    // كارت مميز جداً للمنتج النهائي (VIP Card)
                    const pType = document.getElementById('fp_product_type');
                    const sSize = document.getElementById('fp_size');
                    const total = (parseFloat(fpQty.value) * parseFloat(fpPrice.value)).toFixed(2);
                    
                    html = `
                    <div class="bg-gradient-to-r from-[#8A704C] to-[#C7A977] p-6 rounded-2xl text-white shadow-xl relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 opacity-10 text-8xl"><i class="fa-solid fa-shirt"></i></div>
                        <div class="relative z-10">
                            <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold mb-3 inline-block">✨ منتج مصنع (جاهز)</span>
                            <h2 class="text-2xl font-black mb-1">${itemName}</h2>
                            <p class="text-white/80 text-sm mb-4">النوع: ${pType.options[pType.selectedIndex]?.text || '-'} | المقاس: ${sSize.options[sSize.selectedIndex]?.text || '-'}</p>
                            
                            <div class="grid grid-cols-2 gap-4 bg-black/20 p-4 rounded-xl">
                                <div>
                                    <div class="text-white/60 text-[10px]">الكمية × السعر</div>
                                    <div class="font-bold font-mono text-lg">${fpQty.value} قطعة × ${fpPrice.value} ج.م</div>
                                </div>
                                <div class="text-left">
                                    <div class="text-white/60 text-[10px]">الإجمالي الكلي</div>
                                    <div class="font-black text-2xl text-yellow-300 font-mono">${total} ج.م</div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                } else {
                    // كارت عادي للمخزون
                    const trackName = visibleTrackingType.options[visibleTrackingType.selectedIndex].text;
                    html = `
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <h4 class="font-bold text-gray-800 text-lg border-b border-gray-200 pb-2 mb-2">${itemName}</h4>
                        <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                            <div><span class="font-bold text-gray-800">الفئة:</span> ${categorySelect.options[categorySelect.selectedIndex].text}</div>
                            <div><span class="font-bold text-gray-800">اللون:</span> ${colorName}</div>
                            <div class="col-span-2"><span class="font-bold text-gray-800">نوع التتبع:</span> ${trackName}</div>
                        </div>
                    </div>`;
                }

                modalArea.innerHTML = html;
                document.getElementById('reviewModal').classList.remove('hidden');
            });

            // إغلاق النافذة
            const closeModal = () => document.getElementById('reviewModal').classList.add('hidden');
            document.getElementById('closeReviewModalBtn').addEventListener('click', closeModal);
            document.getElementById('cancelSubmitBtn').addEventListener('click', closeModal);

            // تأكيد وإرسال مع تأثير التحميل
            document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
                this.disabled = true;
                document.getElementById('confirmBtnIcon').className = "fa-solid fa-spinner fa-spin";
                document.getElementById('confirmBtnText').textContent = "جاري الحفظ...";
                document.getElementById('inventoryForm').submit();
            });

            // تشغيل التهيئة الأولية
            handleCategoryChange();
        });
    </script>
@endsection