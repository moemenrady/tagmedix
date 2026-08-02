@extends('layouts.app')

@section('title', 'حركات المخزون - Inventory Transactions')

@section('content')

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.4s ease-out forwards;
        }

        .animate-fade-down {
            animation: fadeInDown 0.4s ease-out forwards;
        }

        /* Custom Scrollbar for table */
        .table-container::-webkit-scrollbar {
            height: 6px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }
    </style>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 animate-fade-down">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-right-left text-activeGold"></i>
                <span data-ar="سجل حركات المخزون" data-en="Inventory Transactions Log">سجل حركات المخزون</span>
            </h2>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                <a href="{{ route('inventory.index') }}" class="hover:text-activeGold transition">
                    <i class="fa-solid fa-boxes-stacked text-[10px]"></i>
                    <span data-ar="المخزون" data-en="Inventory">المخزون</span>
                </a>
                <i class="fa-solid fa-chevron-left text-[10px] rtl:inline ltr:hidden"></i>
                <i class="fa-solid fa-chevron-right text-[10px] hidden ltr:inline"></i>
                <span data-ar="الحركات" data-en="Transactions">الحركات</span>
            </p>
        </div>

        <button onclick="toggleFilters()"
            class="bg-white border border-gray-200 text-gray-700 hover:text-activeGold hover:border-activeGold px-4 py-2 rounded-lg shadow-sm transition flex items-center gap-2 font-medium text-sm w-full md:w-auto justify-center group">
            <i class="fa-solid fa-filter group-hover:scale-110 transition-transform"></i>
            <span data-ar="تصفية وبحث" data-en="Filter & Search">تصفية وبحث</span>
        </button>
    </div>

    <div id="filtersSection"
        class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6 {{ request()->hasAny(['type', 'date_from', 'date_to']) ? '' : 'hidden' }} animate-fade-down">
        <form action="{{ route('inventory-transactions.index') }}" method="GET"
            class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-600">
                    <span data-ar="نوع الحركة" data-en="Transaction Type">نوع الحركة</span>
                </label>
                <select name="type"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50 cursor-pointer">
                    <option value="">-- <span data-ar="الكل" data-en="All">الكل</span> --</option>
                    <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>شراء / Purchase (In)
                    </option>
                    <option value="issue" {{ request('type') == 'issue' ? 'selected' : '' }}>صرف / Issue (Out)</option>
                    <option value="adjustment_in" {{ request('type') == 'adjustment_in' ? 'selected' : '' }}>تسوية بالزيادة
                        / Adjustment (In)</option>
                    <option value="adjustment_out" {{ request('type') == 'adjustment_out' ? 'selected' : '' }}>تسوية بالنقص
                        / Adjustment (Out)</option>
                    <option value="return_in" {{ request('type') == 'return_in' ? 'selected' : '' }}>مرتجع للمخزن / Return
                        (In)</option>
                    <option value="return_out" {{ request('type') == 'return_out' ? 'selected' : '' }}>مرتجع للمورد / Return
                        (Out)</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-600">
                    <span data-ar="من تاريخ" data-en="Date From">من تاريخ</span>
                </label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50">
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-600">
                    <span data-ar="إلى تاريخ" data-en="Date To">إلى تاريخ</span>
                </label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50">
            </div>

            <div class="flex items-end gap-2 h-full pt-4 md:pt-0">
                <button type="submit"
                    class="flex-1 bg-activeGold hover:bg-[#725c3d] text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md transition flex justify-center items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span data-ar="بحث" data-en="Search">بحث</span>
                </button>
                <a href="{{ route('inventory-transactions.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm font-bold transition flex justify-center items-center"
                    title="إعادة ضبط">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fade-up">
        <div class="overflow-x-auto table-container">
            <table class="w-full text-left rtl:text-right text-sm text-gray-600 whitespace-nowrap">
                <thead class="bg-gray-50/80 text-gray-700 font-bold border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-5 py-4"><span data-ar="التاريخ والوقت" data-en="Date & Time">التاريخ
                                والوقت</span></th>
                        <th scope="col" class="px-5 py-4"><span data-ar="الصنف" data-en="Item">الصنف</span></th>
                        <th scope="col" class="px-5 py-4"><span data-ar="نوع الحركة" data-en="Type">نوع الحركة</span>
                        </th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="الكمية" data-en="Qty">الكمية</span>
                        </th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="الرصيد بعد"
                                data-en="Balance After">الرصيد بعد</span></th>
                        <th scope="col" class="px-5 py-4"><span data-ar="المستخدم" data-en="User">المستخدم</span></th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="التفاصيل"
                                data-en="Details">التفاصيل</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $transaction)
                        @php
                            $isIncoming = in_array($transaction->type, ['purchase', 'adjustment_in', 'return_in']);

                            // Badge Configuration based on type
                            $badge = match ($transaction->type) {
                                'purchase' => [
                                    'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'icon' => 'fa-arrow-down',
                                    'ar' => 'شراء',
                                    'en' => 'Purchase',
                                ],
                                'issue' => [
                                    'color' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'icon' => 'fa-arrow-up',
                                    'ar' => 'صرف',
                                    'en' => 'Issue',
                                ],
                                'adjustment_in' => [
                                    'color' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'icon' => 'fa-plus',
                                    'ar' => 'تسوية (زيادة)',
                                    'en' => 'Adj (In)',
                                ],
                                'adjustment_out' => [
                                    'color' => 'bg-orange-100 text-orange-800 border-orange-200',
                                    'icon' => 'fa-minus',
                                    'ar' => 'تسوية (نقص)',
                                    'en' => 'Adj (Out)',
                                ],
                                'return_in' => [
                                    'color' => 'bg-teal-100 text-teal-800 border-teal-200',
                                    'icon' => 'fa-rotate-left',
                                    'ar' => 'مرتجع للمخزن',
                                    'en' => 'Return (In)',
                                ],
                                'return_out' => [
                                    'color' => 'bg-red-100 text-red-800 border-red-200',
                                    'icon' => 'fa-rotate-right',
                                    'ar' => 'مرتجع للمورد',
                                    'en' => 'Return (Out)',
                                ],
                                default => [
                                    'color' => 'bg-gray-100 text-gray-800 border-gray-200',
                                    'icon' => 'fa-circle',
                                    'ar' => 'غير معروف',
                                    'en' => 'Unknown',
                                ],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition group">
                            <td class="px-5 py-4">
                                <div class="flex flex-col">
                                    <span
                                        class="font-bold text-gray-800">{{ $transaction->created_at->format('Y-m-d') }}</span>
                                    <span
                                        class="text-xs text-gray-400 font-mono">{{ $transaction->created_at->format('h:i A') }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-activeGold">{{ $transaction->item->name ?? '-' }}</span>
                                    <span
                                        class="text-xs text-gray-500 font-mono">{{ $transaction->item->code ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border {{ $badge['color'] }}">
                                    <i class="fa-solid {{ $badge['icon'] }} text-[10px]"></i>
                                    <span data-ar="{{ $badge['ar'] }}"
                                        data-en="{{ $badge['en'] }}">{{ $badge['ar'] }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div
                                    class="flex items-center justify-center gap-1 font-bold text-base {{ $isIncoming ? 'text-emerald-600' : 'text-rose-600' }}">
                                    <span>{{ $isIncoming ? '+' : '-' }}</span>
                                    <span>{{ floatval($transaction->quantity) }}</span>
                                    <span
                                        class="text-xs font-normal text-gray-400">{{ $transaction->item->unit ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="font-bold text-gray-800 bg-gray-100 px-2 py-1 rounded-lg">
                                    {{ floatval($transaction->balance_after) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs shrink-0">
                                        <i class="fa-solid fa-user text-[10px]"></i>
                                    </div>
                                    <span
                                        class="text-xs font-medium">{{ $transaction->creator->name ?? 'نظام / System' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button onclick="viewDetails({{ $transaction->id }})"
                                    class="text-gray-400 hover:text-activeGold bg-gray-50 hover:bg-[#8A704C]/10 p-2 rounded-lg transition"
                                    title="عرض التفاصيل">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center">
                                        <i class="fa-solid fa-clock-rotate-left text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">
                                        <span data-ar="لا توجد حركات مخزون مسجلة"
                                            data-en="No inventory transactions recorded">لا توجد حركات مخزون مسجلة</span>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-gray-100 flex justify-center dir-ltr">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <div id="detailsModal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center items-center p-4 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300 relative"
            id="detailsModalContent">

            <div
                class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-activeGold"></div>
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-activeGold"></i>
                    <span data-ar="تفاصيل الحركة" data-en="Transaction Details">تفاصيل الحركة</span>
                </h3>
                <button onclick="closeDetailsModal()"
                    class="text-gray-400 hover:text-rose-500 transition bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div id="modalLoader" class="p-10 flex flex-col items-center justify-center gap-3">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-activeGold"></i>
                <span class="text-sm text-gray-500 font-medium" data-ar="جاري جلب البيانات..."
                    data-en="Loading data...">جاري جلب البيانات...</span>
            </div>

            <div id="modalData" class="p-6 hidden space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block mb-1" data-ar="الصنف"
                            data-en="Item">الصنف</span>
                        <div class="font-bold text-gray-800 text-sm truncate" id="m_item_name"></div>
                        <div class="text-xs text-activeGold font-mono mt-0.5" id="m_item_code"></div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block mb-1" data-ar="الكمية المنقولة"
                            data-en="Moved Qty">الكمية المنقولة</span>
                        <div class="font-bold text-lg flex items-center gap-1" id="m_qty_container">
                            <span id="m_qty"></span>
                            <span class="text-xs text-gray-500 font-normal" id="m_unit"></span>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block mb-1" data-ar="تكلفة الوحدة"
                            data-en="Unit Cost">تكلفة الوحدة</span>
                        <div class="font-bold text-gray-800 text-sm">
                            <span id="m_unit_cost"></span> <span class="text-xs font-normal">EGP</span>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block mb-1" data-ar="التكلفة الإجمالية"
                            data-en="Total Cost">التكلفة الإجمالية</span>
                        <div class="font-bold text-gray-800 text-sm">
                            <span id="m_total_cost"></span> <span class="text-xs font-normal">EGP</span>
                        </div>
                    </div>
                </div>

                <div class="bg-[#faf9f7] p-4 rounded-xl border border-[#e5dfd5]">
                    <span class="text-xs font-bold text-activeGold flex items-center gap-1.5 mb-2">
                        <i class="fa-regular fa-comment-dots"></i>
                        <span data-ar="ملاحظات / مرجع" data-en="Notes / Reference">ملاحظات / مرجع</span>
                    </span>
                    <p class="text-sm text-gray-600 leading-relaxed" id="m_notes">لا توجد ملاحظات.</p>
                    <div
                        class="mt-3 pt-3 border-t border-gray-200/50 flex justify-between items-center text-xs text-gray-500">
                        <div class="flex items-center gap-1">
                            <i class="fa-solid fa-link"></i>
                            <span id="m_reference">N/A</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fa-regular fa-clock"></i>
                            <span id="m_date" class="font-mono"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // --- Toggle Filters ---
        function toggleFilters() {
            const filters = document.getElementById('filtersSection');
            if (filters.classList.contains('hidden')) {
                filters.classList.remove('hidden');
            } else {
                filters.classList.add('hidden');
            }
        }

        // --- Modal Logic & AJAX Fetch ---
        const detailsModal = document.getElementById('detailsModal');
        const detailsModalContent = document.getElementById('detailsModalContent');
        const modalLoader = document.getElementById('modalLoader');
        const modalData = document.getElementById('modalData');

        function closeDetailsModal() {
            detailsModal.classList.add('opacity-0');
            detailsModalContent.classList.add('scale-95');
            setTimeout(() => detailsModal.classList.add('hidden'), 300);
        }

        async function viewDetails(id) {
            // Show Modal with Loader
            detailsModal.classList.remove('hidden');
            modalLoader.classList.remove('hidden');
            modalData.classList.add('hidden');

            setTimeout(() => {
                detailsModal.classList.remove('opacity-0');
                detailsModalContent.classList.remove('scale-95');
            }, 10);

            try {
                const response = await fetch(`/inventory-transactions/${id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    const isAr = localStorage.getItem('lang') !== 'en';

                    // Populate Data
                    document.getElementById('m_item_name').textContent = data.item.name;
                    document.getElementById('m_item_code').textContent = data.item.code;

                    // Qty Logic
                    const isIncoming = ['purchase', 'adjustment_in', 'return_in'].includes(data.type);
                    const qtyEl = document.getElementById('m_qty');
                    const qtyContainer = document.getElementById('m_qty_container');

                    qtyEl.textContent = `${isIncoming ? '+' : '-'}${parseFloat(data.quantity)}`;
                    qtyContainer.className =
                        `font-bold text-lg flex items-center gap-1 ${isIncoming ? 'text-emerald-600' : 'text-rose-600'}`;
                    document.getElementById('m_unit').textContent = data.item.unit || '';

                    // Costs
                    document.getElementById('m_unit_cost').textContent = parseFloat(data.unit_cost).toLocaleString();
                    const totalCost = parseFloat(data.quantity) * parseFloat(data.unit_cost);
                    document.getElementById('m_total_cost').textContent = totalCost.toLocaleString();

                    // Notes & References
                    document.getElementById('m_notes').textContent = data.notes || (isAr ?
                        'لا توجد ملاحظات إضافية مسجلة.' : 'No additional notes recorded.');

                    const refType = data.reference_type ? data.reference_type : 'Manual';
                    const refId = data.reference_id ? `#${data.reference_id}` : '';
                    document.getElementById('m_reference').textContent = `${refType} ${refId}`;

                    // Date formatting (simple)
                    const dateObj = new Date(data.created_at);
                    document.getElementById('m_date').textContent = dateObj.toISOString().split('T')[0] + ' ' + dateObj
                        .toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                    // Hide Loader, Show Data
                    modalLoader.classList.add('hidden');
                    modalData.classList.remove('hidden');

                    // Apply current language translations dynamically
                    if (typeof applyLanguage === 'function') {
                        applyLanguage(localStorage.getItem('lang') || 'ar');
                    }
                }
            } catch (error) {
                console.error('Error fetching transaction details:', error);
                modalLoader.innerHTML =
                    `<div class="text-rose-500 font-bold"><i class="fa-solid fa-triangle-exclamation mr-2"></i> حدث خطأ أثناء جلب البيانات</div>`;
            }
        }

        // Close modal on outside click
        detailsModal.addEventListener('click', function(e) {
            if (e.target === detailsModal) closeDetailsModal();
        });
    </script>
@endpush
