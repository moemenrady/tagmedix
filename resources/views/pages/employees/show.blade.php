@extends('layouts.app')

@section('title', 'تفاصيل الموظف | ' . $employee->name)
@section('page_title', 'تفاصيل الموظف - ' . $employee->name)

@section('content')
<div class="space-y-6 pb-16 font-sans w-full" x-data="{ isAdvanceModalOpen: false, activeTab: 'production' }">

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

    <!-- Header: Employee Details & Quick Actions -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-[#8A704C]/10 border border-[#8A704C]/20 flex items-center justify-center text-[#8A704C] font-bold text-2xl shrink-0 shadow-sm">
                    {{ strtoupper(mb_substr($employee->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-black text-gray-900">{{ $employee->name }}</h2>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $employee->active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $employee->active ? 'على رأس العمل' : 'معطل' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 mt-1.5">
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-[#8A704C]"></i> {{ $employee->phone ?? 'لا يوجد هاتف' }}</span>
                        @if ($employee->address)
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-[#8A704C]"></i> {{ $employee->address }}</span>
                        @endif
                        @if ($employee->hire_date)
                            <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-check text-[#8A704C]"></i> التعيين: {{ $employee->hire_date->format('Y-m-d') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Financial Type & Advances summary -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                @if ($employee->salary_type === 'monthly')
                    <span class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 font-bold text-xs flex items-center gap-2 border border-blue-200">
                        <i class="fa-solid fa-calendar-days"></i> راتب شهري: {{ number_format($employee->monthly_salary, 2) }} ج.م
                    </span>
                @else
                    <span class="px-4 py-2 rounded-xl bg-purple-50 text-purple-700 font-bold text-xs flex items-center gap-2 border border-purple-200">
                        <i class="fa-solid fa-scissors"></i> محاسبة بالإنتاج (القطعة)
                    </span>
                @endif

                @php $pendingAdvances = $employee->getPendingAdvancesAmount(); @endphp
                @if ($pendingAdvances > 0)
                    <span class="px-4 py-2 rounded-xl bg-red-50 text-red-700 font-bold text-xs flex items-center gap-1.5 border border-red-200">
                        <i class="fa-solid fa-hand-holding-dollar"></i> سلف معلقة: {{ number_format($pendingAdvances, 2) }} ج.م
                    </span>
                @endif

                <button @click="isAdvanceModalOpen = true" class="bg-[#8A704C] hover:bg-[#735d3f] text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>سلفة / مكافأة / خصم</span>
                </button>

                <a href="{{ route('employees.index') }}" class="p-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition" title="العودة">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 gap-2">
        <button @click="activeTab = 'production'"
            :class="activeTab === 'production' ? 'border-[#8A704C] text-[#8A704C] font-black' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="py-3 px-5 border-b-2 text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-scissors"></i>
            <span>سجل الإنتاج والمستحقات</span>
        </button>

        <button @click="activeTab = 'advances'"
            :class="activeTab === 'advances' ? 'border-[#8A704C] text-[#8A704C] font-black' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="py-3 px-5 border-b-2 text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            <span>السلف والخصومات والمكافآت ({{ $advances->count() }})</span>
        </button>

        <button @click="activeTab = 'payrolls'"
            :class="activeTab === 'payrolls' ? 'border-[#8A704C] text-[#8A704C] font-black' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="py-3 px-5 border-b-2 text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-receipt"></i>
            <span>سجل الرواتب المصروفة ({{ $payrolls->count() }})</span>
        </button>
    </div>

    <!-- Tab 1: Production Logs (Accordion) -->
    <div x-show="activeTab === 'production'" class="space-y-4">
        @forelse($monthlyData as $month => $data)
            <div x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }" class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="w-full px-6 py-4 bg-gray-50 flex items-center justify-between transition border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-calendar text-gray-500 text-lg"></i>
                        <span class="font-bold text-gray-800 text-base">{{ $month }}</span>
                        <span class="bg-gray-200 text-gray-600 text-xs px-2.5 py-0.5 rounded-full font-bold">{{ $data['logs']->count() }} عمليات</span>
                    </div>

                    <div class="flex items-center gap-4">
                        @if ($employee->salary_type === 'piece_rate')
                            <span class="font-black text-[#8A704C] text-sm bg-[#8A704C]/10 px-3 py-1 rounded-full">
                                مستحق القطع: {{ number_format($data['piece_rate_earnings'], 2) }} ج.م
                            </span>
                        @endif

                        <button @click="expanded = !expanded" class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:bg-gray-100 transition shadow-sm">
                            <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="expanded ? 'rotate-180' : ''"></i>
                        </button>
                    </div>
                </div>

                <div x-show="expanded" class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead class="bg-white border-b border-gray-100 text-gray-500">
                            <tr>
                                <th class="px-6 py-3">رقم الطلب</th>
                                <th class="px-6 py-3">التاريخ والوقت</th>
                                <th class="px-6 py-3">من حالة</th>
                                <th class="px-6 py-3">إلى حالة</th>
                                <th class="px-6 py-3">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($data['logs'] as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3 font-bold text-[#8A704C]">
                                        {{ $log->order ? $log->order->order_number : '#' . $log->order_id }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">
                                        {{ $log->executed_at ? $log->executed_at->format('Y-m-d H:i') : ($log->created_at ? $log->created_at->format('Y-m-d H:i') : 'N/A') }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ $log->from_status ?? '---' }}</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded bg-[#8A704C]/10 text-[#8A704C] font-semibold">{{ $log->to_status }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500 max-w-xs truncate" title="{{ $log->note }}">
                                        {{ $log->note ?? '---' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">لا توجد سجلات في هذا الشهر.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-12 text-center text-gray-400">
                <i class="fa-solid fa-scissors text-4xl mb-3 text-gray-300"></i>
                <p class="text-base font-bold text-gray-700">لا توجد سجلات إنتاج مسجلة لهذا الموظف حتى الآن.</p>
            </div>
        @endforelse
    </div>

    <!-- Tab 2: Advances, Bonuses & Deductions -->
    <div x-show="activeTab === 'advances'" style="display: none;" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-[#8A704C]"></i>
                    <span>سجل السلف المالية، المكافآت، والجزاءات</span>
                </div>
                <button @click="isAdvanceModalOpen = true" class="bg-[#8A704C] hover:bg-[#735d3f] text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-plus"></i> إضافة معاملة مالية
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/60 text-gray-500 text-xs">
                        <tr>
                            <th class="p-3.5">النوع</th>
                            <th class="p-3.5">المبلغ</th>
                            <th class="p-3.5">التاريخ</th>
                            <th class="p-3.5">حالة التسوية</th>
                            <th class="p-3.5">البيان / ملاحظات</th>
                            <th class="p-3.5 text-center">حذف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($advances as $adv)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-3.5">
                                    @if ($adv->type === 'advance')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-50 text-red-700 border border-red-200">سلفة نقدية</span>
                                    @elseif ($adv->type === 'bonus')
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">مكافأة / حافز</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200">خصم / جزاء</span>
                                    @endif
                                </td>
                                <td class="p-3.5 font-black text-gray-900">
                                    {{ number_format($adv->amount, 2) }} ج.م
                                </td>
                                <td class="p-3.5 text-xs text-gray-600">{{ $adv->date->format('Y-m-d') }}</td>
                                <td class="p-3.5 text-xs">
                                    @if ($adv->is_settled)
                                        <span class="text-emerald-700 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i> تمت تسويتها براتب {{ $adv->payroll?->period_month }}/{{ $adv->payroll?->period_year }}
                                        </span>
                                    @else
                                        <span class="text-amber-700 font-bold flex items-center gap-1">
                                            <i class="fa-solid fa-clock"></i> معلقة (تخصم تلقائياً بالراتب القادم)
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-xs text-gray-500">{{ $adv->note ?? '-' }}</td>
                                <td class="p-3.5 text-center">
                                    @if (!$adv->is_settled)
                                        <form action="{{ route('employees.advances.destroy', $adv) }}" method="POST" onsubmit="return confirm('حذف هذه المعاملة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 p-1" title="حذف">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 text-xs">لا توجد سلف أو مكافآت مسجلة للموظف بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab 3: Payroll History -->
    <div x-show="activeTab === 'payrolls'" style="display: none;" class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                <div class="font-bold text-gray-800 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#8A704C]"></i>
                    <span>سجل قسائم الرواتب الشهرية المصروفة للموظف</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/60 text-gray-500 text-xs">
                        <tr>
                            <th class="p-3.5">الشهر</th>
                            <th class="p-3.5">رقم القسيمة</th>
                            <th class="p-3.5">الأساسي / الإنتاج</th>
                            <th class="p-3.5">المكافآت</th>
                            <th class="p-3.5">الاستقطاعات والسلف</th>
                            <th class="p-3.5">الصافي المنصرف</th>
                            <th class="p-3.5">الحالة</th>
                            <th class="p-3.5 text-center">عرض</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payrolls as $pr)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="p-3.5 font-bold text-gray-900">شهر {{ $pr->period_month }} / {{ $pr->period_year }}</td>
                                <td class="p-3.5 font-mono text-xs text-[#8A704C] font-bold">{{ $pr->payroll_number }}</td>
                                <td class="p-3.5 font-bold">{{ number_format($pr->salary_type === 'monthly' ? $pr->base_salary : $pr->piece_rate_earnings, 2) }} ج.م</td>
                                <td class="p-3.5 text-emerald-600 font-bold">+{{ number_format($pr->bonuses_amount, 2) }}</td>
                                <td class="p-3.5 text-red-600 font-bold">-{{ number_format($pr->deductions_amount + $pr->advances_deducted, 2) }}</td>
                                <td class="p-3.5 font-black text-[#8A704C]">{{ number_format($pr->net_salary, 2) }} ج.م</td>
                                <td class="p-3.5 text-xs">
                                    @if ($pr->status === 'paid')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">مدفوع</span>
                                    @elseif ($pr->status === 'approved')
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold">معتمد</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold">مسودة</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    <a href="{{ route('payroll.show', $pr) }}" class="p-1.5 rounded-lg text-gray-500 hover:text-[#8A704C] hover:bg-gray-100 transition inline-block">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-400 text-xs">لا توجد قسائم رواتب سابقة مسجلة لهذا الموظف.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Add Advance / Bonus / Deduction -->
    <div x-show="isAdvanceModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div @click.away="isAdvanceModalOpen = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-dollar text-[#8A704C]"></i>
                    <span>تسجيل معاملة مالية: {{ $employee->name }}</span>
                </h3>
                <button @click="isAdvanceModalOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="{{ route('employees.advances.store', $employee) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">نوع المعاملة *</label>
                    <select name="type" required class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <option value="advance">سلفة نقدية (تخصم من الراتب)</option>
                        <option value="bonus">مكافأة / حافز (تضاف إلى الراتب)</option>
                        <option value="deduction">خصم / جزاء (يخصم من الراتب)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">المبلغ (ج.م) *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00"
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-base font-bold outline-none focus:ring-2 focus:ring-[#8A704C]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">التاريخ *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#8A704C]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">السبب / البيان</label>
                    <textarea name="note" rows="2" placeholder="بيان السلفة أو سبب المكافأة/الخصم..."
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#8A704C]"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="isAdvanceModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold">إلغاء</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#8A704C] hover:bg-[#735d3f] text-white text-xs font-bold flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> حفظ المعاملة
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
