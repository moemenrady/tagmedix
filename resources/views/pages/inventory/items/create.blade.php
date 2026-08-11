@extends('layouts.app')

@section('title', 'إضافة صنف جديد - Add New Item')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 animate-fade-in-down">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-box-open text-activeGold"></i>
                <span data-ar="إضافة صنف جديد" data-en="Add New Item">إضافة صنف جديد</span>
            </h2>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                <a href="{{ route('inventory.index') }}" class="hover:text-activeGold transition">
                    <i class="fa-solid fa-boxes-stacked text-[10px]"></i>
                    <span data-ar="المخزون" data-en="Inventory">المخزون</span>
                </a>
                <i class="fa-solid fa-chevron-left text-[10px] rtl:inline ltr:hidden"></i>
                <i class="fa-solid fa-chevron-right text-[10px] hidden ltr:inline"></i>
                <span data-ar="إضافة صنف" data-en="Add Item">إضافة صنف</span>
            </p>
        </div>

        <a href="{{ route('inventory.index') }}"
            class="bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2 font-medium text-sm w-full sm:w-auto justify-center">
            <i class="fa-solid fa-arrow-right rtl:inline ltr:hidden"></i>
            <i class="fa-solid fa-arrow-left hidden ltr:inline"></i>
            <span data-ar="العودة للقائمة" data-en="Back to List">العودة للقائمة</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
        <form action="{{ route('inventory.store') }}" method="POST" onsubmit="handleFormSubmit(event)" id="itemForm"
            class="p-6 md:p-8 space-y-8">
            @csrf

            <div>
                <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-gray-400 text-sm"></i>
                    <span data-ar="البيانات الأساسية" data-en="Basic Information">البيانات الأساسية</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-1.5 lg:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700">
                            <span data-ar="اسم الصنف *" data-en="Item Name *">اسم الصنف *</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-activeGold/30 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800"
                            placeholder="...">
                        @error('name')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="code" class="block text-sm font-semibold text-gray-700">
                            <span data-ar="كود الصنف *" data-en="Item Code *">كود الصنف *</span>
                        </label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-activeGold/30 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800"
                            style="direction: ltr;" placeholder="ITM-0001">
                        @error('code')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="category_id" class="block text-sm font-semibold text-gray-700">
                            <span data-ar="التصنيف *" data-en="Category *">التصنيف *</span>
                        </label>
                        <div class="relative">
                            <select id="category_id" name="category_id" required
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 appearance-none focus:outline-none focus:ring-2 focus:ring-activeGold/30 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800 cursor-pointer">
                                <option value="" disabled selected>-- الاختيار --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i
                                class="fa-solid fa-chevron-down absolute top-1/2 -translate-y-1/2 left-4 rtl:left-4 rtl:right-auto ltr:right-4 ltr:left-auto text-gray-400 pointer-events-none text-xs"></i>
                        </div>
                        @error('category_id')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="unit" class="block text-sm font-semibold text-gray-700">
                            <span data-ar="وحدة القياس" data-en="Unit of Measure">وحدة القياس</span>
                        </label>
                        <input type="text" id="unit" name="unit" value="{{ old('unit') }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-activeGold/30 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800"
                            placeholder="قطعة، كجم، متر...">
                        @error('unit')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="minimum_quantity" class="block text-sm font-semibold text-gray-700">
                            <span data-ar="الحد الأدنى للمخزون" data-en="Minimum Quantity">الحد الأدنى للمخزون</span>
                        </label>
                        <input type="number" step="0.01" id="minimum_quantity" name="minimum_quantity"
                            value="{{ old('minimum_quantity', 0) }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-activeGold/30 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800">
                        @error('minimum_quantity')
                            <span class="text-xs text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div
                class="bg-[#faf9f7] p-5 md:p-6 rounded-2xl border border-[#e5dfd5] shadow-inner space-y-4 relative overflow-hidden group">
                <div
                    class="absolute top-0 ltr:right-0 rtl:left-0 w-32 h-32 bg-activeGold opacity-5 rounded-full blur-3xl -translate-y-1/2 group-hover:opacity-10 transition duration-500">
                </div>

                <h3
                    class="text-sm font-bold text-activeGold uppercase tracking-wider flex items-center gap-2 relative z-10">
                    <i class="fa-solid fa-calculator"></i>
                    <span data-ar="قسم إدخال المخزون والتسعير" data-en="Stock Entry & Pricing Section">قسم إدخال المخزون
                        والتسعير</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 relative z-10">
                    <div class="space-y-1.5">
                        <label for="current_quantity" class="text-sm font-bold text-gray-700 block">
                            <span data-ar="الكمية الافتتاحية" data-en="Initial Quantity">الكمية الافتتاحية</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" id="price_quantity" name="current_quantity"
                                value="{{ old('current_quantity') }}"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-activeGold/40 focus:border-activeGold transition bg-white text-gray-800 shadow-sm"
                                placeholder="مثال: 10" required>
                            <i
                                class="fa-solid fa-scale-balanced absolute top-1/2 -translate-y-1/2 left-4 rtl:left-4 ltr:right-4 text-gray-300 pointer-events-none text-xs"></i>
                        </div>
                        @error('current_quantity')
                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="total_price" class="text-sm font-bold text-gray-700 block">
                            <span data-ar="إجمالي سعر الشراء" data-en="Total Purchase Price">إجمالي سعر الشراء</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" id="total_price" name="total_price"
                                value="{{ old('total_price') }}"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-activeGold/40 focus:border-activeGold transition bg-white text-gray-800 shadow-sm"
                                placeholder="مثال: 500" required>
                            <i
                                class="fa-solid fa-money-bill-wave absolute top-1/2 -translate-y-1/2 left-4 rtl:left-4 ltr:right-4 text-gray-300 pointer-events-none text-xs"></i>
                        </div>
                        @error('total_price')
                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="last_purchase_price" class="text-sm font-bold text-gray-700 block">
                            <span data-ar="سعر الوحدة الواحد" data-en="Unit Price">سعر الوحدة الواحد</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.001" id="unit_price" name="last_purchase_price"
                                value="{{ old('last_purchase_price') }}"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-100 text-gray-500 outline-none cursor-not-allowed font-mono text-lg"
                                placeholder="0.000" readonly>
                            <i
                                class="fa-solid fa-lock absolute top-1/2 -translate-y-1/2 left-4 rtl:left-4 ltr:right-4 text-gray-300 pointer-events-none text-xs"></i>
                        </div>
                        @error('last_purchase_price')
                            <span class="text-red-500 text-xs block">{{ $message }}</span>
                        @enderror
                        <p class="text-[10px] text-gray-400 mt-1" data-ar="*يتم حسابه تلقائياً"
                            data-en="*Calculated automatically">*يتم حسابه تلقائياً</p>
                    </div>
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="description" class="block text-sm font-semibold text-gray-700">
                    <span data-ar="ملاحظات / وصف (اختياري)" data-en="Description / Notes (Optional)">ملاحظات / وصف
                        (اختياري)</span>
                </label>
                <textarea id="description" name="description" rows="3"
                    class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-activeGold/30 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800 resize-none">{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-xs text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div>
                    <h4 class="text-sm font-bold text-gray-800" data-ar="حالة الصنف" data-en="Item Status">حالة الصنف
                    </h4>
                    <p class="text-xs text-gray-500 mt-0.5" data-ar="تفعيل أو إيقاف ظهور هذا الصنف في النظام"
                        data-en="Enable or disable this item in the system">تفعيل أو إيقاف ظهور هذا الصنف في النظام</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" class="sr-only peer" value="1"
                        {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                    <div
                        class="w-14 h-7 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] rtl:after:right-[2px] rtl:after:left-auto after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-activeGold shadow-inner">
                    </div>
                </label>
            </div>

            <div class="pt-5 border-t border-gray-100 flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                <a href="{{ route('inventory.index') }}"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition text-center w-full sm:w-auto">
                    <span data-ar="إلغاء" data-en="Cancel">إلغاء</span>
                </a>
                <button type="submit" id="submitBtn"
                    class="px-8 py-2.5 rounded-xl text-sm font-bold text-white bg-activeGold hover:bg-[#725c3d] shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 w-full sm:w-auto group relative overflow-hidden">
                    <span
                        class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                    <i class="fa-solid fa-check"></i>
                    <span data-ar="حفظ الصنف" data-en="Save Item" class="relative z-10">حفظ الصنف</span>
                </button>
            </div>

        </form>
    </div>

@endsection

@push('scripts')
    <style>
        /* CSS بسيط للأنميشن عند فتح الصفحة */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .animate-fade-in-down {
            animation: fadeInDown 0.4s ease-out forwards;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. منطق قسم التسعير (الكمية × السعر = سعر الوحدة) ---
            const priceQtyInput = document.getElementById('price_quantity');
            const totalPriceInput = document.getElementById('total_price');
            const unitPriceInput = document.getElementById('unit_price');

            function calculateUnitPrice() {
                const qty = parseFloat(priceQtyInput.value) || 0;
                const total = parseFloat(totalPriceInput.value) || 0;

                if (qty > 0 && total >= 0) {
                    // الحساب وتثبيت النتيجة لـ 3 أرقام عشرية
                    const result = total / qty;
                    unitPriceInput.value = Number(result.toFixed(3));

                    // تأثير مرئي لطيف عند تغيير الرقم (Animation)
                    unitPriceInput.classList.remove('text-gray-500');
                    unitPriceInput.classList.add('text-activeGold', 'scale-105');
                    setTimeout(() => {
                        unitPriceInput.classList.remove('scale-105');
                    }, 150);
                } else {
                    unitPriceInput.value = '';
                    unitPriceInput.classList.add('text-gray-500');
                    unitPriceInput.classList.remove('text-activeGold');
                }
            }

            priceQtyInput.addEventListener('input', calculateUnitPrice);
            totalPriceInput.addEventListener('input', calculateUnitPrice);

            // تشغيل الدالة مرة عند فتح الصفحة (في حال وجود قيم قديمة بسبب الـ Validation error)
            calculateUnitPrice();
        });

        // --- 2. تفاعل زر الحفظ ---
        function handleFormSubmit(e) {
            const btn = document.getElementById('submitBtn');

            // التحقق مما إذا كان النموذج صالحاً
            if (e.target.checkValidity()) {
                const isAr = localStorage.getItem('lang') !== 'en';

                // تغيير شكل الزر لإظهار حالة التحميل
                btn.innerHTML =
                    `<i class="fa-solid fa-circle-notch fa-spin"></i> <span>${isAr ? 'جاري الحفظ...' : 'Saving...'}</span>`;
                btn.classList.add('opacity-80', 'cursor-not-allowed');
                btn.classList.remove('hover:bg-[#725c3d]');

                // نقوم بتعطيل الزر بعد جزء من الثانية لكي يتم إرسال الفورم الفعلي
                setTimeout(() => {
                    btn.disabled = true;
                }, 50);
            }
        }
    </script>
@endpush
