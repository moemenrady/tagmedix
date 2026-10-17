@extends('layouts.app')

@section('title', 'قسيمة راتب | ' . $payroll->employee->name)
@section('page_title', 'قسيمة راتب الموظف - ' . $payroll->employee->name)

@section('content')
<div class="space-y-6 pb-16 font-sans w-full" x-data="{ isPayModalOpen: false }">

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="bg-green-50 border-r-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm flex justify-between items-center print:hidden">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Actions Toolbar (Hidden on Print) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('payroll.index', ['month' => $payroll->period_month, 'year' => $payroll->period_year]) }}"
                class="p-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition" title="العودة لمسير الرواتب">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-black text-gray-900">
                    قسيمة راتب: {{ $payroll->employee->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">شهر {{ $payroll->period_month }} / {{ $payroll->period_year }} - كود: {{ $payroll->payroll_number }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            @if ($payroll->status === 'draft')
                <form action="{{ route('payroll.approve', $payroll) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>اعتماد الراتب</span>
                    </button>
                </form>
            @endif

            @if ($payroll->status !== 'paid')
                <button @click="isPayModalOpen = true" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>تسجيل صرف وتسليم الراتب</span>
                </button>
            @endif

            <button onclick="window.print()" class="bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i>
                <span>طباعة القسيمة</span>
            </button>
        </div>
    </div>

    <!-- Printable Payslip Sheet -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-md p-8 sm:p-12 space-y-8">

        <!-- Top Header & Branding -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-8 border-b-2 border-gray-100 gap-6">
            <div>
                <h1 class="text-3xl font-black text-[#8A704C] tracking-tight">Tagmedix</h1>
                <p class="text-xs font-bold text-gray-400 mt-1 uppercase tracking-widest">مصنع السكراب والزي الطبي</p>
                <div class="mt-4 text-xs text-gray-600 space-y-1">
                    <p><span class="font-bold text-gray-800">قسيمة راتب شهر:</span> <span class="font-bold text-[#8A704C]">{{ DateTime::createFromFormat('!m', $payroll->period_month)->format('F') }} {{ $payroll->period_year }}</span></p>
                    <p><span class="font-bold text-gray-800">رقم السند:</span> <span class="font-mono text-gray-900 font-bold">{{ $payroll->payroll_number }}</span></p>
                </div>
            </div>

            <!-- Status Badges -->
            <div class="text-left rtl:text-right sm:rtl:text-left space-y-2">
                <div>
                    @if ($payroll->status === 'paid')
                        <span class="px-4 py-1.5 text-xs font-black rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <i class="fa-solid fa-circle-check text-xs"></i> تم الصرف والتسليم
                        </span>
                    @elseif ($payroll->status === 'approved')
                        <span class="px-4 py-1.5 text-xs font-black rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                            <i class="fa-solid fa-clipboard-check text-xs"></i> معتمد وجاهز للصرف
                        </span>
                    @else
                        <span class="px-4 py-1.5 text-xs font-black rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                            <i class="fa-solid fa-pencil text-xs"></i> مسودة قيد المراجعة
                        </span>
                    @endif
                </div>

                @if ($payroll->paid_at)
                    <p class="text-xs text-gray-500">تاريخ الصرف: {{ $payroll->paid_at->format('Y-m-d H:i') }} ({{ $payroll->payment_method }})</p>
                @endif
            </div>
        </div>

        <!-- Employee Info Bar -->
        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs font-bold text-gray-400 mb-1">اسم الموظف</p>
                <p class="font-black text-gray-900 text-base">{{ $payroll->employee->name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $payroll->employee->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 mb-1">نظام المحاسبة</p>
                @if ($payroll->salary_type === 'piece_rate')
                    <span class="font-bold text-purple-700">محاسبة بالإنتاج (القطعة)</span>
                    <p class="text-xs text-gray-500 mt-0.5">إجمالي المنجز: {{ $payroll->completed_pieces_count }} قطعة</p>
                @else
                    <span class="font-bold text-blue-700">راتب شهري ثابت</span>
                    <p class="text-xs text-gray-500 mt-0.5">الأساسي: {{ number_format($payroll->base_salary, 2) }} ج.م</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 mb-1">تاريخ التعيين</p>
                <p class="font-bold text-gray-800">{{ $payroll->employee->hire_date ? $payroll->employee->hire_date->format('Y-m-d') : 'غير محدد' }}</p>
            </div>
        </div>

        <!-- Earnings & Deductions Dual Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- 1. المستحقات والبدلات (Earnings) -->
            <div class="border border-emerald-200/80 rounded-2xl p-5 bg-emerald-50/20 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-black text-emerald-800 pb-3 border-b border-emerald-200 flex items-center justify-between">
                        <span>المستحقات والحوافز (له)</span>
                        <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                    </h3>

                    <div class="divide-y divide-emerald-100 text-sm mt-3">
                        @if ($payroll->salary_type === 'monthly')
                            <div class="py-2.5 flex justify-between items-center">
                                <span class="text-gray-700 font-medium">الراتب الأساسي الشهري</span>
                                <span class="font-bold text-gray-900">{{ number_format($payroll->base_salary, 2) }} ج.م</span>
                            </div>
                        @else
                            <div class="py-2.5 flex justify-between items-center">
                                <div>
                                    <span class="text-gray-700 font-medium">مستحقات إنتاج القطع</span>
                                    <div class="text-[11px] text-gray-400">{{ $payroll->completed_pieces_count }} قطعة منجزة بالورشة</div>
                                </div>
                                <span class="font-bold text-gray-900">{{ number_format($payroll->piece_rate_earnings, 2) }} ج.م</span>
                            </div>
                        @endif

                        @foreach ($payroll->advances->where('type', 'bonus') as $bonus)
                            <div class="py-2.5 flex justify-between items-center">
                                <div>
                                    <span class="text-emerald-700 font-medium">مكافأة / حافز إضافي</span>
                                    @if ($bonus->note)
                                        <div class="text-[11px] text-gray-400">{{ $bonus->note }} ({{ $bonus->date->format('m-d') }})</div>
                                    @endif
                                </div>
                                <span class="font-bold text-emerald-700">+ {{ number_format($bonus->amount, 2) }} ج.م</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-emerald-200 flex justify-between items-center font-black text-emerald-900 text-base mt-4">
                    <span>إجمالي المستحقات:</span>
                    <span>{{ number_format(($payroll->salary_type === 'monthly' ? $payroll->base_salary : $payroll->piece_rate_earnings) + $payroll->bonuses_amount, 2) }} ج.م</span>
                </div>
            </div>

            <!-- 2. الاستقطاعات والسلف (Deductions) -->
            <div class="border border-red-200/80 rounded-2xl p-5 bg-red-50/20 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-black text-red-800 pb-3 border-b border-red-200 flex items-center justify-between">
                        <span>الاستقطاعات والسلف المسحوبة (عليه)</span>
                        <i class="fa-solid fa-minus-circle text-red-600"></i>
                    </h3>

                    <div class="divide-y divide-red-100 text-sm mt-3">
                        @forelse ($payroll->advances->whereIn('type', ['advance', 'deduction']) as $ded)
                            <div class="py-2.5 flex justify-between items-center">
                                <div>
                                    <span class="font-medium {{ $ded->type === 'advance' ? 'text-gray-800' : 'text-red-700' }}">
                                        {{ $ded->type === 'advance' ? 'سلفة نقدية' : 'خصم / جزاء' }}
                                    </span>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $ded->date->format('Y-m-d') }} {{ $ded->note ? '- ' . $ded->note : '' }}
                                    </div>
                                </div>
                                <span class="font-bold text-red-600">- {{ number_format($ded->amount, 2) }} ج.م</span>
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-400 text-xs">
                                لا توجد سلف أو استقطاعات مسجلة لهذا الشهر.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t border-red-200 flex justify-between items-center font-black text-red-900 text-base mt-4">
                    <span>إجمالي الاستقطاعات:</span>
                    <span>- {{ number_format($payroll->deductions_amount + $payroll->advances_deducted, 2) }} ج.م</span>
                </div>
            </div>

        </div>

        <!-- Net Payable Grand Box -->
        <div class="bg-[#8A704C]/10 border-2 border-[#8A704C]/30 rounded-2xl p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <p class="text-xs font-bold text-[#8A704C] uppercase tracking-wider mb-1">صافي الراتب المستحق للصرف</p>
                <h3 class="text-3xl font-black text-[#8A704C]">{{ number_format($payroll->net_salary, 2) }} <span class="text-base font-normal">ج.م</span></h3>
            </div>

            <div class="text-left rtl:text-right sm:rtl:text-left text-xs text-gray-500">
                <p><span class="font-bold text-gray-700">توقيع الموظف بالاستلام:</span> ___________________</p>
                <p class="mt-2"><span class="font-bold text-gray-700">توقيع الإدارة المالية:</span> ___________________</p>
            </div>
        </div>

        <!-- Production Details Table (If Piece Rate) -->
        @if ($payroll->salary_type === 'piece_rate' && $productionLogs->count() > 0)
            <div class="pt-6 border-t border-gray-100">
                <h4 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-scissors text-[#8A704C]"></i>
                    <span>تفاصيل الأوردرات المنجزة بالورشة لشهر {{ $payroll->period_month }}/{{ $payroll->period_year }}</span>
                </h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="p-2.5">رقم الأوردر</th>
                                <th class="p-2.5">العميل</th>
                                <th class="p-2.5">المرحلة</th>
                                <th class="p-2.5">تاريخ الإنجاز</th>
                                <th class="p-2.5">سعر القطعة المعتمد</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($productionLogs as $log)
                                <tr>
                                    <td class="p-2.5 font-bold text-[#8A704C]">
                                        {{ $log->order?->order_number ?? '#' . $log->order_id }}
                                    </td>
                                    <td class="p-2.5 text-gray-800">{{ $log->order?->customer_name ?? '-' }}</td>
                                    <td class="p-2.5">
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ $log->to_status }}</span>
                                    </td>
                                    <td class="p-2.5 text-gray-600">{{ $log->executed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td class="p-2.5 font-bold text-gray-900">
                                        @php
                                            $price = \App\Models\TailorPiecePrice::where('employee_id', $payroll->employee_id)
                                                ->where('order_id', $log->order_id)
                                                ->value('piece_price');
                                        @endphp
                                        {{ $price ? number_format($price, 2) . ' ج.م' : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <!-- Modal: Pay this payroll -->
    <div x-show="isPayModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 print:hidden">
        <div @click.away="isPayModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-base font-bold text-gray-800">صرف وتسليم الراتب للموظف</h3>
                <button @click="isPayModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('payroll.pay', $payroll) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="bg-emerald-50 p-4 rounded-xl text-center">
                    <p class="text-xs text-emerald-700 font-bold">المبلغ المسلم للموظف:</p>
                    <p class="text-2xl font-black text-emerald-800 mt-1">{{ number_format($payroll->net_salary, 2) }} ج.م</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة الصرف *</label>
                    <select name="payment_method" required class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <option value="cash">نقداً (كاش - خزينة المصنع)</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="vodafone_cash">فودافون كاش / إنستاباي</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">ملاحظات الصرف (اختياري)</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#8A704C]"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="isPayModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> تأكيد التسليم والصرف
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
