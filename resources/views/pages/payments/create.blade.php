@extends('layouts.app') <!-- تأكد من اسم ملف الـ Layout الخاص بك -->

@section('title', 'إضافة دفعة')

@section('content')
    <!-- Tom Select CSS للمظهر الاحترافي لقائمة البحث -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.css" rel="stylesheet">
    <!-- SweetAlert2 للتنبيهات الاحترافية -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* تخصيص مظهر Tom Select ليتماشى مع تصميمك واللون الذهبي */
        .ts-control {
            border-radius: 0.5rem !important;
            border-color: #d1d5db !important;
            padding: 0.6rem 1rem !important;
            font-family: inherit !important;
            min-height: 46px !important;
        }

        .ts-control.focus {
            border-color: #8A704C !important;
            box-shadow: 0 0 0 1px #8A704C !important;
        }

        .ts-dropdown {
            border-radius: 0.5rem !important;
            font-family: inherit !important;
            text-align: right;
            /* Default RTL */
        }

        html[dir="ltr"] .ts-dropdown {
            text-align: left;
        }
    </style>

    <div class="max-w-4xl mx-auto w-full">

        <!-- عنوان الصفحة -->
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fa-solid fa-money-bill-wave text-activeGold"></i>
                <span data-ar="تسجيل دفعة جديدة" data-en="Register New Payment">تسجيل دفعة جديدة</span>
            </h2>
            <a href="{{ route('payments.index') }}"
                class="text-sm text-gray-500 hover:text-activeGold transition flex items-center gap-2">
                <span data-ar="العودة للقائمة" data-en="Back to List">العودة للقائمة</span>
                <i class="fa-solid fa-arrow-left-long rtl:rotate-180"></i>
            </a>
        </div>

        <!-- كارت الفورم -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <form id="paymentForm" class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- حقل اختيار الطلب (مع البحث) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span data-ar="رقم الطلب (ابحث برقم الطلب أو اسم العميل)"
                                data-en="Order (Search by Number or Customer)">رقم الطلب (ابحث برقم الطلب أو اسم
                                العميل)</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="order_id" name="order_id" required class="w-full" placeholder="اختر الطلب...">
                            <option value=""></option>
                            @foreach ($orders as $order)
                                <!-- افترضت وجود order_number و customer_name في مودل الطلب، عدلها حسب هيكل بياناتك -->
                                <option value="{{ $order->id }}">
                                    #{{ $order->order_number ?? $order->id }} - {{ $order->customer_name ?? 'بدون اسم' }}
                                </option>
                            @endforeach
                        </select>
                        <p id="error-order_id" class="text-red-500 text-xs mt-1 hidden"></p>
                    </div>

                    <!-- حقل المبلغ -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span data-ar="المبلغ" data-en="Amount">المبلغ</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none rtl:right-0 rtl:left-auto ltr:left-0 ltr:right-auto rtl:pr-3 ltr:pl-3">
                                <span class="text-gray-500 sm:text-sm">ج.م</span>
                            </div>
                            <input type="number" id="amount" name="amount" step="0.01" min="0" required
                                class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 rtl:pr-10 ltr:pl-10 focus:ring-activeGold focus:border-activeGold transition duration-200 outline-none"
                                data-ar-placeholder="أدخل المبلغ..." data-en-placeholder="Enter amount..."
                                placeholder="أدخل المبلغ...">
                        </div>
                        <p id="error-amount" class="text-red-500 text-xs mt-1 hidden"></p>
                    </div>

                    <!-- حقل تاريخ الدفع -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span data-ar="تاريخ الدفع" data-en="Payment Date">تاريخ الدفع</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="payment_date" name="payment_date" required value="{{ date('Y-m-d') }}"
                            class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-activeGold focus:border-activeGold transition duration-200 outline-none">
                        <p id="error-payment_date" class="text-red-500 text-xs mt-1 hidden"></p>
                    </div>

                    <!-- حقل الملاحظات -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <span data-ar="ملاحظات (اختياري)" data-en="Notes (Optional)">ملاحظات (اختياري)</span>
                        </label>
                        <textarea id="note" name="note" rows="3"
                            class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-activeGold focus:border-activeGold transition duration-200 outline-none resize-none"
                            data-ar-placeholder="أضف أي تفاصيل إضافية هنا..." data-en-placeholder="Add any extra details here..."
                            placeholder="أضف أي تفاصيل إضافية هنا..."></textarea>
                        <p id="error-note" class="text-red-500 text-xs mt-1 hidden"></p>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3 mt-8">
                    <button type="button" onclick="window.history.back()"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                        <span data-ar="إلغاء" data-en="Cancel">إلغاء</span>
                    </button>
                    <button type="submit" id="submitBtn"
                        class="px-6 py-2.5 rounded-lg bg-activeGold text-white font-medium hover:bg-[#735A3A] shadow-md hover:shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span data-ar="حفظ الدفعة" data-en="Save Payment">حفظ الدفعة</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // 1. تفعيل Tom Select للبحث في الطلبات
            const lang = localStorage.getItem("lang") || "ar";
            const tomSelectInstance = new TomSelect('#order_id', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: lang === 'ar' ? 'ابحث عن الطلب...' : 'Search for order...',
                render: {
                    no_results: function(data, escape) {
                        return '<div class="no-results p-3 text-gray-500">' + (lang === 'ar' ?
                            'لا توجد نتائج' : 'No results found') + '</div>';
                    }
                }
            });

            // 2. تحديث الـ Placeholders بناءً على اللغة
            function updatePlaceholders(lang) {
                document.querySelectorAll('[data-ar-placeholder]').forEach(el => {
                    el.placeholder = lang === 'ar' ? el.getAttribute('data-ar-placeholder') : el
                        .getAttribute('data-en-placeholder');
                });
                tomSelectInstance.settings.placeholder = lang === 'ar' ? 'ابحث عن الطلب...' : 'Search for order...';
                tomSelectInstance.inputState();
            }

            // ربط تحديث الحقول بزر اللغة الموجود في الـ Layout
            const btn = document.getElementById("languageBtn");
            if (btn) {
                btn.addEventListener('click', () => {
                    setTimeout(() => {
                        updatePlaceholders(localStorage.getItem("lang") || "ar");
                    }, 50);
                });
            }
            updatePlaceholders(lang);

            // 3. التعامل مع إرسال الفورم (AJAX)
            const form = document.getElementById('paymentForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // إعادة ضبط الأخطاء
                document.querySelectorAll('p[id^="error-"]').forEach(el => el.classList.add('hidden'));

                // تجهيز البيانات
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                const currentLang = localStorage.getItem("lang") || "ar";

                // حالة التحميل للزر
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin"></i> <span data-ar="جاري الحفظ..." data-en="Saving...">' +
                    (currentLang === 'ar' ? 'جاري الحفظ...' : 'Saving...') + '</span>';
                submitBtn.disabled = true;

                try {
                    const response = await fetch('{{ route('payments.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.status === 201 || response.ok) {
                        // رسالة نجاح
                        Swal.fire({
                            icon: 'success',
                            title: currentLang === 'ar' ? 'تم الحفظ!' : 'Saved!',
                            text: currentLang === 'ar' ? 'تم تسجيل الدفعة بنجاح.' :
                                'Payment registered successfully.',
                            confirmButtonColor: '#8A704C',
                            confirmButtonText: currentLang === 'ar' ? 'حسناً' : 'OK'
                        }).then(() => {
                            // تفريغ الفورم بعد النجاح
                            form.reset();
                            tomSelectInstance.clear();
                            document.getElementById('payment_date').value = new Date()
                                .toISOString().split('T')[0];
                        });
                    } else if (response.status === 422) {
                        // عرض أخطاء الـ Validation
                        const errors = result.errors;
                        for (const key in errors) {
                            const errorEl = document.getElementById(`error-${key}`);
                            if (errorEl) {
                                errorEl.textContent = errors[key][0];
                                errorEl.classList.remove('hidden');
                            }
                        }
                    } else {
                        throw new Error('Server error');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: currentLang === 'ar' ? 'خطأ!' : 'Error!',
                        text: currentLang === 'ar' ? 'حدث خطأ أثناء حفظ الدفعة.' :
                            'An error occurred while saving.',
                        confirmButtonColor: '#8A704C'
                    });
                } finally {
                    // إعادة الزر لحالته الطبيعية
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            });
        });
    </script>
@endpush
