@extends('layouts.app')

@section('title', 'Employee Details')
@section('page_title', 'Employee Details - ' . $employee->name)

@section('content')

    <!-- Header: Employee Details -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-2xl shrink-0">
                    {{ strtoupper(mb_substr($employee->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $employee->name }}</h2>
                    <p class="text-gray-500 flex items-center gap-2 mt-1">
                        <i class="fa-solid fa-phone text-sm"></i> {{ $employee->phone ?? 'N/A' }}
                    </p>
                </div>
            </div>
            
            <div class="flex flex-col gap-2 text-right">
                @if ($employee->salary_type === 'monthly')
                    <span class="px-4 py-2 rounded-full bg-blue-100 text-blue-700 font-semibold flex items-center justify-end gap-2">
                        <i class="fa-solid fa-calendar-days"></i> راتب شهري: {{ number_format($employee->monthly_salary, 2) }} ج.م
                    </span>
                @else
                    <span class="px-4 py-2 rounded-full bg-purple-100 text-purple-700 font-semibold flex items-center justify-end gap-2">
                        <i class="fa-solid fa-scissors"></i> محاسبة بالإنتاج (القطعة)
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Production Logs grouped by Month (Accordion) -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-[#8A704C]"></i> سجل الإنتاج والمستحقات
        </h3>
        <a href="{{ route('employees.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> العودة للقائمة
        </a>
    </div>

    <div class="space-y-4">
        @forelse($monthlyData as $month => $data)
            <div x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                
                <!-- Tab Header -->
                <div class="w-full px-6 py-4 bg-gray-50 flex items-center justify-between transition border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-calendar text-gray-500 text-lg"></i>
                        <span class="font-bold text-gray-800 text-lg">{{ $month }}</span>
                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $data['logs']->count() }} سجلات</span>
                    </div>

                    <div class="flex items-center gap-6">
                        <!-- Earnings -->
                        <div class="text-right" dir="ltr">
                            <span class="text-xs text-gray-500 block text-right">مستحقات هذا الشهر</span>
                            @if ($employee->salary_type === 'monthly')
                                <span class="font-bold text-green-600 text-lg">{{ number_format($employee->monthly_salary, 2) }} ج.م</span>
                            @else
                                <span class="font-bold text-green-600 text-lg">{{ number_format($data['piece_rate_earnings'], 2) }} ج.م</span>
                            @endif
                        </div>
                        
                        <!-- Toggle Button -->
                        <button @click="expanded = !expanded" class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition shadow-sm focus:outline-none" title="إظهار/إخفاء التفاصيل">
                            <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="expanded ? 'rotate-180' : ''"></i>
                        </button>
                    </div>
                </div>

                <!-- Tab Body (Table) -->
                <div x-show="expanded" x-collapse x-cloak>
                    <div class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap text-left text-sm" dir="rtl">
                            <thead class="bg-white border-b border-gray-100 text-gray-500">
                                <tr>
                                    <th class="px-6 py-3 text-right">رقم الطلب</th>
                                    <th class="px-6 py-3 text-right">التاريخ والوقت</th>
                                    <th class="px-6 py-3 text-right">من حالة</th>
                                    <th class="px-6 py-3 text-right">إلى حالة</th>
                                    <th class="px-6 py-3 text-right">ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($data['logs'] as $log)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3 font-semibold text-gray-900">
                                            {{ $log->order ? $log->order->order_number : '#' . $log->order_id }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-600">
                                            {{ $log->executed_at ? $log->executed_at->format('Y-m-d h:i A') : ($log->created_at ? $log->created_at->format('Y-m-d h:i A') : 'N/A') }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded bg-gray-100 text-gray-600 text-xs">{{ $log->from_status ?? '---' }}</span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded bg-[#8A704C]/10 text-[#8A704C] text-xs font-semibold">{{ $log->to_status }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-500 max-w-xs truncate" title="{{ $log->note }}">
                                            {{ $log->note ?? '---' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">لا يوجد سجلات في هذا الشهر.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center text-gray-500">
                <i class="fa-solid fa-clipboard text-4xl mb-3 text-gray-300"></i>
                <p class="text-lg font-medium text-gray-600">لا يوجد سجلات إنتاج</p>
                <p class="text-sm">هذا الموظف لم يقم بأي عمليات إنتاج حتى الآن.</p>
            </div>
        @endforelse
    </div>
@endsection
