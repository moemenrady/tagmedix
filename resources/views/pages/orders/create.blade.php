@extends('layouts.app')

@section('title', 'إنشاء طلب جديد')
@section('page_title', 'إضافة طلب جديد للشركة')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" dir="rtl">

        @if ($errors->any())
            <div class="mb-6 p-5 bg-red-50 border-r-4 border-red-500 text-red-700 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <p class="font-bold">يرجى تصحيح الأخطاء التالية:</p>
                </div>
                <ul class="list-disc list-inside text-sm space-y-1 mr-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                <div class="lg:col-span-2 space-y-6">

                    <div
                        class="order-item-card modern-card bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-50 pb-4">
                            <span
                                class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg shadow-inner">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-800">بيانات العميل والطلب</h3>
                                <p class="text-xs text-gray-400 mt-0.5">أدخل المعلومات الأساسية للتواصل والشحن بحقول واضحة
                                    ومريحة</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">اسم العميل <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">مصدر الطلب <span
                                        class="text-red-500">*</span></label>
                                <select name="source" required
                                    class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                                    <option value="new" {{ old('source') == 'new' ? 'selected' : '' }}>طلب جديد (تفصيل)
                                    </option>
                                    <option value="from_stock" {{ old('source') == 'from_stock' ? 'selected' : '' }}>من
                                        المخزن جاهز</option>
                                    <option value="replacement" {{ old('source') == 'replacement' ? 'selected' : '' }}>طلب
                                        استبدال</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">رقم الهاتف الأساسي <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="phone_1" value="{{ old('phone_1') }}" required
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-semibold text-gray-800 font-sans tracking-wide">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">رقم هاتف إضافي</label>
                                <input type="text" name="phone_2" value="{{ old('phone_2') }}"
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-semibold text-gray-800 font-sans tracking-wide">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">المحافظة <span
                                        class="text-red-500">*</span></label>
                                <select name="governorate" required
                                    class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                                    <option value="" disabled selected>اختر المحافظة...</option>
                                    <option value="القاهرة">القاهرة</option>
                                    <option value="الجيزة">الجيزة</option>
                                    <option value="الإسكندرية">الإسكندرية</option>
                                    <option value="الدقهلية">الدقهلية</option>
                                    <option value="الغربية">الغربية</option>
                                    <option value="الشرقية">الشرقية</option>
                                    <option value="القليوبية">القليوبية</option>
                                    <option value="دمياط">دمياط</option>
                                    <option value="بورسعيد">بورسعيد</option>
                                    <option value="السويس">السويس</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">رابط / حساب إنستغرام</label>
                                <div class="relative flex items-center">
                                    <span class="absolute right-4 text-pink-600 text-base"><i
                                            class="fa-brands fa-instagram"></i></span>
                                    <input type="text" name="instagram_profile" value="{{ old('instagram_profile') }}"
                                        placeholder="@username"
                                        class="w-full h-[46px] pr-11 pl-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm text-left font-mono"
                                        dir="ltr">
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-600 mb-2">العنوان بالتفصيل <span
                                        class="text-red-500">*</span></label>
                                <textarea name="full_address" rows="2" required placeholder="اسم الشارع، رقم العقار، الدور، علامة مميزة..."
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 resize-none min-h-[70px]"></textarea>
                            </div>
                        </div>
                    </div>

                    <div
                        class="order-item-card modern-card bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-gray-50 pb-4">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg shadow-inner">
                                    <i class="fa-solid fa-basket-shopping"></i>
                                </span>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-gray-800">منتجات ومحتويات الطلب</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">يمكنك إضافة منتج واحد أو عدة منتجات وتخصيصها
                                        بالكامل</p>
                                </div>
                            </div>

                            <button type="button" id="addItemBtn"
                                class="modern-card bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center justify-center gap-2 shadow-sm transform hover:-translate-y-0.5">
                                <i class="fa-solid fa-circle-plus text-base"></i> إضافة منتج للطلب
                            </button>
                        </div>

                        <div id="itemsContainer" class="space-y-5">
                        </div>
                    </div>

                </div>

                <div class="space-y-6 lg:sticky lg:top-6">

                    <div class="bg-[#111827] text-gray-300 rounded-2xl shadow-xl border border-gray-800 p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-800 pb-4">
                            <span
                                class="w-10 h-10 rounded-xl bg-gray-800 text-[#8A704C] flex items-center justify-center text-lg shadow-inner">
                                <i class="fa-solid fa-calculator"></i>
                            </span>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">الملخص المالي</h3>
                                <p class="text-xs text-gray-500 mt-0.5">متابعة الحسابات الإجمالية للطلب فورياً</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div
                                class="flex justify-between items-center bg-gray-900/50 p-4 rounded-xl border border-gray-800 shadow-inner">
                                <span class="text-sm font-semibold text-gray-400">إجمالي قيمة المنتجات:</span>
                                <div class="text-left">
                                    <span id="totalPriceDisplay" class="text-2xl font-black text-white">0.00</span>
                                    <span class="text-xs text-[#8A704C] font-bold mr-1">ج.م</span>
                                </div>
                                <input type="hidden" name="total_price" id="total_price_hidden" value="0">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 mb-2">المبلغ المدفوع مقدمًا
                                    (ج.م)</label>
                                <div class="relative flex items-center">
                                    <span class="absolute right-4 text-emerald-500"><i
                                            class="fa-solid fa-money-bill-wave"></i></span>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount"
                                        value="0" min="0"
                                        class="w-full h-[48px] pr-11 pl-4 py-3 bg-gray-900 border border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-base font-bold text-white text-left font-mono"
                                        dir="ltr">
                                </div>
                            </div>

                            <div
                                class="flex justify-between items-center bg-red-950/20 p-4 rounded-xl border border-red-900/30">
                                <span class="text-sm font-semibold text-red-400/80">المبلغ المتبقي للتحصيل:</span>
                                <div class="text-left">
                                    <span id="remainingPriceDisplay" class="text-xl font-bold text-red-400">0.00</span>
                                    <span class="text-xs text-red-400 mr-1">ج.م</span>
                                </div>
                                <input type="hidden" name="remaining_amount" id="remaining_amount_hidden"
                                    value="0">
                            </div>



                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full bg-[#8A704C] hover:bg-[#725c3e] text-white font-bold py-3.5 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-3 text-base sm:text-lg transform hover:-translate-y-0.5">
                                    <i class="fa-solid fa-cloud-arrow-up text-xl"></i> حفظ وإرسال الطلب
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </form>
    </div>

    <template id="itemTemplate">
        <div
            class="order-item-card modern-card bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm relative transition-all hover:shadow-md mb-5 group">

            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-50">
                <div class="flex items-center gap-3">
                    <span
                        class="w-8 h-8 rounded-lg bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-sm font-bold shadow-inner">
                        <i class="fa-solid fa-box-open"></i>
                    </span>
                    <h4 class="text-base font-bold text-gray-800">تفاصيل المنتج</h4>
                </div>
                <button type="button"
                    class="remove-item-btn text-gray-400 hover:text-red-600 hover:bg-red-50 w-8 h-8 rounded-xl flex items-center justify-center transition-all"
                    title="إلغاء المنتج">
                    <i class="fa-solid fa-trash-can text-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">
                        نوع البنطلون
                        <span class="text-red-500">*</span>
                    </label>

                    <select name="items[{index}][pant_type_id]" required
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl
               focus:outline-none focus:ring-2 focus:ring-[#8A704C]
               focus:bg-white transition-all text-sm font-medium
               text-gray-800 cursor-pointer">

                        <option value="">اختر نوع البنطلون</option>

                        @foreach ($pantTypes as $pantType)
                            <option value="{{ $pantType->id }}">
                                {{ $pantType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">
                        نوع المنتج
                        <span class="text-red-500">*</span>
                    </label>

                    <select name="items[{index}][type_id]" required
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl
               focus:outline-none focus:ring-2 focus:ring-[#8A704C]
               focus:bg-white transition-all text-sm font-medium
               text-gray-800 cursor-pointer">

                        <option value="">اختر نوع المنتج</option>

                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">الستايل</label>
                    <select name="items[{index}][style_id]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        @foreach ($styles as $style)
                            <option value="{{ $style->id }}">{{ $style->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">اللون</label>
                    <select name="items[{index}][color_id]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">المقاس</label>
                    <select name="items[{index}][size_id]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        @foreach ($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">الكم</label>
                    <select name="items[{index}][sleeve]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        <option value="long">كم طويل</option>
                        <option value="short">كم قصير</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 mb-3">
                        الحسابات (الكمية - السعر - الإجمالي)
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">

                        <!-- الكمية -->
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">الكمية</label>
                            <input type="number" name="items[{index}][quantity]" value="1" min="1" required
                                class="item-qty w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl
                       text-center text-lg font-bold font-mono
                       focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                        </div>

                        <!-- السعر -->
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">سعر القطعة</label>
                            <input type="number" step="0.01" name="items[{index}][price]" value="0"
                                min="0" required
                                class="item-price w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl
                       text-center text-lg font-bold font-mono
                       focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                                dir="ltr">
                        </div>

                        <!-- الإجمالي -->
                        <div>
                            <label class="block text-[11px] font-bold text-emerald-600 mb-2">الإجمالي</label>

                            <div
                                class="w-full h-[52px] flex items-center justify-center
                        bg-emerald-50 text-emerald-700 border border-emerald-200
                        rounded-xl text-lg font-black font-mono">
                                <span class="item-total-display">0.00</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 mb-2">ملاحظات خاصة ومقاسات مخصصة لهذه القطعة (إن
                        وجد)</label>
                    <input type="text" name="items[{index}][notes]"
                        placeholder="أدخل أي ملاحظات للتفصيل أو التعديل هنا..."
                        class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800">
                </div>

            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('itemsContainer');
            const template = document.getElementById('itemTemplate').innerHTML;
            const addItemBtn = document.getElementById('addItemBtn');

            const totalPriceDisplay = document.getElementById('totalPriceDisplay');
            const totalPriceHidden = document.getElementById('total_price_hidden');
            const paidAmountInput = document.getElementById('paid_amount');
            const remainingPriceDisplay = document.getElementById('remainingPriceDisplay');
            const remainingAmountHidden = document.getElementById('remaining_amount_hidden');

            let itemIndex = 0;

            // دالة لإضافة سطر منتج جديد داخل الحاوية
            function addNewItemRow() {
                let itemHtml = template.replace(/{index}/g, itemIndex);
                container.insertAdjacentHTML('beforeend', itemHtml);
                itemIndex++;

                bindRowCalculations();
                calculateFullOrderSummary();
            }

            // ربط مستمعي الحسابات في كل سطر مضاف حديثاً
            function bindRowCalculations() {
                const rows = container.querySelectorAll('.order-item-card');
                rows.forEach(row => {
                    const qtyInput = row.querySelector('.item-qty');
                    const priceInput = row.querySelector('.item-price');
                    const removeBtn = row.querySelector('.remove-item-btn');

                    if (qtyInput && priceInput && removeBtn) {
                        qtyInput.oninput = () => {
                            calculateRowTotal(row);
                            calculateFullOrderSummary();
                        };
                        priceInput.oninput = () => {
                            calculateRowTotal(row);
                            calculateFullOrderSummary();
                        };
                        removeBtn.onclick = () => {
                            row.remove();
                            calculateFullOrderSummary();
                        };
                    }
                });
            }

            // حساب إجمالي سطر منتج منفصل
            function calculateRowTotal(row) {
                const qtyInput = row.querySelector('.item-qty');
                const priceInput = row.querySelector('.item-price');
                const totalDisplay = row.querySelector('.item-total-display');

                if (qtyInput && priceInput && totalDisplay) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    const total = qty * price;
                    totalDisplay.textContent = total.toFixed(2);
                }
            }

            // دالة حساب التكلفة الإجمالية للطلب والمبلغ المتبقي للتحصيل بالكامل
            function calculateFullOrderSummary() {
                let orderTotal = 0;
                const rows = container.querySelectorAll('.order-item-card');

                rows.forEach(row => {
                    const qtyInput = row.querySelector('.item-qty');
                    const priceInput = row.querySelector('.item-price');
                    if (qtyInput && priceInput) {
                        const qty = parseFloat(qtyInput.value) || 0;
                        const price = parseFloat(priceInput.value) || 0;
                        orderTotal += (qty * price);
                    }
                });

                const paidAmount = parseFloat(paidAmountInput.value) || 0;
                const remainingAmount = orderTotal - paidAmount;

                totalPriceDisplay.textContent = orderTotal.toFixed(2);
                totalPriceHidden.value = orderTotal.toFixed(2);

                remainingPriceDisplay.textContent = remainingAmount.toFixed(2);
                remainingAmountHidden.value = remainingAmount.toFixed(2);
            }

            paidAmountInput.addEventListener('input', calculateFullOrderSummary);
            addItemBtn.addEventListener('click', addNewItemRow);

            // إضافة سطر أول للمنتج افتراضياً فور فتح الصفحة ليكون جاهزاً للمستخدم
            addNewItemRow();
        });
    </script>
@endpush
{{-- @extends('layouts.app')

@section('title', 'النظام تحت الصيانة')

@section('content')
    <style>
        .maintenance-page {
            min-height: calc(100vh - 120px);
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8fafc;
            padding: 30px;
        }

        .maintenance-card {
            max-width: 650px;
            width: 100%;
            background: #fff;
            border-radius: 24px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
            border: 1px solid #e5e7eb;
        }

        .maintenance-icon {
            width: 110px;
            height: 110px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            display: flex;
            justify-content: center;
            align-items: center;
            color: #d97706;
            font-size: 48px;
        }

        .maintenance-card h1 {
            font-size: 32px;
            color: #1f2937;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .maintenance-card p {
            color: #6b7280;
            font-size: 17px;
            line-height: 1.9;
            margin-bottom: 30px;
        }

        .maintenance-badge {
            display: inline-block;
            background: #fff7ed;
            color: #c2410c;
            padding: 10px 22px;
            border-radius: 999px;
            font-weight: 600;
            border: 1px solid #fdba74;
        }

        @media(max-width:768px) {
            .maintenance-card {
                padding: 40px 25px;
            }

            .maintenance-card h1 {
                font-size: 26px;
            }

            .maintenance-card p {
                font-size: 15px;
            }
        }
    </style>

    <div class="maintenance-page">
        <div class="maintenance-card">

            <div class="maintenance-icon">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>

            <h1>🚧 النظام تحت الصيانة</h1>

            <p>
                نعمل حالياً على تطوير هذه الصفحة وإضافة المزيد من المميزات لتحسين تجربة الاستخدام.
                <br>
                نعتذر عن أي إزعاج، وسيتم إتاحتها في أقرب وقت ممكن.
            </p>

            <span class="maintenance-badge">
                تحت التطوير
            </span>

        </div>
    </div>
@endsection --}}
