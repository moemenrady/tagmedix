@extends('layouts.app')

@section('title', 'إدارة المخزون - Inventory Management')

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

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-fade-down {
            animation: fadeInDown 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-scale-in {
            animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* تحسين شكل شريط التمرير */
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
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

        /* تأثيرات زجاجية */
        .glass-effect {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 animate-fade-down">
        <div class="w-full md:w-auto">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-activeGold to-[#927852] flex items-center justify-center text-white shadow-lg shrink-0">
                    <i class="fa-solid fa-boxes-stacked text-lg sm:text-xl"></i>
                </div>
                <span>إدارة الأصناف والمخزون</span>
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-2 font-medium pr-14 sm:pr-16">
                متابعة شاملة للكميات، القيم المالية، وحركة الأصناف بشكل لحظي
            </p>
        </div>

        <div class="flex flex-row items-center gap-2 sm:gap-3 w-full md:w-auto mt-2 md:mt-0">
            <button onclick="toggleFilters()"
                class="bg-white border border-gray-200 text-gray-700 hover:text-activeGold hover:border-activeGold px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl shadow-sm transition-all duration-300 flex items-center justify-center gap-2 text-xs sm:text-sm font-bold flex-1 md:flex-none">
                <i class="fa-solid fa-filter"></i>
                <span>تصفية متقدمة</span>
            </button>
            <button onclick="window.location.href='{{ route('inventory.create') }}'"
                class="bg-gradient-to-r from-activeGold to-[#866d48] hover:from-[#866d48] hover:to-[#6a5537] text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 text-xs sm:text-sm font-bold flex-1 md:flex-none whitespace-nowrap">
                <i class="fa-solid fa-plus text-base sm:text-lg"></i>
                <span>إضافة صنف</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-8 animate-fade-down" style="animation-delay: 0.1s;">
        <div
            class="bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-amber-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start gap-2">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase mb-1 sm:mb-2 line-clamp-1">إجمالي
                        المخزون</p>
                    <h3 class="text-lg sm:text-3xl font-extrabold text-gray-900 tracking-tight break-all">
                        {{ number_format($stats['total_inventory_value'], 0) }} <span
                            class="text-[10px] sm:text-sm font-bold text-gray-400">EGP</span>
                    </h3>
                </div>
                <div
                    class="hidden sm:flex w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-50 text-activeGold items-center justify-center text-lg sm:text-xl shadow-inner group-hover:scale-110 transition-transform duration-300 shrink-0">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
        </div>

        <div
            class="bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-blue-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start gap-2">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase mb-1 sm:mb-2 line-clamp-1">نشط / كلي
                    </p>
                    <h3 class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ $stats['active_items'] }} <span class="text-xs sm:text-base text-gray-400 font-bold">/
                            {{ $stats['total_items'] }}</span>
                    </h3>
                </div>
                <div
                    class="hidden sm:flex w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50 text-blue-600 items-center justify-center text-lg sm:text-xl shadow-inner group-hover:scale-110 transition-transform duration-300 shrink-0">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>
        </div>

        <a href="{{ route('inventory.index', ['low_stock' => 1]) }}"
            class="bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group block relative overflow-hidden cursor-pointer">
            <div
                class="absolute -right-6 -top-6 w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-orange-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start gap-2">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase mb-1 sm:mb-2 line-clamp-1">وشك النفاذ
                    </p>
                    <h3
                        class="text-xl sm:text-3xl font-extrabold tracking-tight {{ $stats['low_stock_items'] > 0 ? 'text-orange-500' : 'text-gray-900' }}">
                        {{ $stats['low_stock_items'] }}
                    </h3>
                </div>
                <div
                    class="hidden sm:flex w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-orange-50 text-orange-500 items-center justify-center text-lg sm:text-xl shadow-inner group-hover:scale-110 transition-transform duration-300 shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('inventory.index', ['out_of_stock' => 1]) }}"
            class="bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group block relative overflow-hidden cursor-pointer">
            <div
                class="absolute -right-6 -top-6 w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-rose-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start gap-2">
                <div>
                    <p class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase mb-1 sm:mb-2 line-clamp-1">نفدت
                        تماماً</p>
                    <h3
                        class="text-xl sm:text-3xl font-extrabold tracking-tight {{ $stats['out_of_stock_items'] > 0 ? 'text-rose-600' : 'text-gray-900' }}">
                        {{ $stats['out_of_stock_items'] }}
                    </h3>
                </div>
                <div
                    class="hidden sm:flex w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-rose-50 text-rose-600 items-center justify-center text-lg sm:text-xl shadow-inner group-hover:scale-110 transition-transform duration-300 shrink-0">
                    <i class="fa-solid fa-ban border-rose-500"></i>
                </div>
            </div>
        </a>
    </div>

    <div id="filtersSection"
        class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 {{ request()->hasAny(['search', 'category_id', 'is_active', 'low_stock', 'out_of_stock']) ? '' : 'hidden' }} animate-fade-down relative overflow-hidden">
        <div class="absolute rtl:right-0 ltr:left-0 top-0 h-full w-1 bg-activeGold"></div>
        <form action="{{ route('inventory.index') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-5">
            <div class="space-y-2 sm:col-span-2 lg:col-span-2">
                <label class="text-xs font-bold text-gray-700">بحث سريع</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute top-3.5 rtl:right-4 ltr:left-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="كود أو اسم الصنف..."
                        class="w-full border-2 border-gray-100 rounded-xl rtl:pr-11 ltr:pl-11 py-2.5 text-sm focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all bg-gray-50/50 hover:bg-white">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-700">التصنيف</label>
                <select name="category_id"
                    class="w-full border-2 border-gray-100 rounded-xl px-4 py-2.5 text-sm focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all bg-gray-50/50 hover:bg-white font-medium cursor-pointer appearance-none">
                    <option value="">جميع التصنيفات</option>
                    @foreach ($categoryStats as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-700">حالة الصنف</label>
                <select name="is_active"
                    class="w-full border-2 border-gray-100 rounded-xl px-4 py-2.5 text-sm focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all bg-gray-50/50 hover:bg-white font-medium cursor-pointer appearance-none">
                    <option value="">جميع الحالات</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط ومفعل</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>معطل</option>
                </select>
            </div>

            <div class="flex items-end gap-2 pt-2 sm:pt-0 sm:col-span-2 lg:col-span-1">
                <button type="submit"
                    class="flex-1 bg-gray-900 hover:bg-black text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex justify-center items-center gap-2">
                    <i class="fa-solid fa-filter"></i> عرض
                </button>
                <a href="{{ route('inventory.index') }}"
                    class="bg-gray-100 hover:bg-rose-100 text-gray-600 hover:text-rose-600 px-4 py-2.5 rounded-xl text-sm font-bold transition-all flex justify-center items-center shadow-sm"
                    title="إلغاء الفلتر">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="animate-fade-up" style="animation-delay: 0.2s;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            @forelse($items as $item)
                @php
                    $isLow = $item->current_quantity <= $item->minimum_quantity && $item->current_quantity > 0;
                    $isOut = $item->current_quantity <= 0;
                    $totalVal = $item->current_quantity * $item->last_purchase_price;

                    // تحديد الألوان بناءً على حالة الرصيد
                    $statusColor = $isOut ? 'rose' : ($isLow ? 'orange' : 'emerald');

                    $itemData = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'code' => $item->code,
                        'category_id' => $item->category_id,
                        'category_name' => $item->category?->name,
                        'unit' => $item->unit,
                        'description' => $item->description,
                        'minimum_quantity' => (float) $item->minimum_quantity,
                        'current_quantity' => (float) $item->current_quantity,
                        'last_purchase_price' => (float) $item->last_purchase_price,
                        'total_value' => (float) $totalVal,
                        'is_active' => (bool) $item->is_active,
                        'transactions_count' => (int) $item->inventory_transactions_count,
                        'total_in' => (float) ($item->total_purchased_quantity ?? 0),
                        'total_out' => (float) ($item->total_issued_quantity ?? 0),
                    ];
                    $itemJson = json_encode($itemData, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                @endphp

                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-lg transition-all duration-300 relative flex flex-col h-full group">
                    <div
                        class="absolute rtl:right-0 ltr:left-0 top-0 h-full w-1.5 rounded-r-2xl bg-{{ $statusColor }}-500">
                    </div>

                    <div class="flex justify-between items-start mb-4 relative z-10 pl-2 rtl:pr-3 ltr:pl-3">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 shrink-0 border border-gray-100">
                                <i class="fa-solid fa-box-open text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-gray-900 text-base mb-1 group-hover:text-activeGold transition-colors leading-tight line-clamp-2"
                                    title="{{ $item->name }}">{{ $item->name }}</h3>
                                <div class="flex flex-wrap items-center gap-2 text-[11px] mt-1.5">
                                    <span
                                        class="font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ $item->code }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-activeGold font-bold truncate max-w-[100px]"
                                        title="{{ $item->category->name ?? 'بدون تصنيف' }}">{{ $item->category->name ?? 'بدون تصنيف' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 pt-1">
                            @if ($item->is_active)
                                <span class="flex h-2.5 w-2.5 relative tooltip" title="الصنف نشط">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                </span>
                            @else
                                <span class="flex h-2.5 w-2.5 rounded-full bg-gray-300 tooltip" title="الصنف معطل"></span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-5 mt-auto rtl:pr-2 ltr:pl-2">
                        <div
                            class="bg-{{ $statusColor }}-50/50 p-3 rounded-xl border border-{{ $statusColor }}-100/50 flex flex-col justify-center items-center text-center">
                            <span class="text-[10px] font-bold text-gray-500 uppercase mb-1">الرصيد الفعلي</span>
                            <div class="flex items-baseline gap-1 text-{{ $statusColor }}-700">
                                <span class="text-xl font-extrabold">{{ floatval($item->current_quantity) }}</span>
                                <span class="text-[10px] font-medium">{{ $item->unit ?? 'وحدة' }}</span>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex flex-col justify-center items-center text-center">
                            <span class="text-[10px] font-bold text-gray-500 uppercase mb-1">القيمة المالية</span>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-base font-extrabold text-gray-900">{{ number_format($totalVal, 0) }}</span>
                                <span class="text-[10px] text-gray-400 font-bold">ج.م</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 rtl:pr-2 ltr:pl-2">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">الحد الأدنى للطلب</span>
                            <span class="text-xs font-bold text-gray-700">{{ floatval($item->minimum_quantity) }}
                                {{ $item->unit }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button onclick="viewItemDetails(this)" data-item='{{ $itemJson }}'
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-500 bg-gray-50 hover:text-activeGold hover:bg-amber-50 transition-all flex items-center justify-center border border-gray-200 hover:border-amber-200"
                                title="تفاصيل">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <button onclick="editItem(this)" data-item="{{ $itemJson }}"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-500 bg-gray-50 hover:text-blue-600 hover:bg-blue-50 transition-all flex items-center justify-center border border-gray-200 hover:border-blue-200"
                                title="تعديل">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                            <button onclick="deleteItem({{ $item->id }})"
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg text-gray-500 bg-gray-50 hover:text-rose-600 hover:bg-rose-50 transition-all flex items-center justify-center border border-gray-200 hover:border-rose-200"
                                title="حذف">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-10 sm:p-16 text-center">
                    <div class="flex flex-col items-center justify-center gap-4">
                        <div
                            class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 shadow-inner border border-gray-100">
                            <i class="fa-solid fa-box-open text-4xl"></i>
                        </div>
                        <div>
                            <h4 class="text-gray-900 font-bold text-lg mb-2">لا توجد أصناف مطابقة</h4>
                            <p class="text-gray-500 text-sm">جرب تغيير محددات البحث أو قم بإضافة صنف جديد لمخزنك.</p>
                        </div>
                        <button onclick="window.location.href='{{ route('inventory.create') }}'"
                            class="mt-4 bg-activeGold text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md hover:bg-[#866d48] transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> إضافة صنف الآن
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($items->hasPages())
            <div class="mt-8 p-4 sm:p-5 border border-gray-100 rounded-2xl bg-white flex justify-center shadow-sm">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <div id="itemModal"
        class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex justify-center items-center p-3 sm:p-4 transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-full sm:max-h-[95vh]"
            id="itemModalContent">

            <div
                class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 relative shrink-0">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-activeGold to-[#927852]"></div>
                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 flex items-center gap-3" id="modalTitle">
                    <div
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-amber-100 text-activeGold flex items-center justify-center">
                        <i class="fa-solid fa-box text-base sm:text-lg"></i>
                    </div>
                    <span>إضافة صنف جديد</span>
                </h3>
                <button onclick="closeItemModal()"
                    class="text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition-colors bg-white border border-gray-200 w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-xmark sm:text-lg"></i>
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1">
                <form id="itemForm" onsubmit="saveItem(event)" class="space-y-5 sm:space-y-6">
                    <input type="hidden" id="item_id" name="item_id">

                    <div id="formErrors"
                        class="hidden p-3 sm:p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-base sm:text-lg"></i>
                        <span id="formErrorsText"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div class="md:col-span-2 pb-4 border-b border-gray-100">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><i
                                    class="fa-solid fa-circle-info text-activeGold"></i> البيانات الأساسية</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-700">اسم الصنف <span
                                            class="text-rose-500">*</span></label>
                                    <input type="text" id="form_name" name="name" required
                                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-700">كود الصنف (Barcode) <span
                                            class="text-rose-500">*</span></label>
                                    <input type="text" id="form_code" name="code" required
                                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-700">التصنيف <span
                                    class="text-rose-500">*</span></label>
                            <select id="form_category_id" name="category_id" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all cursor-pointer">
                                <option value="">-- اختر التصنيف --</option>
                                @foreach ($categoryStats as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-700">الوحدة القياسية</label>
                            <input type="text" id="form_unit" name="unit" placeholder="مثال: قطعة، لتر، كجم"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all">
                        </div>

                        <div class="md:col-span-2 pt-2 pb-4 border-b border-gray-100">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><i
                                    class="fa-solid fa-sliders text-activeGold"></i> إعدادات الرصيد والتسعير</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-700">الرصيد الحالي الافتتاحي</label>
                                    <input type="number" step="0.01" min="0" id="form_current_quantity"
                                        name="current_quantity" value="0"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-700">حد الطلب (الحد الأدنى)</label>
                                    <input type="number" step="0.01" min="0" id="form_minimum_quantity"
                                        name="minimum_quantity" value="0"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-700">آخر سعر شراء (التكلفة)</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" min="0"
                                            id="form_last_purchase_price" name="last_purchase_price" value="0"
                                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 rtl:pl-12 ltr:pr-12 text-sm font-mono focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all">
                                        <span
                                            class="absolute top-2.5 rtl:left-4 ltr:right-4 text-xs font-bold text-gray-400">EGP</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-xs font-bold text-gray-700">وصف الصنف / ملاحظات</label>
                            <textarea id="form_description" name="description" rows="2"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:border-activeGold focus:ring-4 focus:ring-activeGold/10 outline-none transition-all resize-none"></textarea>
                        </div>

                        <div
                            class="md:col-span-2 flex flex-row items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">حالة الصنف</span>
                                <span class="text-[10px] sm:text-xs text-gray-500">هل الصنف متاح للتعامل عليه في
                                    الحركات؟</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                <input type="checkbox" id="form_is_active" name="is_active" value="1" checked
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] rtl:after:right-[2px] rtl:after:left-auto after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-activeGold">
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <div
                class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2 sm:gap-3 shrink-0">
                <button type="button" onclick="closeItemModal()"
                    class="px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition-all flex-1 sm:flex-none text-center">إلغاء</button>
                <button type="button" onclick="document.getElementById('itemForm').dispatchEvent(new Event('submit'))"
                    id="saveBtn"
                    class="bg-gradient-to-r from-activeGold to-[#866d48] hover:from-[#866d48] hover:to-[#6a5537] text-white px-6 sm:px-8 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 flex-1 sm:flex-none">
                    <i class="fa-solid fa-check"></i> حفظ البيانات
                </button>
            </div>
        </div>
    </div>

    <div id="viewModal"
        class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex justify-center items-center p-3 sm:p-4 transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-full sm:max-h-[95vh]"
            id="viewModalContent">

            <div
                class="bg-gray-900 p-5 sm:px-8 sm:py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center relative overflow-hidden shrink-0 gap-4">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-activeGold/20 rounded-full blur-3xl"></div>
                <button onclick="closeViewModal()"
                    class="absolute top-4 left-4 sm:top-6 sm:left-6 text-gray-400 hover:text-white transition-colors bg-white/5 hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center backdrop-blur-sm z-20">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="relative z-10 flex items-center gap-4 text-white w-full">
                    <div
                        class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/10 glass-effect flex items-center justify-center text-2xl sm:text-3xl shadow-lg shrink-0 border border-white/20">
                        <i class="fa-solid fa-box-open text-activeGold"></i>
                    </div>
                    <div class="flex-1 pr-1">
                        <h3 class="text-lg sm:text-2xl font-extrabold mb-1.5 leading-tight pr-8 sm:pr-0" id="v_name">-
                        </h3>
                        <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-300">
                            <span class="bg-white/10 px-2 py-0.5 rounded font-mono border border-white/10"
                                id="v_code">-</span>
                            <span class="flex items-center"><i
                                    class="fa-solid fa-tags text-activeGold/80 text-[10px] mr-1"></i> <span
                                    id="v_category">-</span></span>
                        </div>
                    </div>
                </div>
                <div id="v_status_badge"
                    class="relative z-10 self-start sm:self-auto w-full sm:w-auto mt-2 sm:mt-0 flex justify-start sm:justify-end">
                </div>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                        <div
                            class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4 relative overflow-hidden">
                            <div class="absolute rtl:right-0 ltr:left-0 top-0 h-full w-1.5" id="v_qty_indicator"></div>

                            <div
                                class="flex-1 w-full text-center sm:text-right border-b border-gray-100 sm:border-b-0 pb-3 sm:pb-0">
                                <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase block mb-1">الرصيد
                                    الفعلي الآن</span>
                                <div class="flex items-baseline justify-center sm:justify-start gap-1">
                                    <span class="text-3xl sm:text-4xl font-extrabold" id="v_qty">-</span>
                                    <span class="text-xs sm:text-sm font-bold text-gray-400" id="v_unit_1">-</span>
                                </div>
                            </div>

                            <div class="hidden sm:block w-px h-12 bg-gray-100"></div>

                            <div class="flex-1 w-full flex justify-around sm:justify-between items-center text-center">
                                <div>
                                    <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase block mb-1">الحد
                                        الأدنى</span>
                                    <span class="text-lg sm:text-xl font-bold text-gray-700" id="v_min_qty">-</span>
                                </div>
                                <div class="hidden sm:block w-px h-8 bg-gray-100 mx-2"></div>
                                <div>
                                    <span class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase block mb-1">حالة
                                        التوفر</span>
                                    <span class="text-[10px] sm:text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap"
                                        id="v_stock_status_text">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:gap-4">
                            <div
                                class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gray-50 text-gray-400 absolute rtl:left-3 ltr:right-3 top-3 sm:top-4 flex items-center justify-center text-base sm:text-lg">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <span
                                    class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase block mb-1 sm:mb-2 pr-10 rtl:pr-0">القيمة
                                    الإجمالية</span>
                                <div class="text-lg sm:text-2xl font-extrabold text-gray-900 mt-2" id="v_total_value">-
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold uppercase">EGP</span>
                            </div>
                            <div
                                class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gray-50 text-gray-400 absolute rtl:left-3 ltr:right-3 top-3 sm:top-4 flex items-center justify-center text-base sm:text-lg">
                                    <i class="fa-solid fa-tag"></i>
                                </div>
                                <span
                                    class="text-[10px] sm:text-xs font-bold text-gray-500 uppercase block mb-1 sm:mb-2 pr-10 rtl:pr-0">سعر
                                    التكلفة</span>
                                <div class="text-lg sm:text-2xl font-extrabold text-gray-700 mt-2 font-mono"
                                    id="v_price">-</div>
                                <span class="text-[10px] text-gray-400 font-bold uppercase">EGP / <span
                                        id="v_unit_2"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 sm:space-y-6">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full">
                            <div
                                class="px-4 py-3 sm:px-5 sm:py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                                <h4 class="text-xs sm:text-sm font-bold text-gray-900"><i
                                        class="fa-solid fa-chart-line text-activeGold ml-1.5"></i> ملخص الحركات</h4>
                                <span
                                    class="text-[10px] sm:text-xs font-bold bg-activeGold/10 text-activeGold px-2 py-1 rounded-lg"
                                    id="v_tx_count">0 حركة</span>
                            </div>
                            <div class="p-4 sm:p-5 space-y-4 sm:space-y-5">
                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <span class="text-[10px] sm:text-xs font-bold text-gray-500">الوارد (شراء /
                                            تسوية)</span>
                                        <span class="text-xs sm:text-sm font-extrabold text-emerald-600"
                                            id="v_total_in">0</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 sm:h-2">
                                        <div class="bg-emerald-500 h-1.5 sm:h-2 rounded-full w-full"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between items-end mb-1.5">
                                        <span class="text-[10px] sm:text-xs font-bold text-gray-500">المنصرف (صرف /
                                            هالك)</span>
                                        <span class="text-xs sm:text-sm font-extrabold text-rose-500"
                                            id="v_total_out">0</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 sm:h-2">
                                        <div class="bg-rose-500 h-1.5 sm:h-2 rounded-full w-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 sm:mt-6 bg-amber-50/50 p-4 sm:p-5 rounded-2xl border border-amber-100/50">
                    <span
                        class="text-[10px] sm:text-xs font-bold text-activeGold uppercase flex items-center gap-2 mb-2"><i
                            class="fa-regular fa-comment-dots"></i> الوصف والملاحظات</span>
                    <p class="text-xs sm:text-sm text-gray-700 leading-relaxed font-medium" id="v_description">لا توجد
                        ملاحظات.</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Toggle Filters
        function toggleFilters() {
            const filters = document.getElementById('filtersSection');
            filters.classList.toggle('hidden');
        }

        // Modal Control Functions
        const itemModal = document.getElementById('itemModal');
        const itemModalContent = document.getElementById('itemModalContent');
        const viewModal = document.getElementById('viewModal');
        const viewModalContent = document.getElementById('viewModalContent');

        function openCreateModal() {
            document.getElementById('itemForm').reset();
            document.getElementById('item_id').value = '';
            document.getElementById('form_current_quantity').readOnly = false;
            document.getElementById('formErrors').classList.add('hidden');

            document.getElementById('modalTitle').innerHTML = `
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-amber-100 text-activeGold flex items-center justify-center"><i class="fa-solid fa-box text-base sm:text-lg"></i></div>
                <span>إضافة صنف جديد</span>
            `;

            itemModal.classList.remove('hidden');
            setTimeout(() => {
                itemModal.classList.remove('opacity-0');
                itemModalContent.classList.remove('scale-95');
            }, 10);
        }

        function closeItemModal() {
            itemModal.classList.add('opacity-0');
            itemModalContent.classList.add('scale-95');
            setTimeout(() => itemModal.classList.add('hidden'), 300);
        }

        function closeViewModal() {
            viewModal.classList.add('opacity-0');
            viewModalContent.classList.add('scale-95');
            setTimeout(() => viewModal.classList.add('hidden'), 300);
        }

        function editItem(btnElement) {
            const data = JSON.parse(btnElement.getAttribute('data-item'));

            document.getElementById('item_id').value = data.id;
            document.getElementById('form_name').value = data.name;
            document.getElementById('form_code').value = data.code;
            document.getElementById('form_category_id').value = data.category_id;
            document.getElementById('form_unit').value = data.unit || '';
            document.getElementById('form_minimum_quantity').value = data.minimum_quantity;
            document.getElementById('form_current_quantity').value = data.current_quantity;
            document.getElementById('form_last_purchase_price').value = data.last_purchase_price;
            document.getElementById('form_description').value = data.description || '';
            document.getElementById('form_is_active').checked = data.is_active;

            document.getElementById('formErrors').classList.add('hidden');
            document.getElementById('modalTitle').innerHTML = `
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-pen-to-square text-base sm:text-lg"></i></div>
                <span class="truncate">تعديل: ${data.name}</span>
            `;

            itemModal.classList.remove('hidden');
            setTimeout(() => {
                itemModal.classList.remove('opacity-0');
                itemModalContent.classList.remove('scale-95');
            }, 10);
        }

        function viewItemDetails(btnElement) {
            const d = JSON.parse(btnElement.getAttribute('data-item'));

            document.getElementById('v_name').textContent = d.name;
            document.getElementById('v_code').textContent = d.code;
            document.getElementById('v_category').textContent = d.category_name || 'بدون تصنيف';

            if (d.is_active) {
                document.getElementById('v_status_badge').innerHTML =
                    `<span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1.5 rounded-full text-xs font-bold flex items-center justify-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> نشط</span>`;
            } else {
                document.getElementById('v_status_badge').innerHTML =
                    `<span class="bg-gray-500/20 text-gray-300 border border-gray-500/30 px-3 py-1.5 rounded-full text-xs font-bold flex items-center justify-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-400"></span> معطل</span>`;
            }

            document.getElementById('v_qty').textContent = d.current_quantity;
            document.getElementById('v_unit_1').textContent = d.unit || 'وحدة';
            document.getElementById('v_unit_2').textContent = d.unit || 'وحدة';
            document.getElementById('v_min_qty').textContent = d.minimum_quantity;

            let statusHtml = '';
            let indicatorClass = '';
            if (d.current_quantity <= 0) {
                statusHtml = `<span class="bg-rose-100 text-rose-700">نفد تماماً</span>`;
                indicatorClass = 'bg-rose-500';
                document.getElementById('v_qty').className = 'text-3xl sm:text-4xl font-extrabold text-rose-600';
            } else if (d.current_quantity <= d.minimum_quantity) {
                statusHtml = `<span class="bg-orange-100 text-orange-700">وصل للأدنى</span>`;
                indicatorClass = 'bg-orange-500';
                document.getElementById('v_qty').className = 'text-3xl sm:text-4xl font-extrabold text-orange-500';
            } else {
                statusHtml = `<span class="bg-emerald-100 text-emerald-700">رصيد آمن</span>`;
                indicatorClass = 'bg-emerald-500';
                document.getElementById('v_qty').className = 'text-3xl sm:text-4xl font-extrabold text-gray-900';
            }

            document.getElementById('v_stock_status_text').innerHTML = statusHtml;
            document.getElementById('v_qty_indicator').className =
                `absolute rtl:right-0 ltr:left-0 top-0 h-full w-1.5 ${indicatorClass}`;

            document.getElementById('v_price').textContent = Number(d.last_purchase_price).toLocaleString('en-US');
            document.getElementById('v_total_value').textContent = Number(d.total_value).toLocaleString('en-US');

            document.getElementById('v_tx_count').textContent = d.transactions_count + ' حركة';
            document.getElementById('v_total_in').textContent = d.total_in || 0;
            document.getElementById('v_total_out').textContent = d.total_out || 0;
            document.getElementById('v_description').textContent = d.description ||
                'لا توجد ملاحظات إضافية مسجلة لهذا الصنف.';

            viewModal.classList.remove('hidden');
            setTimeout(() => {
                viewModal.classList.remove('opacity-0');
                viewModalContent.classList.remove('scale-95');
            }, 10);
        }

        async function saveItem(e) {
            e.preventDefault();
            const id = document.getElementById('item_id').value;
            const isEdit = !!id;
            const url = isEdit ? `/inventory/${id}` : '{{ route('inventory.store') }}';
            const formData = new FormData(e.target);

            if (isEdit) formData.append('_method', 'PUT');
            formData.set('is_active', document.getElementById('form_is_active').checked ? 1 : 0);

            const saveBtn = document.getElementById('saveBtn');
            const originalBtnHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.reload();
                } else {
                    const errDiv = document.getElementById('formErrors');
                    document.getElementById('formErrorsText').innerHTML = result.message ||
                        'يرجى مراجعة البيانات المدخلة والمحاولة مرة أخرى.';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                const errDiv = document.getElementById('formErrors');
                document.getElementById('formErrorsText').innerHTML = 'حدث خطأ في الاتصال بالخادم.';
                errDiv.classList.remove('hidden');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnHtml;
            }
        }

        async function deleteItem(id) {
            if (!confirm('هل أنت متأكد من حذف هذا الصنف؟ لا يمكن التراجع عن هذا الإجراء.')) return;
            try {
                const response = await fetch(`/inventory/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                if (result.success) window.location.reload();
            } catch (e) {
                console.error(e);
            }
        }
    </script>
@endpush
