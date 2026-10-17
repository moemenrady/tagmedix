@extends('layouts.app')

@section('title', 'مسير الرواتب | Payroll')
@section('page_title', 'مسير الرواتب ومستحقات الموظفين')

@section('content')
<div class="space-y-6 pb-16 font-sans w-full" x-data="{ isBulkPayModalOpen: false }">

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="bg-green-50 border-r-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm">
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Top Action & Month Selection Bar -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div>
            <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-[#8A704C]"></i>
                <span>مسير رواتب ومستحقات الموظفين والخياطين</span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">احتساب دقيق لرواتب الشهر بناءً على الإنتاج الفعلي (القطع) والرواتب الثابتة مع خصم السلف آلياً</p>
        </div>

        <!-- Month & Year Controls + Generate Action -->
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <form method="GET" action="{{ route('payroll.index') }}" class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200">
                <select name="month" onchange="this.form.submit()" class="bg-transparent text-xs font-bold text-gray-800 outline-none px-2 py-1 cursor-pointer">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }} (شهر {{ $m }})
                        </option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" class="bg-transparent text-xs font-bold text-gray-800 outline-none px-2 py-1 cursor-pointer">
                    @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <!-- Auto Generate / Recalculate Button -->
            <form action="{{ route('payroll.generate') }}" method="POST" onsubmit="return confirm('هل تريد احتساب وتحديث رواتب جميع الموظفين لشهر {{ $selectedMonth }}/{{ $selectedYear }} بناءً على الإنتاج والسلف الحالية؟')">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <button type="submit" class="bg-[#8A704C] hover:bg-[#735d3f] text-white px-5 py-2.5 rounded-xl font-bold transition shadow-md flex items-center gap-2 text-xs">
                    <i class="fa-solid fa-calculator"></i>
                    <span>توليد مسير الشهر آلياً</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Quick Stats Cards for Selected Period -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">الموظفون في المسير</p>
            <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_employees_count']) }}</p>
            <p class="text-[11px] text-gray-400 mt-2 font-medium">شهر {{ $selectedMonth }} / {{ $selectedYear }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">الأساسي + إنتاج القطع</p>
            <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_base_and_pieces'], 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            <p class="text-[11px] text-gray-400 mt-2 font-medium">مستحقات العمل الفعلية</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">المكافآت والحوافز</p>
            <p class="text-2xl font-black text-emerald-600">+ {{ number_format($stats['total_bonuses'], 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            <p class="text-[11px] text-gray-400 mt-2 font-medium">إضافي ومكافآت التميز</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">الخصومات والسلف</p>
            <p class="text-2xl font-black text-red-600">- {{ number_format($stats['total_deductions_and_advances'], 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span></p>
            <p class="text-[11px] text-gray-400 mt-2 font-medium">سلف مسحوبة + جزاءات</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-[#8A704C]/30 bg-gradient-to-br from-white to-[#8A704C]/5 shadow-sm">
            <p class="text-xs font-bold text-[#8A704C] uppercase tracking-wider mb-1">صافي الرواتب المستحقة</p>
            <p class="text-2xl font-black text-[#8A704C]">{{ number_format($stats['total_net_salary'], 2) }} <span class="text-xs font-normal text-[#8A704C]">ج.م</span></p>
            <p class="text-[11px] text-gray-500 mt-2 font-medium">المبلغ الإجمالي للصرف</p>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    @if ($payrolls->count() > 0)
        <div class="flex flex-wrap items-center justify-between gap-3 bg-gray-50 p-4 rounded-2xl border border-gray-200">
            <div class="text-xs text-gray-600 font-bold flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#8A704C]"></i>
                <span>إجراءات سريعة على مسير شهر {{ $selectedMonth }}/{{ $selectedYear }}:</span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Bulk Approve -->
                <form action="{{ route('payroll.bulk-approve') }}" method="POST" onsubmit="return confirm('اعتماد جميع مسودات الرواتب لهذا الشهر؟')">
                    @csrf
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-check-double"></i>
                        <span>اعتماد الكل</span>
                    </button>
                </form>

                <!-- Bulk Pay Modal Trigger -->
                <button @click="isBulkPayModalOpen = true" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>صرف وتسليم الكل</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Payroll Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50/70 text-gray-500 text-xs border-b border-gray-100">
                    <tr>
                        <th class="p-4">رقم المسير</th>
                        <th class="p-4">الموظف</th>
                        <th class="p-4">نظام الراتب</th>
                        <th class="p-4 text-center">القطع المنجزة</th>
                        <th class="p-4">الأساسي / الإنتاج</th>
                        <th class="p-4">المكافآت</th>
                        <th class="p-4">الخصم والسلف</th>
                        <th class="p-4">الصافي المستحق</th>
                        <th class="p-4 text-center">الحالة</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($payrolls as $pay)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-4 font-bold text-gray-900">
                                <a href="{{ route('payroll.show', $pay) }}" class="text-[#8A704C] hover:underline">
                                    {{ $pay->payroll_number }}
                                </a>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-gray-900">{{ $pay->employee->name }}</div>
                                <div class="text-[11px] text-gray-400">{{ $pay->employee->phone ?? '-' }}</div>
                            </td>
                            <td class="p-4 text-xs font-semibold">
                                @if ($pay->salary_type === 'piece_rate')
                                    <span class="px-2.5 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200">بالإنتاج (القطعة)</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200">راتب شهري ثابت</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold text-gray-800">
                                @if ($pay->salary_type === 'piece_rate')
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800 font-mono text-xs">
                                        {{ $pay->completed_pieces_count }} قطعة
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-4 font-bold text-gray-900">
                                {{ number_format($pay->salary_type === 'monthly' ? $pay->base_salary : $pay->piece_rate_earnings, 2) }} ج.م
                            </td>
                            <td class="p-4 text-emerald-600 font-bold">
                                {{ $pay->bonuses_amount > 0 ? '+' . number_format($pay->bonuses_amount, 2) : '0.00' }}
                            </td>
                            <td class="p-4 text-red-600 font-bold text-xs">
                                <div>سلف: -{{ number_format($pay->advances_deducted, 2) }}</div>
                                @if ($pay->deductions_amount > 0)
                                    <div>جزاء: -{{ number_format($pay->deductions_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="p-4 font-black text-[#8A704C] text-base">
                                {{ number_format($pay->net_salary, 2) }} <span class="text-xs font-normal text-gray-400">ج.م</span>
                            </td>
                            <td class="p-4 text-center">
                                @if ($pay->status === 'paid')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check text-[10px]"></i> مدفوع
                                    </span>
                                @elseif ($pay->status === 'approved')
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-clipboard-check text-[10px]"></i> معتمد
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">
                                        <i class="fa-solid fa-pencil text-[10px]"></i> مسودة
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('payroll.show', $pay) }}" class="p-1.5 rounded-lg text-gray-500 hover:text-[#8A704C] hover:bg-gray-100 transition" title="عرض قسيمة الراتب">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>

                                    @if ($pay->status === 'draft')
                                        <form action="{{ route('payroll.approve', $pay) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition" title="اعتماد">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($pay->status !== 'paid')
                                        <form action="{{ route('payroll.destroy', $pay) }}" method="POST" onsubmit="return confirm('حذف هذا الراتب وإعادة فتح السلف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition" title="حذف المسودة">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-12 text-center text-gray-400">
                                <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 block text-gray-300"></i>
                                لم يتم توليد مسير رواتب لشهر {{ $selectedMonth }}/{{ $selectedYear }} بعد.
                                <div class="mt-3">
                                    <form action="{{ route('payroll.generate') }}" method="POST" class="inline-block">
                                        @csrf
                                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                                        <button type="submit" class="bg-[#8A704C] hover:bg-[#735d3f] text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm">
                                            توليد رواتب شهر {{ $selectedMonth }}/{{ $selectedYear }} الآن
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payrolls->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $payrolls->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Bulk Pay -->
    <div x-show="isBulkPayModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div @click.away="isBulkPayModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-base font-bold text-gray-800">صرف وتسليم رواتب شهر {{ $selectedMonth }}/{{ $selectedYear }}</h3>
                <button @click="isBulkPayModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('payroll.bulk-pay') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة الصرف للموظفين</label>
                    <select name="payment_method" class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <option value="cash">نقداً (كاش - خزينة المصنع)</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="vodafone_cash">فودافون كاش / إنستاباي</option>
                    </select>
                </div>
                <p class="text-xs text-gray-500">سيتم تحويل حالة جميع الرواتب المعتمدة لهذا الشهر إلى "مدفوعة ومسلمة".</p>
                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="isBulkPayModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> تأكيد الصرف
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
