@extends('layouts.app')

@section('content')
    <div class="p-4 md:p-8 max-w-3xl mx-auto min-h-screen bg-gray-50/30" x-data="{ mounted: false }" x-init="setTimeout(() => mounted = true, 100)">

        {{-- رأس الصفحة وزر العودة --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 mb-8" x-show="mounted"
            x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0" x-cloak>

            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight flex items-center gap-3">
                    <span class="w-2 h-8 rounded-full bg-[#3f1d7a]"></span>
                    إضافة توريد شركة شحن
                </h1>
                <p class="text-sm text-gray-500 mt-2 font-medium">تسجيل المبالغ الموردة من شركات الشحن بشكل مباشر وسريع</p>
            </div>

            <a href="{{ route('payments.index') }}"
                class="group flex items-center gap-3 text-sm font-bold text-gray-500 hover:text-[#3f1d7a] transition-all duration-300 w-fit">
                <span class="group-hover:-translate-x-1 transition-transform duration-300">العودة للمركز</span>
                <div
                    class="w-9 h-9 rounded-full bg-white shadow-sm border border-gray-200 group-hover:border-[#3f1d7a]/30 group-hover:bg-[#3f1d7a]/5 flex items-center justify-center transition-all duration-300">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </div>
            </a>
        </div>

        {{-- فورم إدخال التوريد --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden relative transform transition-all duration-500"
            x-show="mounted" x-transition:enter="transition ease-out duration-700 delay-100"
            x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>

            {{-- شريط زينة علوي بلون الثيم --}}
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#3f1d7a] to-[#5d31a6]"></div>

            <form action="{{ route('payments.company-supply.store') }}" method="POST" class="p-6 sm:p-10 space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- حقل المبلغ --}}
                    <div class="space-y-2 group">
                        <label for="amount"
                            class="block text-sm font-bold text-gray-700 group-focus-within:text-[#3f1d7a] transition-colors">
                            المبلغ المورد <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            {{-- القيمة التي يتم إرسالها للـ Laravel --}}
                            <input type="hidden" name="amount" id="amount">

                            {{-- القيمة التي يراها المستخدم --}}
                            <input type="text" id="amount_display" inputmode="decimal" autocomplete="off"
                                value="{{ old('amount') ? number_format((float) old('amount'), 2, '.', ',') : '' }}"
                                required
                                class="w-full text-lg font-black text-gray-900 border-gray-200 bg-gray-50 hover:bg-gray-100/50 focus:bg-white rounded-xl focus:ring-4 focus:ring-[#3f1d7a]/15 focus:border-[#3f1d7a] pl-14 pr-4 py-4 transition-all duration-300 @error('amount') border-red-500 ring-red-500/20 bg-red-50 @enderror"
                                placeholder="0.00">

                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none border-r border-gray-200 my-3 pr-4">
                                <span
                                    class="text-gray-400 font-bold text-sm group-focus-within:text-[#3f1d7a] transition-colors">
                                    ج.م
                                </span>
                            </div>
                        </div>

                        @error('amount')
                            <p class="mt-2 text-sm text-red-500 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    {{-- حقل التاريخ --}}
                    <div class="space-y-2 group">
                        <label for="payment_date"
                            class="block text-sm font-bold text-gray-700 group-focus-within:text-[#3f1d7a] transition-colors">
                            تاريخ التوريد <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" name="payment_date" id="payment_date"
                                value="{{ old('payment_date', now()->toDateString()) }}" required
                                class="w-full text-base font-semibold text-gray-900 border-gray-200 bg-gray-50 hover:bg-gray-100/50 focus:bg-white rounded-xl focus:ring-4 focus:ring-[#3f1d7a]/15 focus:border-[#3f1d7a] px-4 py-4 transition-all duration-300 cursor-pointer @error('payment_date') border-red-500 ring-red-500/20 bg-red-50 @enderror">
                        </div>
                        @error('payment_date')
                            <p class="mt-2 text-sm text-red-500 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- حقل الملاحظات --}}
                <div class="space-y-2 group">
                    <label for="note"
                        class="block text-sm font-bold text-gray-700 group-focus-within:text-[#3f1d7a] transition-colors">
                        ملاحظات وتفاصيل <span class="text-gray-400 font-normal">(اختياري)</span>
                    </label>
                    <div class="relative">
                        <textarea name="note" id="note" rows="4"
                            placeholder="اكتب هنا تفاصيل التوريد مثل رقم البوليصة، رقم إيصال التحويل البنكي، أو اسم المندوب..."
                            class="w-full text-sm font-medium text-gray-900 border-gray-200 bg-gray-50 hover:bg-gray-100/50 focus:bg-white rounded-xl focus:ring-4 focus:ring-[#3f1d7a]/15 focus:border-[#3f1d7a] p-4 transition-all duration-300 resize-none @error('note') border-red-500 ring-red-500/20 bg-red-50 @enderror">{{ old('note') }}</textarea>
                    </div>
                    @error('note')
                        <p class="mt-2 text-sm text-red-500 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- أزرار الحفظ والإلغاء --}}
                <div
                    class="pt-6 mt-4 flex flex-col-reverse sm:flex-row items-center justify-end border-t border-gray-100 gap-4">
                    <a href="{{ route('payments.index') }}"
                        class="w-full sm:w-auto px-8 py-3.5 text-sm font-bold text-gray-600 bg-white border-2 border-gray-200 hover:bg-gray-50 hover:border-gray-300 rounded-xl transition-all duration-300 text-center focus:ring-4 focus:ring-gray-100 active:scale-95">
                        إلغاء الأمر
                    </a>

                    <button type="submit"
                        class="w-full sm:w-auto px-8 py-3.5 text-sm font-bold text-white bg-[#3f1d7a] hover:bg-[#321761] rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-lg shadow-[#3f1d7a]/30 hover:shadow-[#3f1d7a]/50 hover:-translate-y-0.5 active:scale-95 focus:ring-4 focus:ring-[#3f1d7a]/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>حفظ التوريد واعتماد المبلغ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const displayInput = document.getElementById('amount_display');
            const amountInput = document.getElementById('amount');

            function formatAmount(value) {
                // السماح بالأرقام والنقطة فقط
                value = value.replace(/[^\d.]/g, '');

                // منع وجود أكثر من نقطة عشرية
                const parts = value.split('.');
                let integerPart = parts[0] || '';
                let decimalPart = parts[1] ?? '';

                // حد أقصى رقمين بعد العلامة العشرية
                decimalPart = decimalPart.substring(0, 2);

                // إزالة الأصفار الزائدة من البداية
                if (integerPart.length > 1) {
                    integerPart = integerPart.replace(/^0+(?=\d)/, '');
                }

                // إضافة فواصل الآلاف
                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                return decimalPart !== '' ?
                    `${integerPart}.${decimalPart}` :
                    integerPart;
            }

            function getRawValue(value) {
                return value.replace(/,/g, '');
            }

            displayInput.addEventListener('input', function() {
                const cursorPosition = this.selectionStart;
                const oldValue = this.value;

                // عدد الفواصل قبل مكان المؤشر
                const commasBeforeCursor = (
                    oldValue.substring(0, cursorPosition).match(/,/g) || []
                ).length;

                const rawValue = getRawValue(oldValue);
                const formattedValue = formatAmount(rawValue);

                this.value = formattedValue;

                // تحديث القيمة الفعلية المرسلة
                amountInput.value = getRawValue(formattedValue);

                // الحفاظ على مكان المؤشر بشكل مريح
                let newCursorPosition = cursorPosition;

                const newCommasBeforeCursor = (
                    formattedValue.substring(0, newCursorPosition).match(/,/g) || []
                ).length;

                newCursorPosition += newCommasBeforeCursor - commasBeforeCursor;

                this.setSelectionRange(
                    newCursorPosition,
                    newCursorPosition
                );
            });

            // تجهيز القيمة عند تحميل الصفحة
            if (displayInput.value) {
                displayInput.value = formatAmount(displayInput.value);
                amountInput.value = getRawValue(displayInput.value);
            }

            // التأكد من تحديث القيمة قبل إرسال الفورم
            displayInput.form?.addEventListener('submit', function() {
                amountInput.value = getRawValue(displayInput.value);
            });
        });
    </script>
    <style>
        /* تخصيص شكل اختيار التاريخ للمتصفحات */
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: 0.2s;
        }

        input[type="date"]:focus::-webkit-calendar-picker-indicator,
        input[type="date"]:hover::-webkit-calendar-picker-indicator {
            opacity: 1;
        }
    </style>

@endsection
