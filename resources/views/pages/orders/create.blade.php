@extends('layouts.app')

@section('title', 'إنشاء طلب جديد')
@section('page_title', 'إضافة طلب جديد للشركة')

@section('content')
    <style>
        /* ================================
                       Custom Select Dropdown & Scrollbar
                    ================================= */
        .custom-select-dropdown {
            max-height: 240px;
            overflow-y: auto;
            overflow-x: hidden;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
            padding: 4px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
            scroll-behavior: smooth;
        }

        .custom-select-dropdown::-webkit-scrollbar {
            width: 5px;
        }

        .custom-select-dropdown::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-select-dropdown::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .custom-select-dropdown::-webkit-scrollbar-thumb:hover {
            background: #8A704C;
        }

        /* Options */
        .gov-option,
        .area-option {
            min-width: 0;
            min-height: 42px;
            padding: 9px 10px !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid transparent;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease, transform .12s ease, opacity .15s ease;
        }

        .gov-option:hover,
        .area-option:hover {
            transform: translateY(-1px);
            border-color: rgba(138, 112, 76, .18);
        }

        .gov-option span,
        .area-option span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Animations */
        @keyframes dropdownLocationIn {
            from {
                opacity: 0;
                transform: translateY(-5px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        #gov_dropdown,
        #area_dropdown,
        #search_results_dropdown {
            animation: dropdownLocationIn .16s ease-out;
            transform-origin: top center;
        }

        /* Search Inputs */
        #gov_search_input,
        #area_search_input {
            height: 40px;
            font-size: 13px;
        }

        /* =================================
                       Responsive Styles
                    ================================= */
        @media (max-width: 767px) {
            .order-page-content {
                padding-bottom: calc(120px + env(safe-area-inset-bottom)) !important;
            }

            .custom-select-dropdown {
                max-height: 220px;
                gap: 5px;
            }

            .gov-option,
            .area-option {
                min-height: 40px;
                padding: 8px 9px !important;
                font-size: 11px !important;
            }

            #gov_dropdown,
            #area_dropdown {
                left: -2px;
                right: -2px;
            }
        }

        @media (min-width: 768px) {
            .custom-select-dropdown {
                max-height: 250px;
            }
        }

        @media (min-width: 1200px) {
            .custom-select-dropdown {
                max-height: 260px;
            }
        }
    </style>

    <div class="order-page-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" dir="rtl">

        <!-- Alerts -->
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

        @if (session('fail'))
            <div class="mb-6 p-5 bg-red-50 border-r-4 border-red-500 text-red-700 rounded-2xl shadow-sm animate-fade-in">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <p class="font-bold">حدث خطأ:</p>
                </div>
                <p class="text-sm mr-5">{{ session('fail') }}</p>
            </div>
        @endif

        <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                <div class="lg:col-span-2 space-y-6">
                    <!-- Customer and Order Data Section -->
                    <div
                        class="order-item-card modern-card bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">

                        <div class="flex items-center gap-3 mb-6 border-b border-gray-50 pb-4">
                            <span
                                class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg shadow-inner">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-800">بيانات العميل والطلب</h3>
                                <p class="text-xs text-gray-400 mt-0.5">أدخل رقم الهاتف ليتم البحث وتعبئة البيانات تلقائياً
                                </p>
                            </div>
                        </div>

                        <!-- Order Reference -->
                        <div class="mb-5">
                            <div class="relative rounded-2xl border border-[#8A704C]/15 bg-[#8A704C]/[0.035] p-4 sm:p-5">
                                <div class="flex items-start gap-3 mb-3">
                                    <span
                                        class="w-9 h-9 shrink-0 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center">
                                        <i class="fa-solid fa-paste text-sm"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <label class="block text-sm font-bold text-gray-700">
                                            مرجع الطلب
                                            <span class="text-[10px] font-medium text-gray-400 mr-1">للاستخدام الداخلي
                                                فقط</span>
                                        </label>
                                        <p class="text-[11px] sm:text-xs text-gray-400 mt-1 leading-relaxed">
                                            يمكنك لصق أي نص طويل هنا للرجوع إليه أثناء إنشاء الطلب. هذا الحقل لا يتم حفظه أو
                                            إرساله مع الطلب.
                                        </p>
                                    </div>
                                </div>
                                <textarea id="order_reference" rows="3" autocomplete="off" spellcheck="false"
                                    placeholder="الصق النص المرجعي هنا..."
                                    class="order-reference-input w-full px-4 py-3.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C]/30 focus:border-[#8A704C]/50 transition-all duration-200 text-sm leading-7 text-gray-700 placeholder:text-gray-400 resize-none overflow-hidden min-h-[90px] max-h-[420px]"></textarea>
                                <div class="flex items-center justify-between mt-2 px-1">
                                    <span class="text-[10px] text-gray-400">
                                        <i class="fa-solid fa-eye ml-1"></i> مرئي لك فقط أثناء إنشاء الطلب
                                    </span>
                                    <button type="button" id="clearOrderReference"
                                        class="hidden text-[11px] font-semibold text-gray-400 hover:text-red-500 transition-colors">
                                        <i class="fa-solid fa-xmark ml-1"></i> مسح
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 🌟 Vanilla JS Customer Search Wrapper 🌟 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5" id="customer_search_wrapper">

                            <!-- حقل رقم الهاتف الأساسي (بحث تفاعلي) -->
                            <div class="md:col-span-2 relative mb-2">
                                <label class="block text-xs font-bold text-[#8A704C] mb-2">
                                    <i class="fa-solid fa-magnifying-glass mr-1"></i> رقم الهاتف الأساسي (بحث وتعبئة
                                    تلقائية) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="phone_1" id="phone_1_input" value="{{ old('phone_1') }}"
                                        required autocomplete="off" dir="ltr"
                                        class="w-full h-[50px] pl-4 pr-11 py-3 bg-white border-2 border-[#8A704C]/30 rounded-xl focus:outline-none focus:ring-0 focus:border-[#8A704C] transition-all text-base font-bold text-gray-800 tracking-wider shadow-sm placeholder:text-gray-300 placeholder:font-normal placeholder:text-sm text-left font-mono"
                                        placeholder="+20... / 01... / أدخل رقم الهاتف...">

                                    <!-- مؤشر التحميل -->
                                    <div id="search_spinner" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                                        <i class="fa-solid fa-circle-notch fa-spin text-[#8A704C]"></i>
                                    </div>
                                </div>

                                <!-- نتائج البحث المنسدلة -->
                                <div id="search_results_dropdown"
                                    class="hidden absolute z-50 w-full bg-white border border-[#8A704C]/20 rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] mt-2 max-h-72 overflow-y-auto divide-y divide-gray-50">
                                    <!-- سيتم إضافة النتائج هنا بواسطة JavaScript -->
                                </div>
                            </div>

                            <!-- باقي بيانات العميل -->
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">اسم العميل <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" id="customer_name_input"
                                    value="{{ old('customer_name') }}" required
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">مصدر الطلب <span
                                        class="text-red-500">*</span></label>
                                <select name="source" id="orderSourceSelect" required
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
                                <label class="block text-xs font-bold text-gray-600 mb-2">رقم هاتف إضافي</label>
                                <input type="text" name="phone_2" id="phone_2_input" value="{{ old('phone_2') }}"
                                    dir="ltr"
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-semibold text-gray-800 font-mono tracking-wide text-left"
                                    placeholder="+20... / 01...">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">رابط / حساب إنستغرام</label>
                                <div class="relative flex items-center">
                                    <span class="absolute right-4 text-pink-600 text-base"><i
                                            class="fa-brands fa-instagram"></i></span>
                                    <input type="text" name="instagram_profile" id="instagram_input"
                                        value="{{ old('instagram_profile') }}" placeholder="@username"
                                        class="w-full h-[46px] pr-11 pl-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm text-left font-mono"
                                        dir="ltr">
                                </div>
                            </div>

                            <!-- Governorate Custom Dropdown -->
                            <div class="relative">
                                <label class="block text-xs font-bold text-gray-600 mb-2">المحافظة <span
                                        class="text-red-500">*</span></label>
                                <input type="hidden" name="address[governorate]" id="governorate_id_hidden"
                                    value="{{ old('address.governorate') }}" required>
                                <div id="gov_trigger"
                                    class="w-full h-[46px] px-4 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-between cursor-pointer hover:bg-white focus-within:ring-2 focus-within:ring-[#8A704C] transition-all">
                                    <span id="gov_selected_text" class="text-sm font-medium text-gray-400">اختر أو ابحث عن
                                        المحافظة...</span>
                                    <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                                        id="gov_arrow"></i>
                                </div>
                                <div id="gov_dropdown"
                                    class="hidden absolute top-full right-0 left-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-2 animate-fade-in">
                                    <div class="relative mb-2">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" id="gov_search_input" placeholder="ابحث باسم المحافظة..."
                                            class="w-full pl-3 pr-8 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#8A704C]">
                                    </div>
                                    <div id="gov_options_list" class="custom-select-dropdown">
                                        @foreach ($governments as $gov)
                                            <div data-value="{{ $gov['id'] }}"
                                                class="gov-option text-xs font-medium text-gray-700 px-3 py-2.5 rounded-xl hover:bg-[#8A704C]/10 hover:text-[#8A704C] cursor-pointer transition-all flex items-center justify-between">
                                                <span>{{ trim($gov['name']) }}</span>
                                                <i class="fa-solid fa-check text-[#8A704C] hidden"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Area Custom Dropdown -->
                            <div class="relative">
                                <label class="block text-xs font-bold text-gray-600 mb-2">المنطقة / الحي <span
                                        class="text-red-500">*</span></label>
                                <input type="hidden" name="address[area]" id="area_id_hidden"
                                    value="{{ old('address.area') }}" required>
                                <div id="area_trigger"
                                    class="w-full h-[46px] px-4 bg-gray-100 border border-gray-200 rounded-xl flex items-center justify-between cursor-not-allowed opacity-70 transition-all">
                                    <span id="area_selected_text" class="text-sm font-medium text-gray-400">اختر المحافظة
                                        أولاً...</span>
                                    <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200"
                                        id="area_arrow"></i>
                                </div>
                                <div id="area_dropdown"
                                    class="hidden absolute top-full right-0 left-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-2 animate-fade-in">
                                    <div class="relative mb-2">
                                        <i
                                            class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" id="area_search_input" placeholder="ابحث باسم المنطقة..."
                                            class="w-full pl-3 pr-8 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#8A704C]">
                                    </div>
                                    <div id="area_options_list" class="custom-select-dropdown">
                                        <div class="text-xs text-gray-400 p-3 text-center">يرجى اختيار المحافظة لعرض
                                            المناطق</div>
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-600 mb-2">العنوان بالتفصيل <span
                                        class="text-red-500">*</span></label>
                                <textarea name="address[full_address]" id="full_address_input" rows="2" required
                                    placeholder="اسم الشارع، رقم العقار، الدور، علامة مميزة..."
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 resize-none min-h-[70px]">{{ old('address.full_address') }}</textarea>
                            </div>
                        </div> <!-- End Vanilla JS Wrapper -->
                    </div>

                    <!-- Products Section -->
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
                        </div>
                        <div id="itemsContainer" class="space-y-5"></div>
                    </div>
                </div>

                <!-- Financial Summary -->
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

    <!-- Floating Add Item Button -->
    <button type="button" id="floatingAddItemBtn"
        class="fixed bottom-24 lg:bottom-10 right-6 lg:right-10 z-50 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.2)] transition-all transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-emerald-600/30 group animate-slide-up"
        title="إضافة منتج جديد">
        <i class="fa-solid fa-plus text-xl sm:text-2xl group-hover:rotate-90 transition-transform duration-300"></i>
    </button>

    <!-- Product Item Template -->
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
                    <i class="fa-solid fa-trash-can text-lg pointer-events-none"></i>
                </button>
            </div>

            <!-- قسم البحث عن المنتج -->
            <div
                class="stock-search-container hidden mb-6 p-5 bg-[#8A704C]/5 border border-[#8A704C]/20 rounded-2xl relative">
                <div class="absolute top-0 right-0 w-1.5 h-full bg-[#8A704C] rounded-r-2xl"></div>
                <label class="block text-sm font-bold text-[#8A704C] mb-2">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> ابحث عن المنتج الجاهز في المخزن
                </label>
                <select name="items[{index}][inventory_finished_product_id]"
                    class="stock-select w-full h-[46px] px-4 bg-white border border-[#8A704C]/30 rounded-xl focus:ring-2 focus:ring-[#8A704C] text-sm font-medium text-gray-800">
                    <option value="">-- اختر أو ابحث عن المنتج (الاسم أو الكود) --</option>
                    @foreach ($finishedProducts as $product)
                        <option value="{{ $product->id }}" data-pant="{{ $product->pant_type_id }}"
                            data-type="{{ $product->type_id }}" data-style="{{ $product->style_id }}"
                            data-color="{{ $product->color_id }}" data-size="{{ $product->size_id }}"
                            data-sleeve="{{ $product->sleeve_type_id }}">
                            {{ $product->product_name }} - كود: {{ $product->product_code }} (الكمية المتاحة:
                            {{ (float) $product->available_qty }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-500 mt-2">عند اختيار المنتج سيتم ملء التفاصيل تلقائياً وتفريغ خانات
                    الماديات والكمية لتعبئتها.</p>
            </div>

            <!-- تفاصيل المنتج -->
            <div
                class="design-details-container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-5 transition-all duration-300">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع البنطلون <span
                            class="text-red-500">*</span></label>
                    <select name="items[{index}][pant_type_id]" required
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800">
                        <option value="">اختر نوع البنطلون</option>
                        @foreach ($pantTypes as $pantType)
                            <option value="{{ $pantType->id }}">{{ $pantType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع المنتج <span
                            class="text-red-500">*</span></label>
                    <select name="items[{index}][type_id]" required
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800">
                        <option value="">اختر نوع المنتج</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">الستايل</label>
                    <select name="items[{index}][style_id]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800">
                        @foreach ($styles as $style)
                            <option value="{{ $style->id }}">{{ $style->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">اللون</label>
                    <select name="items[{index}][color_id]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800">
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">المقاس</label>
                    <select name="items[{index}][size_id]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800">
                        @foreach ($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع الكم</label>
                    <select name="items[{index}][sleeve_type_id]"
                        class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm text-gray-800">
                        <option value="">اختر نوع الكم</option>
                        @foreach ($sleeveType as $sleeve)
                            <option value="{{ $sleeve->id }}">{{ $sleeve->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- الحسابات والملاحظات -->
            <div class="grid grid-cols-1 gap-5">
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 mb-3">الحسابات (الكمية - السعر - الإجمالي)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">الكمية</label>
                            <input type="number" name="items[{index}][quantity]" required value="1"
                                class="item-qty w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">سعر القطعة</label>
                            <input type="number" step="0.01" name="items[{index}][price]" required
                                class="item-price w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]"
                                dir="ltr">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-emerald-600 mb-2">الإجمالي</label>
                            <div
                                class="w-full h-[52px] flex items-center justify-center bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-lg font-black font-mono">
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
                        class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-sm font-medium text-gray-800">
                </div>
            </div>

            <!-- زر إضافة منتج جديد (تحت الملاحظات) -->
            <div class="mt-5 pt-4 border-t border-gray-50 flex justify-end">
                <button type="button" class="add-item-btn-inline modern-card bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition-all flex items-center justify-center gap-2 shadow-sm transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-circle-plus text-base pointer-events-none"></i> إضافة منتج آخر
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Elements for Customer Search (Vanilla JS) ---
            const phone1Input = document.getElementById('phone_1_input');
            const searchSpinner = document.getElementById('search_spinner');
            const searchResultsDropdown = document.getElementById('search_results_dropdown');
            const customerNameInput = document.getElementById('customer_name_input');
            const phone2Input = document.getElementById('phone_2_input');
            const instagramInput = document.getElementById('instagram_input');
            const fullAddressInput = document.getElementById('full_address_input');

            // --- Debounce Function ---
            function debounce(func, delay) {
                let timeoutId;
                return function(...args) {
                    if (timeoutId) clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => {
                        func.apply(this, args);
                    }, delay);
                };
            }

            // --- Select Customer Function ---
            function selectCustomer(customer) {
                customerNameInput.value = customer.name || '';
                phone1Input.value = customer.phone_1 || '';
                phone2Input.value = customer.phone_2 || '';
                instagramInput.value = customer.instagram_profile || '';

                // تعبئة أحدث عنوان للعميل
                const addresses = customer.order_addresses || customer.orderAddresses || [];
                if (addresses.length > 0) {
                    const lastAddress = addresses[0];
                    fullAddressInput.value = lastAddress.full_address || '';

                    const areaVal = lastAddress.area || lastAddress.city || '';
                    if (window.setAddressDropdowns) {
                        window.setAddressDropdowns(lastAddress.governorate, areaVal);
                    }
                }

                // Hide dropdown
                searchResultsDropdown.classList.add('hidden');
            }

            // --- Phone Inputs Auto-Cleaning ---
            function cleanPhoneInput(e) {
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const originalLength = this.value.length;
                
                // Convert Arabic-Indic numerals to Latin digits
                let val = this.value.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
                                    .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
                
                // Allow leading '+' and digits only
                const hasPlus = val.trimStart().startsWith('+');
                const digits = val.replace(/\D/g, '');
                
                this.value = hasPlus ? ('+' + digits) : digits;
                
                // Adjust cursor position if necessary
                if (this.value.length < originalLength) {
                    const diff = originalLength - this.value.length;
                    this.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
                } else {
                    this.setSelectionRange(start, end);
                }
            }

            phone1Input.addEventListener('input', cleanPhoneInput);
            if (phone2Input) {
                phone2Input.addEventListener('input', cleanPhoneInput);
            }

            // --- Fetch & Render Customers ---
            const handleSearch = debounce(async (e) => {
                const query = e.target.value.trim();

                if (query.length < 3) {
                    searchResultsDropdown.classList.add('hidden');
                    searchResultsDropdown.innerHTML = '';
                    return;
                }

                searchSpinner.classList.remove('hidden');

                try {
                    const response = await fetch(
                    `/customers/search?phone=${encodeURIComponent(query)}`);
                    if (!response.ok) throw new Error('Network response was not ok');

                    const result = await response.json();

                    if (result.success && result.data && result.data.length > 0) {
                        searchResultsDropdown.innerHTML = ''; // Clear old results

                        result.data.forEach(customer => {
                            // Create Dropdown Item
                            const itemDiv = document.createElement('div');
                            itemDiv.className =
                                "p-3.5 hover:bg-[#8A704C]/5 cursor-pointer transition-colors group flex items-center gap-3";

                            const phone2Html = customer.phone_2 ?
                                `<span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-mono">${customer.phone_2}</span>` :
                                '';

                            itemDiv.innerHTML = `
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-[#8A704C]/10 group-hover:text-[#8A704C] transition-colors shrink-0">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-gray-800 text-sm truncate">${customer.name}</div>
                                    <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                        <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-mono">${customer.phone_1}</span>
                                        ${phone2Html}
                                    </div>
                                </div>
                                <div class="shrink-0 text-xs text-[#8A704C] font-bold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                    اختيار <i class="fa-solid fa-arrow-left text-[10px]"></i>
                                </div>
                            `;

                            // Add Click Listener
                            itemDiv.addEventListener('click', () => selectCustomer(customer));
                            searchResultsDropdown.appendChild(itemDiv);
                        });

                        searchResultsDropdown.classList.remove('hidden');
                    } else {
                        searchResultsDropdown.classList.add('hidden');
                    }
                } catch (error) {
                    console.error("Error fetching customers:", error);
                } finally {
                    searchSpinner.classList.add('hidden');
                }
            }, 300);

            // Listen to Phone 1 input
            phone1Input.addEventListener('input', handleSearch);

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!phone1Input.contains(e.target) && !searchResultsDropdown.contains(e.target)) {
                    searchResultsDropdown.classList.add('hidden');
                }
            });


            // ==========================================
            // --- DOM Elements (Order Management) ---
            // ==========================================
            const container = document.getElementById('itemsContainer');
            const template = document.getElementById('itemTemplate').innerHTML;
            const addItemBtn = document.getElementById('addItemBtn');
            const floatingAddItemBtn = document.getElementById('floatingAddItemBtn');

            const sourceSelect = document.getElementById('orderSourceSelect');

            const totalPriceDisplay = document.getElementById('totalPriceDisplay');
            const totalPriceHidden = document.getElementById('total_price_hidden');
            const paidAmountInput = document.getElementById('paid_amount');
            const remainingPriceDisplay = document.getElementById('remainingPriceDisplay');
            const remainingAmountHidden = document.getElementById('remaining_amount_hidden');

            const govTrigger = document.getElementById('gov_trigger');
            const govDropdown = document.getElementById('gov_dropdown');
            const govSearchInput = document.getElementById('gov_search_input');
            const govOptionsList = document.getElementById('gov_options_list');
            const govSelectedText = document.getElementById('gov_selected_text');
            const govHiddenInput = document.getElementById('governorate_id_hidden');
            const govArrow = document.getElementById('gov_arrow');

            const areaTrigger = document.getElementById('area_trigger');
            const areaDropdown = document.getElementById('area_dropdown');
            const areaSearchInput = document.getElementById('area_search_input');
            const areaOptionsList = document.getElementById('area_options_list');
            const areaSelectedText = document.getElementById('area_selected_text');
            const areaHiddenInput = document.getElementById('area_id_hidden');
            const areaArrow = document.getElementById('area_arrow');

            const orderReference = document.getElementById('order_reference');
            const clearOrderReference = document.getElementById('clearOrderReference');

            let itemIndex = 0;
            let isAreaEnabled = false;

            // ✨ تحديث القوائم المنسدلة للمحافظة والحي تلقائياً ✨
            window.setAddressDropdowns = function(govName, areaName) {
                if (!govName) return;

                let govId = null;
                govOptionsList.querySelectorAll('.gov-option').forEach(opt => {
                    const spanText = opt.querySelector('span').innerText.trim();
                    if (spanText === govName) {
                        govId = opt.getAttribute('data-value');
                        opt.querySelector('i').classList.remove('hidden');
                    } else {
                        opt.querySelector('i').classList.add('hidden');
                    }
                });

                if (govId) {
                    govHiddenInput.value = govName;
                    govSelectedText.innerText = govName;
                    govSelectedText.classList.replace('text-gray-400', 'text-gray-800');

                    loadAreasForGovernorate(govId, areaName);
                }
            };

            // --- إظهار/إخفاء سيكشن البحث في المخزن ---
            function toggleStockSearchVisibility() {
                const isFromStock = sourceSelect.value === 'from_stock';
                document.querySelectorAll('.order-item-card').forEach(card => {
                    const searchContainer = card.querySelector('.stock-search-container');
                    const designContainer = card.querySelector('.design-details-container');

                    if (searchContainer && designContainer) {
                        if (isFromStock) {
                            searchContainer.classList.remove('hidden');
                            designContainer.classList.add('pointer-events-none', 'opacity-60', 'bg-gray-50',
                                'rounded-xl', 'p-3');
                        } else {
                            searchContainer.classList.add('hidden');
                            designContainer.classList.remove('pointer-events-none', 'opacity-60',
                                'bg-gray-50', 'rounded-xl', 'p-3');

                            const stockSelect = searchContainer.querySelector('.stock-select');
                            if (stockSelect) stockSelect.value = '';
                        }
                    }
                });
            }

            sourceSelect.addEventListener('change', toggleStockSearchVisibility);

            // --- Dropdowns Logic ---
            document.addEventListener('click', function(e) {
                if (!govTrigger.contains(e.target) && !govDropdown.contains(e.target)) {
                    govDropdown.classList.add('hidden');
                    govArrow.classList.remove('rotate-180');
                }
                if (!areaTrigger.contains(e.target) && !areaDropdown.contains(e.target)) {
                    areaDropdown.classList.add('hidden');
                    areaArrow.classList.remove('rotate-180');
                }
            });

            govTrigger.addEventListener('click', () => {
                govDropdown.classList.toggle('hidden');
                govArrow.classList.toggle('rotate-180');
                if (!govDropdown.classList.contains('hidden')) govSearchInput.focus();
            });

            govSearchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                govOptionsList.querySelectorAll('.gov-option').forEach(opt => {
                    const text = opt.textContent.trim().toLowerCase();
                    opt.style.display = text.includes(query) ? '' : 'none';
                });
            });

            govOptionsList.addEventListener('click', function(e) {
                const option = e.target.closest('.gov-option');
                if (!option) return;

                const val = option.getAttribute('data-value');
                const text = option.querySelector('span').innerText.trim();

                govHiddenInput.value = text;
                govSelectedText.innerText = text;
                govSelectedText.classList.replace('text-gray-400', 'text-gray-800');

                govOptionsList.querySelectorAll('.gov-option i').forEach(i => i.classList.add('hidden'));
                option.querySelector('i').classList.remove('hidden');

                govDropdown.classList.add('hidden');
                govArrow.classList.remove('rotate-180');

                loadAreasForGovernorate(val);
            });

            function loadAreasForGovernorate(govId, preselectedArea = null) {
                areaHiddenInput.value = '';
                areaSelectedText.innerText = 'جاري تحميل المناطق...';
                areaSelectedText.classList.add('text-gray-400');
                areaTrigger.classList.remove('cursor-not-allowed', 'opacity-70', 'bg-gray-100');
                areaTrigger.classList.add('cursor-pointer', 'hover:bg-white');
                isAreaEnabled = true;

                fetch(`/orders/get-areas/${govId}`)
                    .then(res => res.json())
                    .then(res => {
                        const areas = Array.isArray(res) ? res : (res.feed || res.data || []);
                        areaOptionsList.innerHTML = '';

                        if (!areas || areas.length === 0) {
                            areaSelectedText.innerText = 'لا توجد مناطق متاحة';
                            areaOptionsList.innerHTML =
                                '<div class="text-xs text-gray-400 p-3 text-center">لا توجد مناطق متاحة لهذه المحافظة</div>';
                            return;
                        }

                        areaSelectedText.innerText = 'اختر أو ابحث عن المنطقة...';
                        let areaFound = false;

                        areas.forEach(area => {
                            const cleanName = area.name.trim();
                            const item = document.createElement('div');
                            item.className =
                                'area-option text-xs font-medium text-gray-700 rounded-xl hover:bg-[#8A704C]/10 hover:text-[#8A704C] cursor-pointer';
                            item.setAttribute('data-value', area.id);
                            item.innerHTML =
                                `<span>${cleanName}</span><i class="fa-solid fa-check text-[#8A704C] hidden"></i>`;

                            if (preselectedArea && cleanName === preselectedArea) {
                                item.querySelector('i').classList.remove('hidden');
                                areaHiddenInput.value = cleanName;
                                areaSelectedText.innerText = cleanName;
                                areaSelectedText.classList.replace('text-gray-400', 'text-gray-800');
                                areaFound = true;
                            }
                            areaOptionsList.appendChild(item);
                        });

                        if (preselectedArea && !areaFound) {
                            areaHiddenInput.value = preselectedArea;
                            areaSelectedText.innerText = preselectedArea;
                            areaSelectedText.classList.replace('text-gray-400', 'text-gray-800');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        areaSelectedText.innerText = 'حدث خطأ في تحميل المناطق';
                    });
            }

            areaTrigger.addEventListener('click', () => {
                if (!isAreaEnabled) return;
                areaDropdown.classList.toggle('hidden');
                areaArrow.classList.toggle('rotate-180');
                if (!areaDropdown.classList.contains('hidden')) areaSearchInput.focus();
            });

            areaSearchInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                areaOptionsList.querySelectorAll('.area-option').forEach(opt => {
                    const text = opt.textContent.trim().toLowerCase();
                    opt.style.display = text.includes(query) ? '' : 'none';
                });
            });

            areaOptionsList.addEventListener('click', function(e) {
                const option = e.target.closest('.area-option');
                if (!option) return;

                const cleanName = option.querySelector('span').innerText.trim();
                areaHiddenInput.value = cleanName;
                areaSelectedText.innerText = cleanName;
                areaSelectedText.classList.replace('text-gray-400', 'text-gray-800');

                areaOptionsList.querySelectorAll('.area-option i').forEach(i => i.classList.add('hidden'));
                option.querySelector('i').classList.remove('hidden');

                areaDropdown.classList.add('hidden');
                areaArrow.classList.remove('rotate-180');
            });

            // --- Dynamic Items Logic ---
            function calculateRowTotal(row) {
                const qtyInput = row.querySelector('.item-qty');
                const priceInput = row.querySelector('.item-price');
                const totalDisplay = row.querySelector('.item-total-display');

                if (qtyInput && priceInput && totalDisplay) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    totalDisplay.textContent = (qty * price).toFixed(2);
                }
            }

            function calculateFullOrderSummary() {
                let orderTotal = 0;
                container.querySelectorAll('.order-item-card').forEach(row => {
                    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    orderTotal += (qty * price);
                });

                const paidAmount = parseFloat(paidAmountInput.value) || 0;
                const remainingAmount = orderTotal - paidAmount;

                totalPriceDisplay.textContent = orderTotal.toFixed(2);
                totalPriceHidden.value = orderTotal.toFixed(2);
                remainingPriceDisplay.textContent = remainingAmount.toFixed(2);
                remainingAmountHidden.value = remainingAmount.toFixed(2);
            }

            container.addEventListener('input', function(e) {
                if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
                    calculateRowTotal(e.target.closest('.order-item-card'));
                    calculateFullOrderSummary();
                }
            });

            container.addEventListener('change', function(e) {
                if (e.target.classList.contains('stock-select')) {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const row = e.target.closest('.order-item-card');

                    if (selectedOption && selectedOption.value) {
                        const fields = {
                            'pant_type_id': selectedOption.dataset.pant,
                            'type_id': selectedOption.dataset.type,
                            'style_id': selectedOption.dataset.style,
                            'color_id': selectedOption.dataset.color,
                            'size_id': selectedOption.dataset.size,
                            'sleeve_type_id': selectedOption.dataset.sleeve
                        };

                        for (const [field, val] of Object.entries(fields)) {
                            const input = row.querySelector(`[name*="[${field}]"]`);
                            if (input) {
                                input.value = val || '';
                            }
                        }

                        const qtyInput = row.querySelector('.item-qty');
                        const priceInput = row.querySelector('.item-price');
                        if (qtyInput) qtyInput.value = '1';
                        if (priceInput) priceInput.value = '';

                        calculateRowTotal(row);
                        calculateFullOrderSummary();
                    }
                }
            });

            container.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-item-btn');
                if (removeBtn) {
                    removeBtn.closest('.order-item-card').remove();
                    calculateFullOrderSummary();
                    return;
                }

                const addItemInlineBtn = e.target.closest('.add-item-btn-inline');
                if (addItemInlineBtn) {
                    addNewItemRow(null, true);
                    return;
                }
            });

            paidAmountInput.addEventListener('input', calculateFullOrderSummary);

            function addNewItemRow(oldData = null, autoFocus = false) {
                container.insertAdjacentHTML('beforeend', template.replace(/{index}/g, itemIndex));
                const newRow = container.lastElementChild;

                const isFromStock = sourceSelect.value === 'from_stock';
                if (isFromStock) {
                    newRow.querySelector('.stock-search-container').classList.remove('hidden');
                    newRow.querySelector('.design-details-container').classList.add('pointer-events-none',
                        'opacity-60', 'bg-gray-50', 'rounded-xl', 'p-3');
                }

                if (oldData) {
                    const fields = ['inventory_finished_product_id', 'pant_type_id', 'type_id', 'style_id',
                        'color_id', 'size_id', 'sleeve_type_id',
                        'quantity', 'price', 'notes'
                    ];
                    fields.forEach(field => {
                        const input = newRow.querySelector(`[name="items[${itemIndex}][${field}]"]`);
                        if (input && oldData[field] !== undefined) {
                            input.value = oldData[field];
                        }
                    });
                    calculateRowTotal(newRow);
                }

                itemIndex++;
                calculateFullOrderSummary();

                if (autoFocus) {
                    setTimeout(() => {
                        newRow.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        newRow.classList.add('ring-2', 'ring-[#8A704C]', 'shadow-lg');
                        setTimeout(() => newRow.classList.remove('ring-2', 'ring-[#8A704C]', 'shadow-lg'),
                            1200);
                        const firstInput = newRow.querySelector('select');
                        if (firstInput) firstInput.focus({
                            preventScroll: true
                        });
                    }, 150);
                }
            }

            // Initial add item button is handled via the container delegation now (except if it's outside, but we removed it)
            if (floatingAddItemBtn) {
                floatingAddItemBtn.addEventListener('click', () => addNewItemRow(null, true));
            }

            // --- Order Reference Logic ---
            if (orderReference) {
                const autoResizeReference = () => {
                    orderReference.style.height = 'auto';
                    orderReference.style.height = Math.min(Math.max(orderReference.scrollHeight, 90), 420) +
                        'px';
                };
                const updateClearButton = () => {
                    clearOrderReference.classList.toggle('hidden', orderReference.value.trim().length === 0);
                };

                orderReference.addEventListener('input', () => {
                    autoResizeReference();
                    updateClearButton();
                });
                orderReference.addEventListener('paste', () => setTimeout(() => {
                    autoResizeReference();
                    updateClearButton();
                }, 0));

                clearOrderReference.addEventListener('click', () => {
                    orderReference.value = '';
                    autoResizeReference();
                    updateClearButton();
                    orderReference.focus();
                });

                autoResizeReference();
            }

            // --- Initial Data Load ---
            const oldGov = @json(old('address.governorate', ''));
            const oldArea = @json(old('address.area', ''));

            if (oldGov) {
                window.setAddressDropdowns(oldGov, oldArea);
            }

            const oldItems = @json(old('items', []));
            if (oldItems && Object.keys(oldItems).length > 0) {
                Object.values(oldItems).forEach(itemData => addNewItemRow(itemData, false));
            } else {
                addNewItemRow(null, false);
            }

            toggleStockSearchVisibility();
        });
    </script>
@endpush
