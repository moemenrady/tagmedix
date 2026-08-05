@extends('layouts.app')

@section('title', 'إنشاء طلب جديد')
@section('page_title', 'إضافة طلب جديد للشركة')

@push('styles')
    <style>
        .custom-select-dropdown {
            max-height: 260px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .custom-select-dropdown::-webkit-scrollbar {
            width: 6px;
        }
        .custom-select-dropdown::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
@endpush

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

                    <!-- بيانات العميل والطلب -->
                    <div class="order-item-card modern-card bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-50 pb-4">
                            <span class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg shadow-inner">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-gray-800">بيانات العميل والطلب</h3>
                                <p class="text-xs text-gray-400 mt-0.5">أدخل المعلومات الأساسية للتواصل والشحن بحقول واضحة ومريحة</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">اسم العميل <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">مصدر الطلب <span class="text-red-500">*</span></label>
                                <select name="source" required
                                    class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                                    <option value="new" {{ old('source') == 'new' ? 'selected' : '' }}>طلب جديد (تفصيل)</option>
                                    <option value="from_stock" {{ old('source') == 'from_stock' ? 'selected' : '' }}>من المخزن جاهز</option>
                                    <option value="replacement" {{ old('source') == 'replacement' ? 'selected' : '' }}>طلب استبدال</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">رقم الهاتف الأساسي <span class="text-red-500">*</span></label>
                                <input type="text" name="phone_1" value="{{ old('phone_1') }}" required
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-semibold text-gray-800 font-sans tracking-wide">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">رقم هاتف إضافي</label>
                                <input type="text" name="phone_2" value="{{ old('phone_2') }}"
                                    class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-semibold text-gray-800 font-sans tracking-wide">
                            </div>

                            <input type="hidden" name="address[country]" id="country_hidden" value="{{ old('address.country', 'مصر') }}">
                            <input type="hidden" name="address[city]" id="city_hidden" value="{{ old('address.city') }}">

                            <div class="relative">
    <label class="block text-xs font-bold text-gray-600 mb-2">المحافظة <span class="text-red-500">*</span></label>
    <input type="hidden" name="address[governorate]" id="governorate_id_hidden" value="{{ old('address.governorate') }}" required>
    
    <div id="gov_trigger" class="w-full h-[46px] px-4 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-between cursor-pointer hover:bg-white focus-within:ring-2 focus-within:ring-[#8A704C] transition-all">
        <span id="gov_selected_text" class="text-sm font-medium text-gray-400">اختر أو ابحث عن المحافظة...</span>
        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200" id="gov_arrow"></i>
    </div>

    <div id="gov_dropdown" class="hidden absolute top-full right-0 left-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-2 animate-fade-in">
        <div class="relative mb-2">
            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input type="text" id="gov_search_input" placeholder="ابحث باسم المحافظة..." class="w-full pl-3 pr-8 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#8A704C]">
        </div>
        <div id="gov_options_list" class="custom-select-dropdown space-y-1">
            @foreach($governments as $gov)
                @php
                    $isObject = is_array($gov);
                    $govName = trim($isObject ? ($gov['name'] ?? '') : $gov);
                    $govId = $isObject ? ($gov['id'] ?? $govName) : $govName;
                @endphp
                <div data-id="{{ $govId }}" data-name="{{ $govName }}" class="gov-option text-xs font-medium text-gray-700 px-3 py-2.5 rounded-xl hover:bg-[#8A704C]/10 hover:text-[#8A704C] cursor-pointer transition-all flex items-center justify-between">
                    <span>{{ $govName }}</span>
                    <i class="fa-solid fa-check text-[#8A704C] hidden"></i>
                </div>
            @endforeach
        </div>
    </div>
</div>

                            <!-- المنطقة / الحي -->
                            <div class="relative">
                                <label id="area_field_label" class="block text-xs font-bold text-gray-600 mb-2">المنطقة / الحي <span class="text-red-500">*</span></label>
                                <input type="hidden" name="address[area]" id="area_id_hidden" value="{{ old('address.area') }}" required>
                                
                                <div id="area_trigger" class="w-full h-[46px] px-4 bg-gray-100 border border-gray-200 rounded-xl flex items-center justify-between cursor-not-allowed opacity-70 transition-all">
                                    <span id="area_selected_text" class="text-sm font-medium text-gray-400">اختر المحافظة أولاً...</span>
                                    <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200" id="area_arrow"></i>
                                </div>

                                <div id="area_dropdown" class="hidden absolute top-full right-0 left-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-2 animate-fade-in">
                                    <div class="relative mb-2">
                                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" id="area_search_input" placeholder="ابحث باسم المنطقة..." class="w-full pl-3 pr-8 py-2 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-[#8A704C]">
                                    </div>
                                    <div id="area_options_list" class="custom-select-dropdown space-y-1">
                                        <div class="text-xs text-gray-400 p-3 text-center">يرجى اختيار المحافظة لعرض المناطق</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-2">رابط / حساب إنستغرام</label>
                                <div class="relative flex items-center">
                                    <span class="absolute right-4 text-pink-600 text-base"><i class="fa-brands fa-instagram"></i></span>
                                    <input type="text" name="instagram_profile" value="{{ old('instagram_profile') }}"
                                        placeholder="@username"
                                        class="w-full h-[46px] pr-11 pl-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm text-left font-mono"
                                        dir="ltr">
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-600 mb-2">العنوان بالتفصيل <span class="text-red-500">*</span></label>
                                <textarea name="address[full_address]" rows="2" required placeholder="اسم الشارع، رقم العقار، الدور، علامة مميزة..."
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 resize-none min-h-[70px]">{{ old('address.full_address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- منتجات ومحتويات الطلب -->
                    <div class="order-item-card modern-card bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-gray-50 pb-4">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg shadow-inner">
                                    <i class="fa-solid fa-basket-shopping"></i>
                                </span>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-gray-800">منتجات ومحتويات الطلب</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">يمكنك إضافة منتج واحد أو عدة منتجات وتخصيصها بالكامل</p>
                                </div>
                            </div>

                            <button type="button" id="addItemBtn"
                                class="modern-card bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center justify-center gap-2 shadow-sm transform hover:-translate-y-0.5">
                                <i class="fa-solid fa-circle-plus text-base"></i> إضافة منتج للطلب
                            </button>
                        </div>

                        <div id="itemsContainer" class="space-y-5"></div>
                    </div>

                </div>

                <!-- الملخص المالي -->
                <div class="space-y-6 lg:sticky lg:top-6">
                    <div class="bg-[#111827] text-gray-300 rounded-2xl shadow-xl border border-gray-800 p-5 sm:p-6">
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-800 pb-4">
                            <span class="w-10 h-10 rounded-xl bg-gray-800 text-[#8A704C] flex items-center justify-center text-lg shadow-inner">
                                <i class="fa-solid fa-calculator"></i>
                            </span>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white">الملخص المالي</h3>
                                <p class="text-xs text-gray-500 mt-0.5">متابعة الحسابات الإجمالية للطلب فورياً</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="flex justify-between items-center bg-gray-900/50 p-4 rounded-xl border border-gray-800 shadow-inner">
                                <span class="text-sm font-semibold text-gray-400">إجمالي قيمة المنتجات:</span>
                                <div class="text-left">
                                    <span id="totalPriceDisplay" class="text-2xl font-black text-white">0.00</span>
                                    <span class="text-xs text-[#8A704C] font-bold mr-1">ج.م</span>
                                </div>
                                <input type="hidden" name="total_price" id="total_price_hidden" value="0">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 mb-2">المبلغ المدفوع مقدمًا (ج.م)</label>
                                <div class="relative flex items-center">
                                    <span class="absolute right-4 text-emerald-500"><i class="fa-solid fa-money-bill-wave"></i></span>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount" value="0" min="0"
                                        class="w-full h-[48px] pr-11 pl-4 py-3 bg-gray-900 border border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] transition-all text-base font-bold text-white text-left font-mono"
                                        dir="ltr">
                                </div>
                            </div>

                            <div class="flex justify-between items-center bg-red-950/20 p-4 rounded-xl border border-red-900/30">
                                <span class="text-sm font-semibold text-red-400/80">المبلغ المتبقي للتحصيل:</span>
                                <div class="text-left">
                                    <span id="remainingPriceDisplay" class="text-xl font-bold text-red-400">0.00</span>
                                    <span class="text-xs text-red-400 mr-1">ج.م</span>
                                </div>
                                <input type="hidden" name="remaining_amount" id="remaining_amount_hidden" value="0">
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

    <!-- Template إضافة عنصر ديناميكي -->
    <template id="itemTemplate">
        <div class="order-item-card modern-card bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm relative transition-all hover:shadow-md mb-5 group">

            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-50">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-sm font-bold shadow-inner">
                        <i class="fa-solid fa-box-open"></i>
                    </span>
                    <h4 class="text-base font-bold text-gray-800">تفاصيل المنتج</h4>
                </div>
                <button type="button" class="remove-item-btn text-gray-400 hover:text-red-600 hover:bg-red-50 w-8 h-8 rounded-xl flex items-center justify-center transition-all" title="إلغاء المنتج">
                    <i class="fa-solid fa-trash-can text-lg"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع البنطلون <span class="text-red-500">*</span></label>
                    <select name="items[{index}][pant_type_id]" required class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        <option value="">اختر نوع البنطلون</option>
                        @foreach ($pantTypes as $pantType)
                            <option value="{{ $pantType->id }}">{{ $pantType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">نوع المنتج <span class="text-red-500">*</span></label>
                    <select name="items[{index}][type_id]" required class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        <option value="">اختر نوع المنتج</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">الستايل</label>
                    <select name="items[{index}][style_id]" class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        @foreach ($styles as $style)
                            <option value="{{ $style->id }}">{{ $style->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">اللون</label>
                    <select name="items[{index}][color_id]" class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        @foreach ($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">المقاس</label>
                    <select name="items[{index}][size_id]" class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        @foreach ($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-2">الكم</label>
                    <select name="items[{index}][sleeve]" class="w-full h-[46px] px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800 cursor-pointer">
                        <option value="long">كم طويل</option>
                        <option value="short">كم قصير</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 mb-3">الحسابات (الكمية - السعر - الإجمالي)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">الكمية</label>
                            <input type="number" name="items[{index}][quantity]" value="1" min="1" required class="item-qty w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-500 mb-2">سعر القطعة</label>
                            <input type="number" step="0.01" name="items[{index}][price]" value="0" min="0" required class="item-price w-full h-[52px] px-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-lg font-bold font-mono focus:outline-none focus:ring-2 focus:ring-[#8A704C]" dir="ltr">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-emerald-600 mb-2">الإجمالي</label>
                            <div class="w-full h-[52px] flex items-center justify-center bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-lg font-black font-mono">
                                <span class="item-total-display">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 mb-2">ملاحظات خاصة ومقاسات مخصصة لهذه القطعة (إن وجد)</label>
                    <input type="text" name="items[{index}][notes]" placeholder="أدخل أي ملاحظات للتفصيل أو التعديل هنا..." class="w-full h-[46px] px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:bg-white transition-all text-sm font-medium text-gray-800">
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

        const govTrigger = document.getElementById('gov_trigger');
        const govDropdown = document.getElementById('gov_dropdown');
        const govSearchInput = document.getElementById('gov_search_input');
        const govOptionsList = document.getElementById('gov_options_list');
        const govSelectedText = document.getElementById('gov_selected_text');
        const govHiddenInput = document.getElementById('governorate_id_hidden');
        const cityHiddenInput = document.getElementById('city_hidden');
        const countryHiddenInput = document.getElementById('country_hidden');
        const areaFieldLabel = document.getElementById('area_field_label');
        const govArrow = document.getElementById('gov_arrow');
        const INTERNATIONAL_GOV = 'شحن دولي';

        function isInternationalShipping() {
            return govHiddenInput.value.trim() === INTERNATIONAL_GOV;
        }

        function setDomesticDefaults() {
            if (countryHiddenInput) countryHiddenInput.value = 'مصر';
            if (areaFieldLabel) {
                areaFieldLabel.innerHTML = 'المنطقة / الحي <span class="text-red-500">*</span>';
            }
        }

        function prepareInternationalGov(clearCountry = true) {
            if (clearCountry && countryHiddenInput) countryHiddenInput.value = '';
            if (clearCountry && cityHiddenInput) cityHiddenInput.value = '';
            if (areaFieldLabel) {
                areaFieldLabel.innerHTML = 'الدولة <span class="text-red-500">*</span>';
            }
        }

        const areaTrigger = document.getElementById('area_trigger');
        const areaDropdown = document.getElementById('area_dropdown');
        const areaSearchInput = document.getElementById('area_search_input');
        const areaOptionsList = document.getElementById('area_options_list');
        const areaSelectedText = document.getElementById('area_selected_text');
        const areaHiddenInput = document.getElementById('area_id_hidden');
        const areaArrow = document.getElementById('area_arrow');

        let itemIndex = 0;
        let isAreaEnabled = false;

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
            if (!govDropdown.classList.contains('hidden')) {
                govSearchInput.focus();
            }
        });

        govSearchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const options = govOptionsList.querySelectorAll('.gov-option');
            options.forEach(opt => {
                const text = opt.innerText.trim().toLowerCase();
                opt.style.display = text.includes(query) ? 'flex' : 'none';
            });
        });

        govOptionsList.querySelectorAll('.gov-option').forEach(opt => {
    opt.addEventListener('click', function() {
        const govId = this.getAttribute('data-id');
        const govName = this.getAttribute('data-name');

        govHiddenInput.value = govName;
        if (cityHiddenInput) {
            cityHiddenInput.value = '';
        }

        if (govName === INTERNATIONAL_GOV) {
            prepareInternationalGov(true);
        } else {
            setDomesticDefaults();
        }
        
        // 2. تحديث الشكل المظهر
        govSelectedText.innerText = govName;
        govSelectedText.classList.remove('text-gray-400');
        govSelectedText.classList.add('text-gray-800');

        govOptionsList.querySelectorAll('.gov-option').forEach(o => o.querySelector('i').classList.add('hidden'));
        this.querySelector('i').classList.remove('hidden');

        govDropdown.classList.add('hidden');
        govArrow.classList.remove('rotate-180');

        // 3. استدعاء المناطق باستخدام الـ ID أو الاسم
        loadAreasForGovernorate(govId, govName);
    });
});

function loadAreasForGovernorate(govId, govName) {
    areaHiddenInput.value = '';
    areaSelectedText.innerText = 'جاري تحميل المناطق...';
    areaSelectedText.classList.add('text-gray-400');
    
    areaTrigger.classList.remove('cursor-not-allowed', 'opacity-70', 'bg-gray-100');
    areaTrigger.classList.add('cursor-pointer', 'hover:bg-white');
    isAreaEnabled = true;

    // استخدام الـ ID المتاح وإذا لم يوجد يتم إرسال الاسم
    const targetParam = govId || govName;

    fetch(`/orders/get-areas/${encodeURIComponent(targetParam)}`)
        .then(res => res.json())
        .then(res => {
            const areas = Array.isArray(res) ? res : (res.feed || res.data || []);
            areaOptionsList.innerHTML = '';

            if (!areas || areas.length === 0) {
                areaSelectedText.innerText = 'لا توجد مناطق متاحة';
                areaOptionsList.innerHTML = '<div class="text-xs text-gray-400 p-3 text-center">لا توجد مناطق متاحة لهذه المحافظة</div>';
                return;
            }

            areaSelectedText.innerText = 'اختر أو ابحث عن المنطقة...';

            areas.forEach(area => {
                const isObj = typeof area === 'object' && area !== null;
                const areaName = (isObj ? (area.name || area.title || '') : String(area ?? '')).trim();

                if (!areaName || /^\d+$/.test(areaName)) {
                    return;
                }

                const item = document.createElement('div');
                item.className = 'area-option text-xs font-medium text-gray-700 px-3 py-2.5 rounded-xl hover:bg-[#8A704C]/10 hover:text-[#8A704C] cursor-pointer transition-all flex items-center justify-between';
                item.setAttribute('data-value', areaName);
                item.innerHTML = `<span>${areaName}</span><i class="fa-solid fa-check text-[#8A704C] hidden"></i>`;
                
                item.addEventListener('click', function() {
                    areaHiddenInput.value = areaName;

                    if (isInternationalShipping()) {
                        if (countryHiddenInput) countryHiddenInput.value = areaName;
                        if (cityHiddenInput) cityHiddenInput.value = '';
                    } else if (cityHiddenInput) {
                        cityHiddenInput.value = areaName;
                    }

                    areaSelectedText.innerText = areaName;
                    areaSelectedText.classList.remove('text-gray-400');
                    areaSelectedText.classList.add('text-gray-800');

                    areaOptionsList.querySelectorAll('.area-option').forEach(o => o.querySelector('i').classList.add('hidden'));
                    this.querySelector('i').classList.remove('hidden');

                    areaDropdown.classList.add('hidden');
                    areaArrow.classList.remove('rotate-180');
                });

                areaOptionsList.appendChild(item);
            });
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
            if (!areaDropdown.classList.contains('hidden')) {
                areaSearchInput.focus();
            }
        });

        areaSearchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const options = areaOptionsList.querySelectorAll('.area-option');
            options.forEach(opt => {
                const text = opt.innerText.trim().toLowerCase();
                opt.style.display = text.includes(query) ? 'flex' : 'none';
            });
        });

        function addNewItemRow() {
            let itemHtml = template.replace(/{index}/g, itemIndex);
            container.insertAdjacentHTML('beforeend', itemHtml);
            itemIndex++;

            bindRowCalculations();
            calculateFullOrderSummary();
        }

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

        addNewItemRow();

        const oldGovName = govHiddenInput.value.trim();
        if (oldGovName) {
            if (oldGovName === INTERNATIONAL_GOV) {
                prepareInternationalGov(false);
            } else {
                setDomesticDefaults();
            }

            govSelectedText.innerText = oldGovName;
            govSelectedText.classList.remove('text-gray-400');
            govSelectedText.classList.add('text-gray-800');

            const matchingGov = Array.from(govOptionsList.querySelectorAll('.gov-option'))
                .find(opt => opt.getAttribute('data-name') === oldGovName);

            if (matchingGov) {
                matchingGov.querySelector('i').classList.remove('hidden');
                loadAreasForGovernorate(matchingGov.getAttribute('data-id'), oldGovName);

                const oldAreaName = areaHiddenInput.value.trim();
                if (oldAreaName) {
                    setTimeout(() => {
                        const matchingArea = Array.from(areaOptionsList.querySelectorAll('.area-option'))
                            .find(opt => opt.getAttribute('data-value') === oldAreaName || opt.innerText.trim() === oldAreaName);

                        if (matchingArea) {
                            areaHiddenInput.value = oldAreaName;
                            if (isInternationalShipping() && countryHiddenInput) {
                                countryHiddenInput.value = oldAreaName;
                            }
                            areaSelectedText.innerText = oldAreaName;
                            areaSelectedText.classList.remove('text-gray-400');
                            areaSelectedText.classList.add('text-gray-800');
                            matchingArea.querySelector('i').classList.remove('hidden');
                        }
                    }, 500);
                }
            }
        }
    });
</script>
@endpush