@extends('layouts.app')

@section('title', 'إضافة مخزون جديد')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Flash Messages & Validation Errors Display (Snackbar / Alert) -->
        <div id="alertsContainer" class="space-y-3">
            @if (session('success'))
                <div class="p-4 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in"
                    role="alert">
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
                <div class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-800 flex items-center justify-between shadow-sm animate-fade-in"
                    role="alert">
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
                <div class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-800 space-y-2 shadow-sm animate-fade-in"
                    role="alert">

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

        <!-- Top Bar / Header & Help Toggle -->
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-secondaryCream shadow-sm">
            <div>
                <h2 class="text-xl font-bold text-darkText flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-primaryBrown"></i>
                    إضافة شحنة/مخزون جديد
                </h2>
                <p class="text-xs text-mutedBrown mt-1">قم بتعبئة بيانات الصنف والوحدات الواردة لتغذية المخزن بنجاح.</p>
            </div>
            <button type="button" id="toggleHelpBtn"
                class="flex items-center gap-2 bg-softPink/30 hover:bg-softPink/50 text-mutedBrown font-semibold px-4 py-2 rounded-xl border border-roseAccent/30 text-xs sm:text-sm transition-all cursor-pointer">
                <i class="fa-solid fa-circle-info text-primaryBrown text-base"></i>
                <span>شرح عملية الإضافة</span>
            </button>
        </div>

        <!-- Help / Explanation Card (Hidden by default) -->
        <div id="helpSection"
            class="hidden bg-white border-2 border-dashed border-roseAccent/40 rounded-2xl p-5 text-darkText space-y-3 animate-fade-in">
            <h3 class="font-bold text-primaryBrown flex items-center gap-2 text-sm sm:text-base">
                <i class="fa-solid fa-lightbulb text-yellow-500"></i>
                دليل التتبع والتعامل مع المخزون:
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs sm:text-sm text-mutedBrown">
                <div class="bg-primaryBg/50 p-3 rounded-xl border border-secondaryCream">
                    <strong class="text-darkText block mb-1">1. النمط الإجمالي (Bulk):</strong>
                    يُستخدم مع المستلزمات التي لا تتطلب تتبع كود كل وحدة بشكل منفصل. يتم إدخال الكمية الكلية وتكلفتها
                    مباشرة.
                </div>
                <div class="bg-primaryBg/50 p-3 rounded-xl border border-secondaryCream">
                    <strong class="text-darkText block mb-1">2. النمط المنفرد (Individual):</strong>
                    يُستخدم مع الأثواب أو الطرود التي يحمل كل منها كود فريد، ليتم الصرف لاحقاً بـ Scan الباركود.
                </div>
            </div>
        </div>

        <!-- Main Entry Form -->
        <form id="inventoryForm" action="{{ route('inventory.stocks.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Card 1: Main Details (Common Fields) -->
            <div class="bg-white rounded-2xl p-5 md:p-6 border border-secondaryCream shadow-sm space-y-4">
                <h3
                    class="font-bold text-darkText text-base border-b border-secondaryCream/60 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-primaryBrown"></i>
                    بيانات الصنف الرئيسية
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    <!-- Item Name -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-darkText mb-1.5">اسم الصنف <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" id="item_name" value="{{ old('name') }}" required
                            placeholder="مثال: قماش جينز أزرق..."
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                    </div>

                    <!-- Item Category -->
                    <div>
                        <label class="block text-xs font-bold text-darkText mb-1.5">الفئة <span
                                class="text-red-500">*</span></label>
                        <select name="category" id="category" required
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                            <option value="">اختر الفئة...</option>
                            <option value="fabric" {{ old('category') == 'fabric' ? 'selected' : '' }}>أقمشة (Fabric)
                            </option>
                            <option value="accessory" {{ old('category') == 'accessory' ? 'selected' : '' }}>إكسسوارات
                                (Accessory)</option>
                            <option value="finished_product" {{ old('category') == 'finished_product' ? 'selected' : '' }}>
                                منتج نهائي (Finished Product)</option>
                        </select>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-xs font-bold text-darkText mb-1.5 flex justify-between">
                            <span>اللون <span class="text-red-500">*</span></span>
                            <button type="button" onclick="document.getElementById('addColorModal').classList.remove('hidden')" class="text-primaryBrown hover:text-mutedBrown flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-plus"></i> إضافة لون
                            </button>
                        </label>
                        <select name="color_id" id="color_id" required
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                            <option value="">اختر اللون...</option>
                            @foreach ($colors ?? [] as $color)
                                <option value="{{ $color->id }}" {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                    {{ $color->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Minimum Stock -->
                    <div>
                        <label class="block text-xs font-bold text-darkText mb-1.5">أقل كمية (حد الأمان) <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="minimum_stock" id="minimum_stock"
                            value="{{ old('minimum_stock') }}" required placeholder="0"
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                    </div>

                    <!-- Stock Tracking Type -->
                    <div>
                        <label class="block text-xs font-bold text-darkText mb-1.5">طريقة التعامل مع المخزون <span
                                class="text-red-500">*</span></label>
                        <select name="stock_tracking_type" id="stock_tracking_type" required
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                            <option value="bulk" {{ old('stock_tracking_type') == 'bulk' ? 'selected' : '' }}>إجمالي
                                (Bulk)</option>
                            <option value="individual" {{ old('stock_tracking_type') == 'individual' ? 'selected' : '' }}>
                                منفرد / توبات (Individual)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2A: Bulk Entry -->
            <div id="bulkSection" class="bg-white rounded-2xl p-5 md:p-6 border border-secondaryCream shadow-sm space-y-4">
                <h3
                    class="font-bold text-darkText text-base border-b border-secondaryCream/60 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-cubes text-primaryBrown"></i>
                    إدخال كمية إجمالية (Bulk)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-darkText mb-1.5">وحدة القياس <span
                                class="text-red-500">*</span></label>
                        <select name="unit_type" id="bulk_unit_type" required
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown">
                            <option value="meter" {{ old('unit_type') == 'meter' ? 'selected' : '' }}>متر (Meter)
                            </option>
                            <option value="kg" {{ old('unit_type') == 'kg' ? 'selected' : '' }}>كيلو (Kg)</option>
                            <option value="piece" {{ old('unit_type') == 'piece' ? 'selected' : '' }}>قطعة (Piece)
                            </option>
                            <option value="quantity" {{ old('unit_type') == 'quantity' ? 'selected' : '' }}>كمية عامة
                                (Quantity)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-darkText mb-1.5">الكمية <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.001" min="0" name="quantity" id="bulk_quantity"
                            value="{{ old('quantity') }}" required placeholder="0"
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-darkText mb-1.5">تكلفة الوحدة الواحدة <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="unit_cost" id="bulk_unit_cost"
                            value="{{ old('unit_cost') }}" required placeholder="0.00"
                            class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-darkText mb-1.5">ملاحظات عامة</label>
                    <textarea name="notes" rows="2" placeholder="أدخل أي ملاحظات إن وجدت..."
                        class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl p-3 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown">{{ old('notes') }}</textarea>
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

    <!-- Review Modal -->
    <div id="reviewModal"
        class="fixed inset-0 bg-darkText/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div
            class="bg-white rounded-2xl max-w-2xl w-full border border-secondaryCream shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-slide-up">

            <div class="bg-primaryBrown text-white p-4 flex justify-between items-center">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-receipt"></i>
                    معاينة كارت الخامة قبل الاعتماد
                </h3>
                <button type="button" id="closeReviewModalBtn"
                    class="text-white/80 hover:text-white text-lg cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-4 text-xs sm:text-sm">
                <div id="summaryHeaderCard" class="bg-primaryBg/60 p-4 rounded-xl border border-secondaryCream space-y-2">
                    <!-- Summary Data -->
                </div>
                <div class="space-y-2">
                    <h4 class="font-bold text-darkText">التفاصيل:</h4>
                    <div id="summaryUnitsList" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <!-- Dynamic Data -->
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-secondaryCream bg-primaryBg/30 flex justify-between items-center">
                <button type="button" id="cancelSubmitBtn"
                    class="bg-secondaryCream hover:bg-softBeige text-darkText font-bold px-5 py-2.5 rounded-xl transition-colors cursor-pointer">
                    تعديل البيانات
                </button>
                <button type="button" id="confirmSubmitBtn"
                    class="bg-primaryBrown hover:bg-mutedBrown text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-md flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-check" id="confirmBtnIcon"></i>
                    <span id="confirmBtnText">تأكيد وإتمام الحفظ</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let unitIndex = 0;

            // References
            const trackingTypeSelect = document.getElementById('stock_tracking_type');
            const bulkSection = document.getElementById('bulkSection');
            const individualSection = document.getElementById('individualSection');
            const unitsContainer = document.getElementById('unitsContainer');
            const addUnitRowBtn = document.getElementById('addUnitRowBtn');
            const helpBtn = document.getElementById('toggleHelpBtn');
            const helpSection = document.getElementById('helpSection');

            // Review Modal References
            const reviewModal = document.getElementById('reviewModal');
            const openReviewModalBtn = document.getElementById('openReviewModalBtn');
            const closeReviewModalBtn = document.getElementById('closeReviewModalBtn');
            const cancelSubmitBtn = document.getElementById('cancelSubmitBtn');
            const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            const confirmBtnIcon = document.getElementById('confirmBtnIcon');
            const inventoryForm = document.getElementById('inventoryForm');

            // Toggle Help Section
            helpBtn.addEventListener('click', () => {
                helpSection.classList.toggle('hidden');
            });

            // Handle Tracking Type Change & Payload Formatter
            function handleTrackingTypeChange() {
                const type = trackingTypeSelect.value;
                const bulkInputs = bulkSection.querySelectorAll('input, select, textarea');
                const individualInputs = individualSection.querySelectorAll('input, select, textarea');

                if (type === 'individual') {
                    bulkSection.classList.add('hidden');
                    individualSection.classList.remove('hidden');

                    // Disable bulk inputs so they don't get sent in POST request
                    bulkInputs.forEach(el => el.disabled = true);
                    // Enable individual inputs
                    individualInputs.forEach(el => el.disabled = false);

                    if (unitsContainer.children.length === 0) {
                        addUnitRow();
                    }
                } else {
                    bulkSection.classList.remove('hidden');
                    individualSection.classList.add('hidden');

                    // Enable bulk inputs
                    bulkInputs.forEach(el => el.disabled = false);
                    // Disable individual inputs so they don't get sent in POST request
                    individualInputs.forEach(el => el.disabled = true);
                }
            }

            trackingTypeSelect.addEventListener('change', handleTrackingTypeChange);

            // Dynamic Unit Row Addition
            function addUnitRow() {
                const rowId = `unit_row_${unitIndex}`;
                const rowHtml = `
            <div id="${rowId}" class="p-3 bg-primaryBg/30 border border-secondaryCream rounded-xl grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-2.5 items-end transition-all">
                <div>
                    <label class="block text-[11px] font-bold text-darkText mb-1">وحدة القياس</label>
                    <select name="units[${unitIndex}][unit_type]" required class="unit-type w-full bg-white border border-secondaryCream rounded-lg p-2 text-xs">
                        <option value="meter">متر</option>
                        <option value="kg">كيلو</option>
                        <option value="piece">قطعة</option>
                        <option value="quantity">كمية</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-darkText mb-1">الكود الفعلي</label>
                    <input type="text" name="units[${unitIndex}][unit_code]" placeholder="TOP-001" required class="unit-code w-full bg-white border border-secondaryCream rounded-lg p-2 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-darkText mb-1">الكمية</label>
                    <input type="number" step="0.001" min="0" name="units[${unitIndex}][quantity]" placeholder="0" required class="unit-qty w-full bg-white border border-secondaryCream rounded-lg p-2 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-darkText mb-1">سعر الوحدة</label>
                    <input type="number" step="0.01" min="0" name="units[${unitIndex}][unit_cost]" placeholder="0.00" required class="unit-cost w-full bg-white border border-secondaryCream rounded-lg p-2 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-darkText mb-1">ملحوظة</label>
                    <input type="text" name="units[${unitIndex}][notes]" placeholder="..." class="unit-notes w-full bg-white border border-secondaryCream rounded-lg p-2 text-xs">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="removeUnitRow('${rowId}')" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 p-2 rounded-lg text-xs w-full sm:w-auto flex items-center justify-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-trash-can"></i>
                        <span class="sm:hidden">حذف</span>
                    </button>
                </div>
            </div>
        `;
                unitsContainer.insertAdjacentHTML('beforeend', rowHtml);

                // Ensure new row elements are enabled if we are in individual mode
                if (trackingTypeSelect.value === 'individual') {
                    document.getElementById(rowId).querySelectorAll('input, select').forEach(el => el.disabled =
                        false);
                } else {
                    document.getElementById(rowId).querySelectorAll('input, select').forEach(el => el.disabled =
                        true);
                }

                unitIndex++;
            }

            addUnitRowBtn.addEventListener('click', addUnitRow);

            window.removeUnitRow = function(rowId) {
                const row = document.getElementById(rowId);
                if (row) {
                    row.remove();
                }
            };

            // Dynamic Error Message Trigger (Internal Helper instead of alert)
            function showLocalError(message) {
                const alertsContainer = document.getElementById('alertsContainer');
                const errHtml = `
                    <div class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-800 flex items-center justify-between shadow-sm animate-fade-in" role="alert">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                            <span class="font-bold text-xs sm:text-sm">${message}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                `;
                alertsContainer.insertAdjacentHTML('beforeend', errHtml);
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            // Form Validation & Modal Trigger
            openReviewModalBtn.addEventListener('click', () => {
                if (!inventoryForm.checkValidity()) {
                    inventoryForm.reportValidity();
                    return;
                }

                // Summary Data
                const itemName = document.getElementById('item_name').value || 'بدون اسم';
                const categorySelect = document.getElementById('category');
                const categoryName = categorySelect.options[categorySelect.selectedIndex]?.text || '-';
                const colorSelect = document.getElementById('color_id');
                const colorName = colorSelect.options[colorSelect.selectedIndex]?.text || '-';
                const trackingType = trackingTypeSelect.value;

                let summaryHeader = `
            <div class="font-bold text-sm text-primaryBrown mb-1">الصنف: ${itemName}</div>
            <div class="font-bold text-xs text-mutedBrown flex items-center justify-between">
                <span>الفئة: ${categoryName}</span>
                <span class="bg-roseAccent/20 text-mutedBrown px-2.5 py-0.5 rounded-full text-xs">اللون: ${colorName}</span>
            </div>
        `;

                let summaryUnits = '';

                if (trackingType === 'bulk') {
                    const qty = document.getElementById('bulk_quantity').value || 0;
                    const unitType = document.getElementById('bulk_unit_type').options[document
                        .getElementById('bulk_unit_type').selectedIndex]?.text;
                    const cost = document.getElementById('bulk_unit_cost').value || 0;
                    const totalCost = (qty * cost).toFixed(2);

                    summaryUnits = `
                <div class="p-3 bg-primaryBg/40 rounded-xl border border-secondaryCream flex justify-between items-center">
                    <div>
                        <span class="font-bold">كمية إجمالية:</span> ${qty} ${unitType}
                        <div class="text-xs text-mutedBrown">سعر الوحدة: ${cost} ج.م</div>
                    </div>
                    <div class="font-bold text-primaryBrown">الإجمالي: ${totalCost} ج.م</div>
                </div>
            `;
                } else {
                    const rows = unitsContainer.querySelectorAll('[id^="unit_row_"]');
                    if (rows.length === 0) {
                        showLocalError('برجاء إضافة وحدة واحدة على الأقل!');
                        return;
                    }

                    rows.forEach((row) => {
                        const code = row.querySelector('.unit-code').value;
                        const qty = row.querySelector('.unit-qty').value;
                        const unitTypeSelect = row.querySelector('.unit-type');
                        const unitType = unitTypeSelect.options[unitTypeSelect.selectedIndex]?.text;
                        const cost = row.querySelector('.unit-cost').value;
                        const notes = row.querySelector('.unit-notes').value;
                        const totalUnitCost = (qty * cost).toFixed(2);

                        summaryUnits += `
                    <div class="p-3 bg-primaryBg/30 rounded-xl border border-secondaryCream flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <div>
                            <span class="font-bold text-darkText">وحدة (${code}):</span> ${qty} ${unitType} - الوحدة بـ ${cost} ج.م
                            ${notes ? `<div class="text-xs text-mutedBrown">ملحوظة: ${notes}</div>` : ''}
                        </div>
                        <div class="font-bold text-primaryBrown shrink-0">
                            الإجمالي: ${totalUnitCost} ج.م
                        </div>
                    </div>
                `;
                    });
                }

                document.getElementById('summaryHeaderCard').innerHTML = summaryHeader;
                document.getElementById('summaryUnitsList').innerHTML = summaryUnits;

                reviewModal.classList.remove('hidden');
            });

            // Close Modal Events
            closeReviewModalBtn.addEventListener('click', () => reviewModal.classList.add('hidden'));
            cancelSubmitBtn.addEventListener('click', () => reviewModal.classList.add('hidden'));

            // Final Form Submission & Loading State Control
            confirmSubmitBtn.addEventListener('click', () => {
                // Disable button and show loading state
                confirmSubmitBtn.disabled = true;
                if (confirmBtnIcon) confirmBtnIcon.className = "fa-solid fa-spinner fa-spin";
                if (confirmBtnText) confirmBtnText.textContent = "جاري الحفظ...";

                // Submit Form
                inventoryForm.submit();
            });

            // Initialize state
            handleTrackingTypeChange();
        });
    </script>
    <!-- Add Color Modal -->
    <div id="addColorModal" class="fixed inset-0 bg-darkText/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full border border-secondaryCream shadow-2xl overflow-hidden animate-slide-up">
            <div class="bg-primaryBrown text-white p-4 flex justify-between items-center">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <i class="fa-solid fa-palette"></i>
                    إضافة لون جديد
                </h3>
                <button type="button" onclick="document.getElementById('addColorModal').classList.add('hidden')"
                    class="text-white/80 hover:text-white text-lg cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-darkText mb-1.5">اسم اللون <span class="text-red-500">*</span></label>
                    <input type="text" id="new_color_name" placeholder="مثال: أحمر" class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown">
                </div>
                <div>
                    <label class="block text-xs font-bold text-darkText mb-1.5">المعرف (Identifier) <span class="text-red-500">*</span></label>
                    <input type="text" id="new_color_identifier" placeholder="مثال: red" class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown">
                </div>
                <div id="addColorError" class="hidden text-xs text-red-500 font-bold"></div>
            </div>
            <div class="p-4 border-t border-secondaryCream bg-primaryBg/30 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addColorModal').classList.add('hidden')"
                    class="bg-secondaryCream hover:bg-softBeige text-darkText font-bold px-5 py-2.5 rounded-xl transition-colors cursor-pointer">
                    إلغاء
                </button>
                <button type="button" id="submitNewColorBtn"
                    class="bg-primaryBrown hover:bg-mutedBrown text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-md flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-check"></i>
                    حفظ اللون
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const submitNewColorBtn = document.getElementById('submitNewColorBtn');
            if (submitNewColorBtn) {
                submitNewColorBtn.addEventListener('click', async function() {
                    const name = document.getElementById('new_color_name').value.trim();
                    const identifier = document.getElementById('new_color_identifier').value.trim();
                    const errorDiv = document.getElementById('addColorError');
                    errorDiv.classList.add('hidden');

                    if (!name || !identifier) {
                        errorDiv.textContent = 'يرجى إدخال اسم اللون والمعرف';
                        errorDiv.classList.remove('hidden');
                        return;
                    }

                    const btn = this;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...';

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                                      || document.querySelector('input[name="_token"]')?.value;
                        const response = await fetch('{{ route("colors.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ name, identifier })
                        });

                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            const select = document.getElementById('color_id');
                            const option = new Option(data.color.name, data.color.id, false, true);
                            select.add(option);
                            
                            document.getElementById('addColorModal').classList.add('hidden');
                            document.getElementById('new_color_name').value = '';
                            document.getElementById('new_color_identifier').value = '';
                        } else {
                            errorDiv.textContent = data.message || 'حدث خطأ أثناء حفظ اللون. قد يكون الاسم أو المعرف مستخدماً من قبل.';
                            errorDiv.classList.remove('hidden');
                        }
                    } catch (e) {
                        errorDiv.textContent = 'حدث خطأ في الاتصال بالخادم';
                        errorDiv.classList.remove('hidden');
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> حفظ اللون';
                    }
                });
            }
        });
    </script>
@endsection
