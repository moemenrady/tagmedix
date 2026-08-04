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

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 animate-fade-down">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                <div
                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-activeGold to-[#927852] flex items-center justify-center text-white shadow-lg">
                    <i class="fa-solid fa-boxes-stacked text-xl"></i>
                </div>
                <span>إدارة الأصناف والمخزون</span>
            </h2>
            <p class="text-sm text-gray-500 mt-2 font-medium">
                متابعة شاملة للكميات، القيم المالية، وحركة الأصناف بشكل لحظي
            </p>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <button onclick="toggleFilters()"
                class="bg-white border border-gray-200 text-gray-700 hover:text-activeGold hover:border-activeGold px-5 py-3 rounded-xl shadow-sm transition-all duration-300 flex items-center justify-center gap-2 text-sm font-bold flex-1 md:flex-none">
                <i class="fa-solid fa-filter text-sm"></i>
                <span>تصفية متقدمة</span>
            </button>
            <button onclick="openCreateModal()"
                class="bg-gradient-to-r from-activeGold to-[#866d48] hover:from-[#866d48] hover:to-[#6a5537] text-white px-6 py-3 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 text-sm font-bold flex-1 md:flex-none">
                <i class="fa-solid fa-plus text-lg"></i>
                <span>إضافة صنف</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 animate-fade-down" style="animation-delay: 0.1s;">
        <div
            class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-amber-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">إجمالي قيمة المخزون</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ number_format($stats['total_inventory_value'], 2) }} <span
                            class="text-sm font-bold text-gray-400">EGP</span>
                    </h3>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-amber-50 text-activeGold flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600 flex items-center gap-2 font-medium">
                <i class="fa-solid fa-layer-group text-activeGold"></i>
                <span>إجمالي <b class="text-gray-900">{{ number_format($stats['total_quantity']) }}</b> وحدة مسجلة</span>
            </div>
        </div>

        <div
            class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-blue-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">الأصناف والتصنيفات</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ $stats['active_items'] }} <span class="text-base text-gray-400 font-bold">/
                            {{ $stats['total_items'] }}</span>
                    </h3>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-cubes text-xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600 flex items-center gap-2 font-medium">
                <span
                    class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-md font-bold text-xs">{{ $stats['active_categories'] }}
                    نشط</span>
                <span>تصنيف متوفر</span>
            </div>
        </div>

        <a href="{{ route('inventory.index', ['low_stock' => 1]) }}"
            class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group block relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-orange-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">وشكت على النفاذ</p>
                    <h3
                        class="text-3xl font-extrabold tracking-tight {{ $stats['low_stock_items'] > 0 ? 'text-orange-500' : 'text-gray-900' }}">
                        {{ $stats['low_stock_items'] }}
                    </h3>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-orange-600 flex items-center justify-between font-medium">
                <span>أصناف وصلت للحد الأدنى</span>
                <i
                    class="fa-solid fa-arrow-left text-xs rtl:inline ltr:hidden group-hover:-translate-x-1 transition-transform"></i>
            </div>
        </a>

        <a href="{{ route('inventory.index', ['out_of_stock' => 1]) }}"
            class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 group block relative overflow-hidden">
            <div
                class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-rose-50 to-transparent rounded-full group-hover:scale-150 transition-transform duration-500">
            </div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-2">نفدت تماماً</p>
                    <h3
                        class="text-3xl font-extrabold tracking-tight {{ $stats['out_of_stock_items'] > 0 ? 'text-rose-600' : 'text-gray-900' }}">
                        {{ $stats['out_of_stock_items'] }}
                    </h3>
                </div>
                <div
                    class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-solid fa-ban border-rose-500"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-rose-600 flex items-center justify-between font-medium">
                <span>أصناف رصيدها صفر</span>
                <i
                    class="fa-solid fa-arrow-left text-xs rtl:inline ltr:hidden group-hover:-translate-x-1 transition-transform"></i>
            </div>
        </a>
    </div>

    <div id="filtersSection"
        class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 {{ request()->hasAny(['search', 'category_id', 'is_active', 'low_stock', 'out_of_stock']) ? '' : 'hidden' }} animate-fade-down relative overflow-hidden">
        <div class="absolute left-0 top-0 h-full w-1 bg-activeGold"></div>
        <form action="{{ route('inventory.index') }}" method="GET"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">

            <div class="space-y-2 lg:col-span-2">
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

            <div class="flex items-end gap-3 pt-2 lg:pt-0">
                <button type="submit"
                    class="flex-1 bg-gray-900 hover:bg-black text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex justify-center items-center gap-2">
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

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fade-up"
        style="animation-delay: 0.2s;">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left rtl:text-right text-sm">
                <thead
                    class="bg-gray-50/80 text-gray-600 text-xs uppercase tracking-wider font-extrabold border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-5">تفاصيل الصنف</th>
                        <th scope="col" class="px-6 py-5 text-center">الرصيد والحد الأدنى</th>
                        <th scope="col" class="px-6 py-5 text-center">بيانات مالية</th>
                        <th scope="col" class="px-6 py-5 text-center">الحالة</th>
                        <th scope="col" class="px-6 py-5 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-700">
                    @forelse($items as $item)
                        @php
                            $isLow = $item->current_quantity <= $item->minimum_quantity && $item->current_quantity > 0;
                            $isOut = $item->current_quantity <= 0;
                            $totalVal = $item->current_quantity * $item->last_purchase_price;

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

                                'total_value' => (float) ($item->current_quantity * $item->last_purchase_price),

                                'is_active' => (bool) $item->is_active,

                                'transactions_count' => (int) $item->inventory_transactions_count,

                                'total_in' => (float) ($item->total_purchased_quantity ?? 0),

                                'total_out' => (float) ($item->total_issued_quantity ?? 0),
                            ];

                            $itemJson = json_encode($itemData, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors group">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center shrink-0 border border-gray-200">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-gray-900 text-base mb-0.5 group-hover:text-activeGold transition-colors">{{ $item->name }}</span>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span
                                                class="font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ $item->code }}</span>
                                            <span class="text-gray-400">•</span>
                                            <span
                                                class="text-activeGold font-medium">{{ $item->category->name ?? 'بدون تصنيف' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg font-extrabold text-sm border
                                    {{ $isOut ? 'bg-rose-50 text-rose-600 border-rose-100' : ($isLow ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100') }}">
                                        <span>{{ floatval($item->current_quantity) }}</span>
                                        <span class="text-xs font-medium opacity-75">{{ $item->unit ?? 'وحدة' }}</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-bold uppercase">الحد الأدنى:
                                        {{ floatval($item->minimum_quantity) }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="font-extrabold text-gray-900">{{ number_format($totalVal, 2) }} <span
                                            class="text-xs text-gray-400 font-normal">EGP</span></span>
                                    <span class="text-[10px] text-gray-500 font-medium">سعر الوحدة:
                                        {{ number_format($item->last_purchase_price, 2) }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($item->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-md font-bold">
                                        <span class="relative flex h-2 w-2"><span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span
                                                class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                                        نشط
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs text-gray-500 bg-gray-100 border border-gray-200 px-2.5 py-1 rounded-md font-bold">
                                        <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                        معطل
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="viewItemDetails(this)" data-item='{{ $itemJson }}'
                                        class="w-8 h-8 rounded-lg text-gray-400 hover:text-activeGold hover:bg-amber-50 transition-all flex items-center justify-center shadow-sm border border-transparent hover:border-amber-100 tooltip"
                                        title="عرض التفاصيل">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <button onclick="editItem(this)" data-item="{{ $itemJson }}"
                                        class="w-8 h-8 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all flex items-center justify-center shadow-sm border border-transparent hover:border-blue-100"
                                        title="تعديل">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="deleteItem({{ $item->id }})"
                                        class="w-8 h-8 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-all flex items-center justify-center shadow-sm border border-transparent hover:border-rose-100"
                                        title="حذف">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center bg-gray-50/30">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <div
                                        class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center text-gray-300 shadow-inner">
                                        <i class="fa-solid fa-box-open text-4xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-gray-900 font-bold text-base mb-1">لا توجد أصناف مطابقة</h4>
                                        <p class="text-gray-500 text-sm">جرب تغيير محددات البحث أو قم بإضافة صنف جديد.</p>
                                    </div>
                                    <button onclick="openCreateModal()"
                                        class="mt-2 bg-activeGold text-white px-5 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-[#866d48] transition-colors">
                                        إضافة صنف الآن
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-center dir-ltr">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <div id="itemModal"
        class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex justify-center items-center p-4 transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[95vh]"
            id="itemModalContent">

            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 relative shrink-0">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-activeGold to-[#927852]"></div>
                <h3 class="text-xl font-extrabold text-gray-900 flex items-center gap-3" id="modalTitle">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-activeGold flex items-center justify-center">
                        <i class="fa-solid fa-box text-lg"></i>
                    </div>
                    <span>إضافة صنف جديد</span>
                </h3>
                <button onclick="closeItemModal()"
                    class="text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition-colors bg-white border border-gray-200 w-10 h-10 rounded-xl flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <form id="itemForm" onsubmit="saveItem(event)" class="space-y-6">
                    <input type="hidden" id="item_id" name="item_id">

                    <div id="formErrors"
                        class="hidden p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm font-bold flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                        <span id="formErrorsText"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 pb-4 border-b border-gray-100">
                            <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><i
                                    class="fa-solid fa-circle-info text-activeGold"></i> البيانات الأساسية</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                            <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2"><i
                                    class="fa-solid fa-sliders text-activeGold"></i> إعدادات الرصيد والتسعير</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-700 tooltip"
                                        title="يستخدم لإنشاء الصنف برصيد افتتاحي، إن وجد">الرصيد الحالي</label>
                                    <input type="number" step="0.01" min="0" id="form_current_quantity"
                                        name="current_quantity" value="0"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:border-activeGold font-mono focus:ring-4 focus:ring-activeGold/10 outline-none transition-all">
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
                            class="md:col-span-2 flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">حالة الصنف</span>
                                <span class="text-xs text-gray-500">هل الصنف متاح للتعامل عليه في الحركات؟</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
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

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="closeItemModal()"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 transition-all">إلغاء</button>
                <button type="button" onclick="document.getElementById('itemForm').dispatchEvent(new Event('submit'))"
                    id="saveBtn"
                    class="bg-gradient-to-r from-activeGold to-[#866d48] hover:from-[#866d48] hover:to-[#6a5537] text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> حفظ البيانات
                </button>
            </div>
        </div>
    </div>

    <div id="viewModal"
        class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex justify-center items-center p-4 transition-all duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden transform scale-95 transition-all duration-300 flex flex-col max-h-[95vh]"
            id="viewModalContent">

            <div class="bg-gray-900 px-8 py-6 flex justify-between items-start relative overflow-hidden shrink-0">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-activeGold/20 rounded-full blur-3xl"></div>
                <div class="relative z-10 flex items-center gap-5 text-white w-full">
                    <div
                        class="w-16 h-16 rounded-2xl bg-white/10 glass-effect flex items-center justify-center text-3xl shadow-lg shrink-0 border border-white/20">
                        <i class="fa-solid fa-box-open text-activeGold"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-extrabold mb-1" id="v_name">-</h3>
                                <div class="flex items-center gap-3 text-sm text-gray-300">
                                    <span class="bg-white/10 px-2 py-0.5 rounded font-mono border border-white/10"
                                        id="v_code">-</span>
                                    <span><i class="fa-solid fa-tags text-activeGold/80 text-xs mr-1"></i> <span
                                            id="v_category">-</span></span>
                                </div>
                            </div>
                            <div id="v_status_badge"></div>
                        </div>
                    </div>
                </div>
                <button onclick="closeViewModal()"
                    class="absolute top-6 left-6 text-gray-400 hover:text-white transition-colors bg-white/5 hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center backdrop-blur-sm z-20">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div
                            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4 relative overflow-hidden">
                            <div class="absolute left-0 top-0 h-full w-1" id="v_qty_indicator"></div>

                            <div class="flex-1 w-full text-center sm:text-right">
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-1">الرصيد الحالي
                                    بالمخزن</span>
                                <div class="flex items-end justify-center sm:justify-start gap-2">
                                    <span class="text-4xl font-extrabold text-gray-900" id="v_qty">-</span>
                                    <span class="text-sm font-bold text-gray-400 mb-1" id="v_unit_1">-</span>
                                </div>
                            </div>

                            <div class="hidden sm:block w-px h-12 bg-gray-100"></div>

                            <div class="flex-1 w-full text-center sm:text-center">
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-1">الحد الأدنى</span>
                                <span class="text-xl font-bold text-gray-700" id="v_min_qty">-</span>
                            </div>

                            <div class="hidden sm:block w-px h-12 bg-gray-100"></div>

                            <div class="flex-1 w-full text-center sm:text-left">
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-1">حالة التوفر</span>
                                <span class="text-sm font-bold px-3 py-1 rounded-full" id="v_stock_status_text">-</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 absolute left-4 top-4 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-coins"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-2">إجمالي قيمة
                                    المخزون</span>
                                <div class="text-2xl font-extrabold text-gray-900 mt-2" id="v_total_value">-</div>
                                <span class="text-[10px] text-gray-400 font-bold uppercase mt-1">EGP</span>
                            </div>
                            <div
                                class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 absolute left-4 top-4 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-tag"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-2">التكلفة (سعر
                                    الشراء)</span>
                                <div class="text-2xl font-extrabold text-gray-700 mt-2 font-mono" id="v_price">-</div>
                                <span class="text-[10px] text-gray-400 font-bold uppercase mt-1">EGP / <span
                                        id="v_unit_2"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full">
                            <div class="px-5 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                                <h4 class="text-sm font-bold text-gray-900"><i
                                        class="fa-solid fa-chart-line text-activeGold mr-2"></i> ملخص الحركات</h4>
                                <span class="text-xs font-bold bg-activeGold/10 text-activeGold px-2 py-1 rounded-lg"
                                    id="v_tx_count">0 حركة</span>
                            </div>
                            <div class="p-5 space-y-4">
                                <div>
                                    <div class="flex justify-between items-end mb-1">
                                        <span class="text-xs font-bold text-gray-500">إجمالي الوارد (مشتريات/تسويات)</span>
                                        <span class="text-sm font-extrabold text-emerald-600" id="v_total_in">0</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-emerald-500 h-1.5 rounded-full w-[100%]"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between items-end mb-1">
                                        <span class="text-xs font-bold text-gray-500">إجمالي المنصرف (صرف/هالك)</span>
                                        <span class="text-sm font-extrabold text-rose-500" id="v_total_out">0</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-rose-500 h-1.5 rounded-full w-[100%]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 bg-amber-50/50 p-5 rounded-2xl border border-amber-100/50">
                    <span class="text-xs font-bold text-activeGold uppercase flex items-center gap-2 mb-2"><i
                            class="fa-regular fa-comment-dots"></i> الوصف والملاحظات</span>
                    <p class="text-sm text-gray-700 leading-relaxed font-medium" id="v_description">لا توجد ملاحظات.</p>
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
            document.getElementById('form_current_quantity').readOnly = false; // السماح بالإضافة لأول مرة
            document.getElementById('formErrors').classList.add('hidden');

            document.getElementById('modalTitle').innerHTML = `
            <div class="w-10 h-10 rounded-lg bg-amber-100 text-activeGold flex items-center justify-center"><i class="fa-solid fa-box text-lg"></i></div>
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

        // Edit Item (Instant load using data attributes!)
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
            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-pen-to-square text-lg"></i></div>
            <span>تعديل بيانات الصنف: ${data.name}</span>
        `;

            itemModal.classList.remove('hidden');
            setTimeout(() => {
                itemModal.classList.remove('opacity-0');
                itemModalContent.classList.remove('scale-95');
            }, 10);
        }

        // View Details (Instant load Dashboard view)
        function viewItemDetails(btnElement) {

            const d = JSON.parse(btnElement.getAttribute('data-item'));

            // Header
            document.getElementById('v_name').textContent = d.name;
            document.getElementById('v_code').textContent = d.code;
            document.getElementById('v_category').textContent = d.category_name;

            if (d.is_active) {
                document.getElementById('v_status_badge').innerHTML =
                    `<span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> نشط</span>`;
            } else {
                document.getElementById('v_status_badge').innerHTML =
                    `<span class="bg-gray-500/20 text-gray-300 border border-gray-500/30 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-400"></span> غير نشط</span>`;
            }

            // Body Stats
            document.getElementById('v_qty').textContent = d.current_quantity;
            document.getElementById('v_unit_1').textContent = d.unit || 'وحدة';
            document.getElementById('v_unit_2').textContent = d.unit || 'وحدة';
            document.getElementById('v_min_qty').textContent = d.minimum_quantity;

            // Logic for stock status colors
            let statusHtml = '';
            let indicatorClass = '';
            if (d.current_quantity <= 0) {
                statusHtml = `<span class="bg-rose-100 text-rose-700">نفد تماماً</span>`;
                indicatorClass = 'bg-rose-500';
                document.getElementById('v_qty').className = 'text-4xl font-extrabold text-rose-600';
            } else if (d.current_quantity <= d.minimum_quantity) {
                statusHtml = `<span class="bg-orange-100 text-orange-700">وصل للحد الأدنى</span>`;
                indicatorClass = 'bg-orange-500';
                document.getElementById('v_qty').className = 'text-4xl font-extrabold text-orange-500';
            } else {
                statusHtml = `<span class="bg-emerald-100 text-emerald-700">رصيد آمن</span>`;
                indicatorClass = 'bg-emerald-500';
                document.getElementById('v_qty').className = 'text-4xl font-extrabold text-gray-900';
            }
            document.getElementById('v_stock_status_text').innerHTML = statusHtml;
            document.getElementById('v_qty_indicator').className =
                `absolute right-0 top-0 h-full w-1.5 ${indicatorClass}`; // right-0 for RTL

            // Financials
            document.getElementById('v_price').textContent = Number(d.last_purchase_price).toLocaleString('en-US', {
                minimumFractionDigits: 2
            });
            document.getElementById('v_total_value').textContent = Number(d.total_value).toLocaleString('en-US', {
                minimumFractionDigits: 2
            });

            // Transactions
            document.getElementById('v_tx_count').textContent = d.transactions_count + ' حركة مسجلة';
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

        // Save/Update Item AJAX
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

        // Delete Item
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
