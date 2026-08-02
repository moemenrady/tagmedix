@extends('layouts.app')

@section('title', 'إدارة المخزون - Inventory Management')

@section('content')

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.35s ease-out forwards;
        }

        .animate-fade-down {
            animation: fadeInDown 0.35s ease-out forwards;
        }

        .table-container::-webkit-scrollbar {
            height: 6px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 8px;
        }
    </style>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 animate-fade-down">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-activeGold"></i>
                <span data-ar="إدارة الأصناف والمخزون" data-en="Items & Inventory Management">إدارة الأصناف والمخزون</span>
            </h2>
            <p class="text-sm text-gray-500 mt-1" data-ar="متابعة الكميات، القيم المالية، وحالة الأصناف بالكامل"
                data-en="Track quantities, financial values, and overall stock status">
                متابعة الكميات، القيم المالية، وحالة الأصناف بالكامل
            </p>
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <button onclick="toggleFilters()"
                class="bg-white border border-gray-200 text-gray-700 hover:text-activeGold hover:border-activeGold px-4 py-2.5 rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm font-medium flex-1 md:flex-none">
                <i class="fa-solid fa-filter text-xs"></i>
                <span data-ar="تصفية" data-en="Filter">تصفية</span>
            </button>
            <button onclick="openCreateModal()"
                class="bg-activeGold hover:bg-[#725c3d] text-white px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 text-sm font-bold flex-1 md:flex-none">
                <i class="fa-solid fa-plus"></i>
                <span data-ar="إضافة صنف جديد" data-en="Add New Item">إضافة صنف جديد</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-fade-down">

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1" data-ar="قيمة المخزون الحالية"
                        data-en="Total Inventory Value">قيمة المخزون الحالية</p>
                    <h3 class="text-2xl font-extrabold text-gray-800">
                        {{ number_format($stats['total_inventory_value'], 2) }}
                        <span class="text-xs font-normal text-gray-500">EGP</span>
                    </h3>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 text-activeGold flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500 flex items-center gap-1">
                <span class="font-bold text-gray-700">{{ number_format($stats['total_quantity']) }}</span>
                <span data-ar="إجمالي قطعة/وحدة مسجلة" data-en="Total units in stock">إجمالي قطعة/وحدة مسجلة</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1" data-ar="الأصناف النشطة"
                        data-en="Active Items">الأصناف النشطة</p>
                    <h3 class="text-2xl font-extrabold text-gray-800">
                        {{ $stats['active_items'] }}
                        <span class="text-xs text-gray-400 font-normal">/ {{ $stats['total_items'] }}</span>
                    </h3>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-box font-normal"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500 flex items-center gap-1">
                <span class="font-bold text-emerald-600">{{ $stats['active_categories'] }}</span>
                <span data-ar="تصنيفات نشطة بالمخزن" data-en="Active categories">تصنيفات نشطة بالمخزن</span>
            </div>
        </div>

        <a href="{{ route('inventory.index', ['low_stock' => 1]) }}"
            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition block group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1" data-ar="وشكت على النفاذ"
                        data-en="Low Stock Warning">وشكت على النفاذ</p>
                    <h3
                        class="text-2xl font-extrabold {{ $stats['low_stock_items'] > 0 ? 'text-amber-600' : 'text-gray-800' }}">
                        {{ $stats['low_stock_items'] }}
                    </h3>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-amber-600 font-medium flex items-center gap-1">
                <span data-ar="أصناف وصلت للحد الأدنى" data-en="Items hit minimum limit">أصناف وصلت للحد الأدنى</span>
                <i class="fa-solid fa-arrow-left text-[10px] rtl:inline ltr:hidden"></i>
                <i class="fa-solid fa-arrow-right text-[10px] hidden ltr:inline"></i>
            </div>
        </a>

        <a href="{{ route('inventory.index', ['out_of_stock' => 1]) }}"
            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition block group">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1" data-ar="منتهية من المخزن"
                        data-en="Out of Stock">منتهية من المخزن</p>
                    <h3
                        class="text-2xl font-extrabold {{ $stats['out_of_stock_items'] > 0 ? 'text-rose-600' : 'text-gray-800' }}">
                        {{ $stats['out_of_stock_items'] }}
                    </h3>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-rose-600 font-medium flex items-center gap-1">
                <span data-ar="أصناف كميتها صفر" data-en="Items with zero quantity">أصناف كميتها صفر</span>
                <i class="fa-solid fa-arrow-left text-[10px] rtl:inline ltr:hidden"></i>
                <i class="fa-solid fa-arrow-right text-[10px] hidden ltr:inline"></i>
            </div>
        </a>
    </div>

    <div id="filtersSection"
        class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6 {{ request()->hasAny(['search', 'category_id', 'is_active', 'low_stock', 'out_of_stock']) ? '' : 'hidden' }} animate-fade-down">
        <form action="{{ route('inventory.index') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

            <div class="space-y-1 sm:col-span-2">
                <label class="text-xs font-bold text-gray-600" data-ar="بحث بالاسم أو الكود"
                    data-en="Search Name or Code">بحث بالاسم أو الكود</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute top-3 rtl:right-3 ltr:left-3 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="أدخل اسم الصنف أو كود الصنف..."
                        class="w-full border border-gray-200 rounded-xl rtl:pr-9 ltr:pl-9 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-600" data-ar="التصنيف" data-en="Category">التصنيف</label>
                <select name="category_id"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50 cursor-pointer">
                    <option value="">-- <span data-ar="كل التصنيفات" data-en="All Categories">كل التصنيفات</span> --
                    </option>
                    @foreach ($categoryStats as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold text-gray-600" data-ar="الحالة" data-en="Status">الحالة</label>
                <select name="is_active"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50 cursor-pointer">
                    <option value="">-- <span data-ar="الكل" data-en="All">الكل</span> --</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشط / Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشط / Inactive
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2 pt-2 sm:pt-0">
                <button type="submit"
                    class="flex-1 bg-activeGold hover:bg-[#725c3d] text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition flex justify-center items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span data-ar="عرض" data-en="Apply">عرض</span>
                </button>
                <a href="{{ route('inventory.index') }}"
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
                        <th scope="col" class="px-5 py-4"><span data-ar="الصنف" data-en="Item">الصنف</span></th>
                        <th scope="col" class="px-5 py-4"><span data-ar="التصنيف" data-en="Category">التصنيف</span>
                        </th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="الكمية الحالية"
                                data-en="Current Qty">الكمية الحالية</span></th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="الحد الأدنى"
                                data-en="Min Qty">الحد الأدنى</span></th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="آخر سعر شراء"
                                data-en="Last Price">آخر سعر شراء</span></th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="إجمالي القيمة"
                                data-en="Total Value">إجمالي القيمة</span></th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="الحالة"
                                data-en="Status">الحالة</span></th>
                        <th scope="col" class="px-5 py-4 text-center"><span data-ar="الإجراءات"
                                data-en="Actions">الإجراءات</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                        @php
                            $isLow = $item->current_quantity <= $item->minimum_quantity && $item->current_quantity > 0;
                            $isOut = $item->current_quantity <= 0;
                            $totalVal = $item->current_quantity * $item->last_purchase_price;
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition group">
                            <td class="px-5 py-4">
                                <div class="flex flex-col">
                                    <span
                                        class="font-bold text-gray-800 group-hover:text-activeGold transition">{{ $item->name }}</span>
                                    <span class="text-xs text-gray-400 font-mono">{{ $item->code }}</span>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-lg text-xs font-semibold">
                                    {{ $item->category->name ?? '-' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <div
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-bold text-sm
                            {{ $isOut ? 'bg-rose-50 text-rose-600' : ($isLow ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-700') }}">
                                    <span>{{ floatval($item->current_quantity) }}</span>
                                    <span class="text-xs font-normal opacity-80">{{ $item->unit ?? '' }}</span>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="text-gray-500 font-medium">
                                    {{ floatval($item->minimum_quantity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="font-mono text-gray-700">
                                    {{ number_format($item->last_purchase_price, 2) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span
                                    class="font-bold text-gray-900 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-100">
                                    {{ number_format($totalVal, 2) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if ($item->is_active)
                                    <span
                                        class="inline-flex items-center gap-1 text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span data-ar="نشط" data-en="Active">نشط</span>
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        <span data-ar="معطل" data-en="Inactive">معطل</span>
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="viewItemDetails({{ $item->id }})"
                                        class="p-2 text-gray-400 hover:text-activeGold hover:bg-amber-50 rounded-lg transition"
                                        title="عرض البيانات">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <button onclick="editItem({{ $item->id }})"
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        title="تعديل">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button onclick="deleteItem({{ $item->id }})"
                                        class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                        title="حذف">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center">
                                        <i class="fa-solid fa-boxes-stacked text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium" data-ar="لا توجد أصناف مطابقة للبحث"
                                        data-en="No items found">
                                        لا توجد أصناف مطابقة للبحث
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="p-4 border-t border-gray-100 flex justify-center dir-ltr">
                {{ $items->links() }}
            </div>
        @endif
    </div>


    <div id="itemModal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center items-center p-4 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden transform scale-95 transition-transform duration-300"
            id="itemModalContent">

            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-activeGold"></div>
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2" id="modalTitle">
                    <i class="fa-solid fa-box text-activeGold"></i>
                    <span data-ar="إضافة صنف جديد" data-en="Add New Item">إضافة صنف جديد</span>
                </h3>
                <button onclick="closeItemModal()"
                    class="text-gray-400 hover:text-rose-500 transition bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="itemForm" onsubmit="saveItem(event)" class="p-6 space-y-4">
                <input type="hidden" id="item_id" name="item_id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700" data-ar="اسم الصنف *" data-en="Item Name *">اسم
                            الصنف *</label>
                        <input type="text" id="form_name" name="name" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700" data-ar="كود الصنف *" data-en="Item Code *">كود
                            الصنف *</label>
                        <input type="text" id="form_code" name="code" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50 font-mono">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700" data-ar="التصنيف *" data-en="Category *">التصنيف
                            *</label>
                        <select id="form_category_id" name="category_id" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50 cursor-pointer">
                            <option value="">-- اختر التصنيف --</option>
                            @foreach ($categoryStats as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700" data-ar="الوحدة (متر / ثوب / قطعة)"
                            data-en="Unit">الوحدة</label>
                        <input type="text" id="form_unit" name="unit" placeholder="مثال: متر، قطعة"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-700" data-ar="حد أدنى المخزون"
                            data-en="Minimum Quantity">حد أدنى المخزون</label>
                        <input type="number" step="0.01" min="0" id="form_minimum_quantity"
                            name="minimum_quantity" value="0"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50">
                    </div>

                    <div class="space-y-1 flex flex-col justify-end">
                        <label class="inline-flex items-center gap-2 cursor-pointer pb-2">
                            <input type="checkbox" id="form_is_active" name="is_active" value="1" checked
                                class="w-4 h-4 text-activeGold rounded focus:ring-activeGold border-gray-300">
                            <span class="text-sm font-bold text-gray-700" data-ar="تفعيل الصنف" data-en="Is Active">تفعيل
                                الصنف</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700" data-ar="الوصف" data-en="Description">الوصف</label>
                    <textarea id="form_description" name="description" rows="2"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-activeGold/30 outline-none transition bg-gray-50"></textarea>
                </div>

                <div id="formErrors"
                    class="hidden p-3 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-xs font-medium">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" onclick="closeItemModal()"
                        class="px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-100 transition"
                        data-ar="إلغاء" data-en="Cancel">إلغاء</button>
                    <button type="submit" id="saveBtn"
                        class="bg-activeGold hover:bg-[#725c3d] text-white px-6 py-2 rounded-xl text-sm font-bold shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span data-ar="حفظ" data-en="Save">حفظ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div id="viewModal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center items-center p-4 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform scale-95 transition-transform duration-300"
            id="viewModalContent">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center relative">
                <div class="absolute top-0 left-0 w-full h-1 bg-activeGold"></div>
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-activeGold"></i>
                    <span data-ar="تفاصيل الصنف" data-en="Item Details">تفاصيل الصنف</span>
                </h3>
                <button onclick="closeViewModal()"
                    class="text-gray-400 hover:text-rose-500 transition bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block" data-ar="الاسم"
                            data-en="Name">الاسم</span>
                        <span class="font-bold text-gray-800 text-sm" id="v_name">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block" data-ar="الكود"
                            data-en="Code">الكود</span>
                        <span class="font-mono text-activeGold font-bold text-sm" id="v_code">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block" data-ar="التصنيف"
                            data-en="Category">التصنيف</span>
                        <span class="font-bold text-gray-700 text-sm" id="v_category">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block" data-ar="الوحدة"
                            data-en="Unit">الوحدة</span>
                        <span class="font-bold text-gray-700 text-sm" id="v_unit">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block" data-ar="الكمية الحالية"
                            data-en="Current Qty">الكمية الحالية</span>
                        <span class="font-bold text-emerald-600 text-base" id="v_qty">-</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase block" data-ar="آخر سعر شراء"
                            data-en="Last Price">آخر سعر شراء</span>
                        <span class="font-bold text-gray-800 text-sm" id="v_price">-</span>
                    </div>
                </div>

                <div class="bg-[#faf9f7] p-3 rounded-xl border border-[#e5dfd5]">
                    <span class="text-xs font-bold text-activeGold block mb-1" data-ar="الوصف"
                        data-en="Description">الوصف</span>
                    <p class="text-sm text-gray-600" id="v_description">لا يوجد وصف مسجل.</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Toggle Filters Accordion
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
            document.getElementById('formErrors').classList.add('hidden');
            document.getElementById('modalTitle').innerHTML = `
            <i class="fa-solid fa-box text-activeGold"></i>
            <span data-ar="إضافة صنف جديد" data-en="Add New Item">إضافة صنف جديد</span>
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

        // Save/Update Item AJAX
        async function saveItem(e) {
            e.preventDefault();
            const id = document.getElementById('item_id').value;
            const isEdit = !!id;
            const url = isEdit ? `/inventory/${id}` : '{{ route('inventory.store') }}';

            const formData = new FormData(e.target);
            if (isEdit) formData.append('_method', 'PUT');

            // Handle Checkbox
            formData.set('is_active', document.getElementById('form_is_active').checked ? 1 : 0);

            const saveBtn = document.getElementById('saveBtn');
            saveBtn.disabled = true;
            saveBtn.classList.add('opacity-50');

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
                    closeItemModal();
                    window.location.reload(); // Quick refresh to sync pagination & metrics
                } else {
                    const errDiv = document.getElementById('formErrors');
                    errDiv.classList.remove('hidden');
                    errDiv.innerHTML = result.message || 'يرجى التأكد من البيانات المدخلة.';
                }
            } catch (err) {
                console.error(err);
            } finally {
                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-50');
            }
        }

        // Edit Item Modal Data Populator
        async function editItem(id) {
            try {
                const res = await fetch(`/inventory/${id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const result = await res.json();

                if (result.success) {
                    const data = result.data;
                    document.getElementById('item_id').value = data.id;
                    document.getElementById('form_name').value = data.name;
                    document.getElementById('form_code').value = data.code;
                    document.getElementById('form_category_id').value = data.category_id;
                    document.getElementById('form_unit').value = data.unit || '';
                    document.getElementById('form_minimum_quantity').value = parseFloat(data.minimum_quantity);
                    document.getElementById('form_description').value = data.description || '';
                    document.getElementById('form_is_active').checked = data.is_active;

                    document.getElementById('modalTitle').innerHTML = `
                    <i class="fa-solid fa-pen-to-square text-activeGold"></i>
                    <span data-ar="تعديل بيانات الصنف" data-en="Edit Item">تعديل بيانات الصنف</span>
                `;

                    itemModal.classList.remove('hidden');
                    setTimeout(() => {
                        itemModal.classList.remove('opacity-0');
                        itemModalContent.classList.remove('scale-95');
                    }, 10);
                }
            } catch (e) {
                console.error(e);
            }
        }

        // View Details Modal
        async function viewItemDetails(id) {
            try {
                const res = await fetch(`/inventory/${id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const result = await res.json();

                if (result.success) {
                    const d = result.data;
                    document.getElementById('v_name').textContent = d.name;
                    document.getElementById('v_code').textContent = d.code;
                    document.getElementById('v_category').textContent = d.category ? d.category.name : '-';
                    document.getElementById('v_unit').textContent = d.unit || '-';
                    document.getElementById('v_qty').textContent = parseFloat(d.current_quantity);
                    document.getElementById('v_price').textContent = parseFloat(d.last_purchase_price).toFixed(2) +
                        ' EGP';
                    document.getElementById('v_description').textContent = d.description ||
                        'لا يوجد وصف مسجل لهذا الصنف.';

                    viewModal.classList.remove('hidden');
                    setTimeout(() => {
                        viewModal.classList.remove('opacity-0');
                        viewModalContent.classList.remove('scale-95');
                    }, 10);
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Delete Item
        async function deleteItem(id) {
            const isAr = localStorage.getItem('lang') !== 'en';
            if (!confirm(isAr ? 'هل أنت تأكد من نقل هذا الصنف إلى المحذوفات؟' :
                    'Are you sure you want to delete this item?')) return;

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
