@extends('layouts.app')

@section('title', 'المدفوعات')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* تصميم سكرول بار أنيق للجدول ليتناسب مع عرض كل البيانات */
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

        /* لدعم اتجاه الصفحة حسب اللغة ديناميكياً للعناصر الداخلية */
        html[dir="ltr"] .rtl-flip {
            transform: scaleX(-1);
        }

        html[dir="ltr"] .text-right-lang {
            text-align: left !important;
        }

        html[dir="rtl"] .text-right-lang {
            text-align: right !important;
        }

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

    @php
        // جلب قائمة الطلبات لاستخدامها في نموذج التعديل داخل النافذة المنبثقة
        $allOrders = \App\Models\Order::latest()->get();

        // تحضير البيانات لـ AlpineJS لضمان تفاعل البطاقات الحسابية ونافذة التعديل
        $paymentData = $payments->map(function ($p) {
            return [
                'id' => $p->id,
                'amount' => $p->amount,
                'payment_date' => $p->payment_date ? $p->payment_date->format('Y-m-d') : '',
                'note' => $p->note,
                'order_id' => $p->order_id,
                'order' => $p->order
                    ? [
                        'id' => $p->order->id,
                        'order_number' => $p->order->order_number,
                        'customer_name' => $p->order->customer_name,
                        'remaining_amount' => $p->order?->getRemainingAmount(),
                    ]
                    : null,
                'creator' => $p->creator ? ['name' => $p->creator->name] : null,
            ];
        });
    @endphp

    <div class="space-y-6 pb-10 font-sans w-full" x-data="paymentManager({{ json_encode($paymentData) }})" x-init="initData()">

        <div x-show="isEditModalOpen" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div @click.away="closeEditModal()"
                class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-2xl transform transition-all"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-activeGold"></i>
                        <span data-ar="تعديل الدفعة" data-en="Edit Payment">تعديل الدفعة</span>
                    </h3>
                    <button @click="closeEditModal()"
                        class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitEdit()" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <span data-ar="رقم الطلب" data-en="Order">رقم الطلب</span> <span
                                    class="text-red-500">*</span>
                            </label>
                            <div wire:ignore>
                                <select id="edit_order_id" x-model="editData.order_id" required class="w-full">
                                    <option value=""></option>
                                    @foreach ($allOrders as $ord)
                                        <option value="{{ $ord->id }}">
                                            #{{ $ord->order_number ?? $ord->id }} - {{ $ord->customer_name ?? 'بدون اسم' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <span data-ar="المبلغ" data-en="Amount">المبلغ</span> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none rtl:right-0 rtl:left-auto ltr:left-0 ltr:right-auto rtl:pr-3 ltr:pl-3">
                                    <span class="text-gray-500 sm:text-sm">ج.م</span>
                                </div>
                                <input type="number" step="0.01" min="0" required x-model="editData.amount"
                                    class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 rtl:pr-10 ltr:pl-10 focus:ring-activeGold focus:border-activeGold transition outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <span data-ar="تاريخ الدفع" data-en="Payment Date">تاريخ الدفع</span> <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="date" required x-model="editData.payment_date"
                                class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-activeGold focus:border-activeGold transition outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <span data-ar="ملاحظات" data-en="Notes">ملاحظات</span>
                            </label>
                            <textarea rows="3" x-model="editData.note"
                                class="block w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-activeGold focus:border-activeGold transition outline-none resize-none"></textarea>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeEditModal()"
                            class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                            <span data-ar="إلغاء" data-en="Cancel">إلغاء</span>
                        </button>
                        <button type="submit" :disabled="isSaving"
                            class="px-5 py-2.5 rounded-lg bg-activeGold text-white font-medium hover:bg-[#735A3A] shadow-md transition flex items-center gap-2 disabled:opacity-50">
                            <i class="fa-solid" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                            <span data-ar="حفظ التعديلات" data-en="Save Changes"
                                x-text="isSaving ? (document.documentElement.lang === 'ar' ? 'جاري الحفظ...' : 'Saving...') : (document.documentElement.lang === 'ar' ? 'حفظ التعديلات' : 'Save Changes')">حفظ
                                التعديلات</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.duration.500ms
                class="bg-emerald-50 border-r-4 border-emerald-500 p-4 rounded-xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                    </div>
                    <p class="text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        @endif

        <div>
            <h2 class="text-2xl font-black text-gray-800 flex items-center gap-2">
                <i class="fa-regular fa-credit-card text-activeGold"></i>
                <span data-ar="إدارة المدفوعات الذكية" data-en="Smart Payments Management">إدارة المدفوعات الذكية</span>
            </h2>
            <p class="text-gray-500 text-sm mt-1" data-ar="تابع وحلل جميع حركات الدفع الخاصة بالطلبات"
                data-en="Track and analyze all payment transactions for orders">تابع وحلل جميع حركات الدفع الخاصة بالطلبات
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div
                class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1" data-ar="العمليات المعروضة"
                        data-en="Displayed Transactions">العمليات المعروضة</p>
                    <h3 class="text-3xl font-black text-gray-800" x-text="filteredPayments.length">0</h3>
                </div>
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-receipt text-2xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1" data-ar="إجمالي المحصل (للبحث الحالي)"
                        data-en="Total Collected (Current Search)">إجمالي المحصل (للبحث الحالي)</p>
                    <h3 class="text-2xl font-black text-gray-800">
                        <span x-text="formatCurrency(totalDisplayedAmount)"></span>
                        <span class="text-sm text-gray-400 font-normal" data-ar="ج.م" data-en="EGP">ج.م</span>
                    </h3>
                </div>
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-hand-holding-dollar text-2xl"></i>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center justify-between group">
                <div>
                    <p class="text-gray-500 text-sm font-medium mb-1" data-ar="الدفعات المتوقعة للطلبات المعروضة"
                        data-en="Expected Payments (Displayed Orders)">الدفعات المتوقعة للطلبات المعروضة</p>
                    <h3 class="text-2xl font-black text-gray-800">
                        <span x-text="formatCurrency(totalExpectedAmount)"></span>
                        <span class="text-sm text-gray-400 font-normal" data-ar="ج.م" data-en="EGP">ج.م</span>
                    </h3>
                </div>
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-50 to-rose-100 flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-sack-dollar text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden flex flex-col">
            <div
                class="p-5 border-b border-gray-100 bg-gray-50/50 space-y-4 md:space-y-0 md:flex md:items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('payments.create') }}"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span data-ar="دفعة جديدة" data-en="New Payment">دفعة جديدة</span>
                    </a>
                </div>

                <div class="relative w-full md:w-[400px] group">
                    <div
                        class="absolute inset-y-0 ltr:right-4 rtl:left-4 flex items-center pointer-events-none transition-colors group-focus-within:text-activeGold">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-activeGold"></i>
                    </div>
                    <input type="text" x-model="searchQuery"
                        placeholder="ابحث باسم العميل، رقم الطلب، أو المستلم... | Search..."
                        class="block w-full rtl:pr-11 rtl:pl-11 ltr:pl-11 ltr:pr-11 py-3 border border-gray-200 rounded-xl text-sm focus:ring-4 focus:ring-activeGold/20 focus:border-activeGold bg-white shadow-sm placeholder-gray-400 transition-all">
                    <button x-show="searchQuery.length > 0" @click="searchQuery = ''" style="display: none;"
                        class="absolute inset-y-0 ltr:right-10 rtl:left-4 flex items-center text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-auto custom-scrollbar" style="max-height: 65vh;">
                <table class="w-full text-right-lang text-sm">
                    <thead
                        class="bg-gray-100 text-gray-600 font-bold sticky top-0 z-10 shadow-sm backdrop-blur-md bg-opacity-90">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">#</th>
                            <th class="px-6 py-4 whitespace-nowrap">
                                <i class="fa-solid fa-hashtag text-xs opacity-50 mx-1"></i>
                                <span data-ar="رقم الطلب" data-en="Order No">رقم الطلب</span>
                            </th>
                            <th class="px-6 py-4 whitespace-nowrap">
                                <i class="fa-regular fa-user text-xs opacity-50 mx-1"></i>
                                <span data-ar="العميل" data-en="Customer">العميل</span>
                            </th>
                            <th class="px-6 py-4 whitespace-nowrap">
                                <i class="fa-solid fa-coins text-xs opacity-50 mx-1"></i>
                                <span data-ar="مبلغ الدفعة" data-en="Payment Amount">مبلغ الدفعة</span>
                            </th>
                            <th class="px-6 py-4 whitespace-nowrap">
                                <i class="fa-regular fa-calendar text-xs opacity-50 mx-1"></i>
                                <span data-ar="تاريخ الدفع" data-en="Payment Date">تاريخ الدفع</span>
                            </th>
                            <th class="px-6 py-4 whitespace-nowrap">
                                <i class="fa-solid fa-user-pen text-xs opacity-50 mx-1"></i>
                                <span data-ar="بواسطة" data-en="Created By">بواسطة</span>
                            </th>
                            <th class="px-6 py-4 text-center whitespace-nowrap" data-ar="إجراءات" data-en="Actions">
                                إجراءات
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="(payment, index) in filteredPayments" :key="payment.id">
                            <tr class="hover:bg-blue-50/30 transition-colors group" x-transition>

                                <td class="px-6 py-4 text-gray-400 font-medium" x-text="index + 1"></td>

                                <td class="px-6 py-4 font-bold text-gray-900">
                                    <template x-if="payment.order">
                                        <a :href="`/orders/${payment.order.id}`"
                                            class="hover:text-activeGold transition-colors flex items-center gap-2 group-hover:underline">
                                            <span x-text="payment.order.order_number"></span>
                                            <i
                                                class="fa-solid fa-arrow-up-right-from-square text-[10px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity rtl-flip"></i>
                                        </a>
                                    </template>
                                    <template x-if="!payment.order"><span class="text-gray-400">-</span></template>
                                </td>

                                <td class="px-6 py-4">
                                    <template x-if="payment.order">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold shrink-0"
                                                x-text="payment.order.customer_name.substring(0, 1)"></div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-800"
                                                    x-text="payment.order.customer_name"></span>
                                                <span
                                                    class="text-xs text-rose-500 font-medium mt-0.5 flex items-center gap-1">
                                                    <span data-ar="متبقي:" data-en="Remaining:">متبقي:</span>
                                                    <span x-text="formatCurrency(payment.order.remaining_amount)"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!payment.order"><span class="text-gray-400">-</span></template>
                                </td>

                                <td class="px-6 py-4">
                                    <div
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 font-black border border-emerald-100">
                                        <span x-text="formatCurrency(payment.amount)"></span>
                                        <span class="text-xs ml-1 font-normal" data-ar="ج.م" data-en="EGP">ج.م</span>
                                    </div>
                                    <template x-if="payment.note">
                                        <p class="text-[11px] text-gray-400 mt-1 truncate max-w-[150px]"
                                            :title="payment.note">
                                            <i class="fa-regular fa-comment-dots"></i> <span x-text="payment.note"></span>
                                        </p>
                                    </template>
                                </td>

                                <td class="px-6 py-4 text-gray-600 text-sm font-medium">
                                    <span x-text="payment.payment_date"></span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded bg-purple-50 flex items-center justify-center text-purple-600 text-xs">
                                            <i class="fa-solid fa-user-shield"></i>
                                        </div>
                                        <span class="font-medium text-gray-700 text-sm"
                                            x-text="payment.creator ? payment.creator.name : '-'"></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <template x-if="payment.order">
                                            <a :href="`/orders/${payment.order.id}`"
                                                class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-blue-50 hover:text-blue-600 flex items-center justify-center transition-colors"
                                                title="عرض الطلب">
                                                <i class="fa-solid fa-box-open"></i>
                                            </a>
                                        </template>

                                        <button @click="openEditModal(payment.id)"
                                            class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-activeGold hover:text-white flex items-center justify-center transition-colors"
                                            title="تعديل">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <form :action="`/payments/${payment.id}`" method="POST" class="m-0 inline"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذه الدفعة؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition-colors"
                                                title="حذف">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="filteredPayments.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-file-invoice-dollar text-6xl mb-4 opacity-50"></i>
                                    <p class="text-lg font-medium text-gray-500" data-ar="لا توجد نتائج"
                                        data-en="No results found">لا توجد نتائج</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($payments->hasPages())
                <div class="p-4 border-t border-gray-100 bg-white">
                    {{ $payments->links() }}
                </div>
            @endif

            <div class="p-4 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-400 font-medium">
                <span data-ar="نهاية قائمة المدفوعات" data-en="End of payments list">نهاية قائمة المدفوعات</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentManager', (initialPayments) => ({
                searchQuery: '',
                payments: initialPayments,

                // متغيرات نافذة التعديل
                isEditModalOpen: false,
                isSaving: false,
                tsInstance: null, // Tom Select Instance
                editData: {
                    id: '',
                    order_id: '',
                    amount: '',
                    payment_date: '',
                    note: ''
                },

                initData() {
                    // تفاعل اللغة مع Tom Select 
                    const btn = document.getElementById("languageBtn");
                    if (btn) {
                        btn.addEventListener('click', () => {
                            if (this.tsInstance) {
                                const lang = localStorage.getItem("lang") || "ar";
                                this.tsInstance.settings.placeholder = lang === 'ar' ?
                                    'ابحث عن الطلب...' : 'Search for order...';
                                this.tsInstance.inputState();
                            }
                        });
                    }
                },

                get filteredPayments() {
                    if (this.searchQuery.trim() === '') return this.payments;
                    const q = this.searchQuery.toLowerCase();
                    return this.payments.filter(p => {
                        const orderNum = p.order && p.order.order_number ? p.order
                            .order_number.toLowerCase() : '';
                        const customer = p.order && p.order.customer_name ? p.order
                            .customer_name.toLowerCase() : '';
                        const creator = p.creator && p.creator.name ? p.creator.name
                            .toLowerCase() : '';
                        return orderNum.includes(q) || customer.includes(q) || creator
                            .includes(q);
                    });
                },

                get totalDisplayedAmount() {
                    return this.filteredPayments.reduce((sum, p) => sum + parseFloat(p.amount || 0),
                        0);
                },

                get totalExpectedAmount() {
                    const uniqueOrders = new Map();
                    this.filteredPayments.forEach(p => {
                        if (p.order && p.order.remaining_amount) {
                            uniqueOrders.set(p.order.id, parseFloat(p.order
                                .remaining_amount));
                        }
                    });
                    let sum = 0;
                    uniqueOrders.forEach(amount => sum += amount);
                    return sum;
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }).format(amount);
                },

                // --- وظائف التعديل ---
                openEditModal(id) {
                    const payment = this.payments.find(p => p.id === id);
                    if (!payment) return;

                    // تعبئة البيانات
                    this.editData = {
                        id: payment.id,
                        order_id: payment.order_id,
                        amount: payment.amount,
                        payment_date: payment.payment_date,
                        note: payment.note || ''
                    };

                    this.isEditModalOpen = true;

                    // تهيئة TomSelect 
                    this.$nextTick(() => {
                        if (!this.tsInstance) {
                            const el = document.getElementById('edit_order_id');
                            const lang = localStorage.getItem("lang") || "ar";
                            this.tsInstance = new TomSelect(el, {
                                create: false,
                                sortField: {
                                    field: "text",
                                    direction: "asc"
                                },
                                placeholder: lang === 'ar' ? 'ابحث عن الطلب...' :
                                    'Search for order...',
                            });
                        }
                        this.tsInstance.setValue(this.editData.order_id);
                    });
                },

                closeEditModal() {
                    this.isEditModalOpen = false;
                },

                async submitEdit() {
                    this.isSaving = true;

                    // تحديث order_id من TomSelect في حال تغيرت برمجياً ولم تلتقطها Alpine
                    if (this.tsInstance) {
                        this.editData.order_id = this.tsInstance.getValue();
                    }

                    try {
                        const response = await fetch(`/payments/${this.editData.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.editData)
                        });

                        const result = await response.json();

                        if (response.ok) {
                            const currentLang = document.documentElement.lang || 'ar';
                            Swal.fire({
                                icon: 'success',
                                title: currentLang === 'ar' ? 'تم التعديل!' : 'Updated!',
                                text: currentLang === 'ar' ? 'تم تعديل الدفعة بنجاح.' :
                                    'Payment updated successfully.',
                                confirmButtonColor: '#8A704C',
                                confirmButtonText: currentLang === 'ar' ? 'حسناً' : 'OK'
                            }).then(() => {
                                // تحديث الواجهة أو إعادة تحميل الصفحة لضمان تحديث الإجماليات بشكل سليم من السيرفر
                                window.location.reload();
                            });
                        } else if (response.status === 422) {
                            // أخطاء Validation
                            let errorMsgs = [];
                            for (let key in result.errors) errorMsgs.push(result.errors[key][0]);
                            Swal.fire({
                                icon: 'error',
                                title: 'خطأ',
                                text: errorMsgs.join('\n'),
                                confirmButtonColor: '#8A704C'
                            });
                        } else {
                            throw new Error('Server error');
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ!',
                            text: 'حدث خطأ أثناء الاتصال بالخادم.',
                            confirmButtonColor: '#8A704C'
                        });
                    } finally {
                        this.isSaving = false;
                    }
                }
            }));
        });
    </script>
@endpush
