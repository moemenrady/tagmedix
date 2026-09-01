@extends('layouts.app')

@section('title', 'إدارة المخزن والتحكم')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6 p-4 sm:p-6 text-darkText font-sans">

        <!-- Header & Quick Actions Bar -->
        <div
            class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white p-5 rounded-2xl border border-secondaryCream shadow-sm">
            <div>
                <h1 class="text-2xl font-bold text-darkText flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-primaryBrown"></i>
                    لوحة التحكم بالمخزن
                </h1>
                <p class="text-xs sm:text-sm text-mutedBrown mt-1">متابعة الأرصدة الحالية، الوحدات الفيزيائية، وتتبع حركات
                    المخزون بدقة.</p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                <!-- Explain Feature Button (!) -->
                <button type="button" onclick="openGuideModal()"
                    class="p-2.5 bg-roseAccent/20 hover:bg-roseAccent/40 text-primaryBrown rounded-xl transition-all border border-roseAccent/40 flex items-center justify-center font-bold cursor-pointer"
                    title="شرح خصائص المخزن">
                    <i class="fa-solid fa-circle-info text-lg"></i>
                </button>

                <!-- Quick Action 1: Stock In -->
                <a href="{{ route('inventory.stocks.create') }}"
                    class="flex-1 sm:flex-initial bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-sm cursor-pointer">
                    <i class="fa-solid fa-arrow-down-to-line"></i>
                    <span>إدخال مخزون (Stock In)</span>
                </a>

                <!-- Quick Action 2: Stock Out (Issue/Adjustment Out) -->
                <button type="button" onclick="openStockOutModal()"
                    class="flex-1 sm:flex-initial bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs sm:text-sm px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-sm cursor-pointer">
                    <i class="fa-solid fa-arrow-up-from-line"></i>
                    <span>صرف مخزون (Stock Out)</span>
                </button>
            </div>
        </div>

        <!-- Analytics Dashboard Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-secondaryCream shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-mutedBrown">إجمالي الأصناف</span>
                <div class="flex justify-between items-end mt-2">
                    <span class="text-xl sm:text-2xl font-bold text-darkText">{{ $stats['total_items'] ?? 0 }}</span>
                    <i class="fa-solid fa-boxes-stacked text-primaryBrown/40 text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-secondaryCream shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-mutedBrown">الألوان والأنواع (Variants)</span>
                <div class="flex justify-between items-end mt-2">
                    <span class="text-xl sm:text-2xl font-bold text-darkText">{{ $stats['total_variants'] ?? 0 }}</span>
                    <i class="fa-solid fa-swatchbook text-primaryBrown/40 text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-secondaryCream shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-mutedBrown">الوحدات الفعلية (Tops/Units)</span>
                <div class="flex justify-between items-end mt-2">
                    <span class="text-xl sm:text-2xl font-bold text-darkText">{{ $stats['total_units'] ?? 0 }}</span>
                    <i class="fa-solid fa-barcode text-primaryBrown/40 text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-secondaryCream shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-mutedBrown">إجمالي قيمة المخزون</span>
                <div class="flex justify-between items-end mt-2">
                    <span class="text-lg sm:text-xl font-bold text-emerald-700">
                        {{ number_format($stats['total_value'] ?? 0, 2) }}
                        <span class="text-xs">ج.م</span>
                    </span>
                    <i class="fa-solid fa-coins text-emerald-500/40 text-2xl"></i>
                </div>
            </div>

            <div
                class="col-span-2 sm:col-span-1 bg-white p-4 rounded-2xl border border-secondaryCream shadow-sm flex flex-col justify-between">
                <span class="text-xs font-bold text-mutedBrown">أصناف تحت حد الأمان</span>
                <div class="flex justify-between items-end mt-2">
                    <span class="text-xl sm:text-2xl font-bold text-rose-600">{{ $stats['low_stock_count'] ?? 0 }}</span>
                    <i class="fa-solid fa-triangle-exclamation text-rose-500/40 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Search & Live Filters Section -->
        <div class="bg-white p-4 rounded-2xl border border-secondaryCream shadow-sm space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <!-- Search Bar -->
                <div class="relative sm:col-span-2">
                    <i class="fa-solid fa-magnifying-glass absolute right-3.5 top-3 text-mutedBrown text-xs"></i>
                    <input id="inventorySearch" type="text"
                        placeholder="بحث بالاسم، الكود، كود التوب (Unit Code)، أو الـ SKU..."
                        class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl pr-9 pl-3 py-2 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                </div>

                <!-- Category Filter -->
                <div>
                    <select id="categoryFilter"
                        class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                        <option value="">جميع الفئات</option>
                        <option value="fabric">أقمشة (Fabric)</option>
                        <option value="accessory">إكسسوارات (Accessory)</option>
                        <option value="finished_product">منتجات نهائية (Finished Product)</option>
                    </select>
                </div>

                <!-- Tracking Type Filter -->
                <div>
                    <select id="trackingTypeFilter"
                        class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:border-primaryBrown transition-colors">
                        <option value="">جميع طرق التتبع</option>
                        <option value="bulk">إجمالي (Bulk)</option>
                        <option value="individual">منفرد/توبات (Individual)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Stock Table View -->
        <div class="bg-white rounded-2xl border border-secondaryCream shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-primaryBg/60 text-mutedBrown border-b border-secondaryCream text-xs font-bold">
                            <th class="p-3.5"></th>
                            <th class="p-3.5">الصنف</th>
                            <th class="p-3.5">الكود الداخلي</th>
                            <th class="p-3.5">الفئة</th>
                            <th class="p-3.5">طريقة التتبع</th>
                            <th class="p-3.5">إجمالي الرصيد</th>
                            <th class="p-3.5">حد الأمان</th>
                            <th class="p-3.5">الحالة</th>
                            <th class="p-3.5 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTableBody" class="divide-y divide-secondaryCream/60 text-xs sm:text-sm">
                        @forelse($items as $item)
                            <!-- Parent Item Row -->
                            <tr class="item-row hover:bg-primaryBg/30 transition-colors" data-id="{{ $item->id }}"
                                data-name="{{ strtolower($item->name) }}" data-code="{{ strtolower($item->code) }}"
                                data-category="{{ $item->category }}" data-tracking="{{ $item->stock_tracking_type }}">
                                <td class="p-3.5 text-center">
                                    <button type="button" onclick="toggleItem({{ $item->id }})"
                                        class="text-primaryBrown hover:text-mutedBrown p-1 cursor-pointer">
                                        <i id="icon-{{ $item->id }}" class="fa-solid fa-chevron-left"></i>
                                    </button>
                                </td>
                                <td class="p-3.5 font-bold text-darkText">
                                    {{ $item->name }}
                                    <span
                                        class="block text-[10px] text-mutedBrown font-normal">{{ $item->variants->count() }}
                                        متغيرات/ألوان</span>
                                </td>
                                <td class="p-3.5 font-mono text-xs text-mutedBrown">{{ $item->code }}</td>
                                <td class="p-3.5">
                                    @if ($item->category === 'fabric')
                                        <span
                                            class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded-md text-[11px] font-semibold border border-blue-100">قماش</span>
                                    @elseif($item->category === 'accessory')
                                        <span
                                            class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md text-[11px] font-semibold border border-amber-100">إكسسوار</span>
                                    @else
                                        <span
                                            class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded-md text-[11px] font-semibold border border-purple-100">منتج
                                            نهائي</span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $item->stock_tracking_type === 'individual' ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-gray-50 text-gray-700 border-gray-100' }}">
                                        {{ $item->stock_tracking_type === 'individual' ? 'تتبع فردي (Tops)' : 'تتبع إجمالي (Bulk)' }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-bold">
                                    {{ number_format($item->dashboard_quantity, 2) }}
                                    {{ $item->unit_type }}
                                </td>
                                <td class="p-3.5 text-mutedBrown">
                                    {{ number_format($item->minimum_stock, 2) }} {{ $item->unit_type }}
                                </td>
                                <td class="p-3.5">
                                    @if ($item->dashboard_quantity <= $item->minimum_stock)
                                        <span
                                            class="inline-flex items-center gap-1 text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full text-[11px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> منخفض
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full text-[11px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> ممتاز
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openHistoryModal({{ $item->id }}, 'item')"
                                            class="p-1.5 text-mutedBrown hover:text-primaryBrown hover:bg-softPink/40 rounded-lg transition-colors cursor-pointer"
                                            title="سجل الحركات">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Expanded Level 2: Item Variants / Colors -->
                            <tr id="item-details-{{ $item->id }}" class="hidden">
                                <td colspan="9" class="bg-primaryBg/30 p-3 sm:p-4 border-inner">
                                    <div class="space-y-3 mr-2 sm:mr-6 border-r-2 border-primaryBrown/30 pr-3">
                                        <h4 class="font-bold text-xs text-primaryBrown flex items-center gap-1.5">
                                            <i class="fa-solid fa-swatchbook"></i> الألوان والمتغيرات الخاصة بـ
                                            ({{ $item->name }})
                                            :
                                        </h4>

                                        <div class="grid grid-cols-1 gap-2">
                                            @foreach ($item->variants as $variant)
                                                <div
                                                    class="bg-white border border-secondaryCream rounded-xl p-3 shadow-2xs space-y-2">
                                                    <div class="flex flex-wrap justify-between items-center gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="w-3.5 h-3.5 rounded-full border border-black/10 shadow-2xs"
                                                                style="background-color: {{ $variant->color->hex_code ?? '#ccc' }}"></span>
                                                            <span
                                                                class="font-bold text-darkText text-xs">{{ $variant->color->name ?? 'لون غير محدد' }}</span>
                                                            <span
                                                                class="text-[11px] font-mono text-mutedBrown">({{ $variant->sku }})</span>
                                                        </div>

                                                        <div class="flex items-center gap-3 text-xs">
                                                            <div>
                                                                <span class="text-mutedBrown">الرصيد: </span>
                                                                <span
                                                                    class="font-bold text-primaryBrown">{{ number_format($variant->dashboard_quantity, 2) }}
                                                                    {{ $item->unit_type }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="text-mutedBrown">القيمة: </span>
                                                                <span
                                                                    class="font-bold text-emerald-700">{{ number_format($variant->dashboard_value, 2) }} ج.م
                                                                    ج.م</span>
                                                            </div>
                                                            <button type="button"
                                                                onclick="openHistoryModal({{ $variant->id }}, 'variant')"
                                                                class="text-xs text-primaryBrown hover:underline flex items-center gap-1 cursor-pointer">
                                                                <i class="fa-solid fa-history"></i> السجل
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Expanded Level 3: Individual Units (Physical Rolls/Tops) -->
                                                    @if ($item->stock_tracking_type === 'individual')
                                                        <div class="pt-2 border-t border-secondaryCream/40">
                                                            <div
                                                                class="text-[11px] font-bold text-mutedBrown mb-1.5 flex justify-between items-center">
                                                                <span>الوحدات الفيزيائية المتاحة (Physical Units):</span>
                                                                <span>عدد الوحدات:
                                                                    {{ $variant->available_units_count }}</span>
                                                            </div>

                                                            <div
                                                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                                                @forelse($variant->inventoryUnits as $unit)
                                                                    <div
                                                                        class="bg-primaryBg/40 border border-secondaryCream rounded-lg p-2 flex flex-col justify-between text-xs">
                                                                        <div
                                                                            class="flex justify-between items-center mb-1">
                                                                            <span
                                                                                class="font-mono font-bold text-darkText">{{ $unit->unit_code }}</span>
                                                                            <span
                                                                                class="px-1.5 py-0.5 rounded text-[10px] font-semibold 
                                                                            {{ $unit->status === 'available' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                                            {{ $unit->status === 'reserved' ? 'bg-amber-100 text-amber-800' : '' }}
                                                                            {{ $unit->status === 'issued' ? 'bg-gray-100 text-gray-800' : '' }}
                                                                            {{ $unit->status === 'damaged' ? 'bg-rose-100 text-rose-800' : '' }}">
                                                                                {{ $unit->status }}
                                                                            </span>
                                                                        </div>
                                                                        <div
                                                                            class="flex justify-between items-center text-mutedBrown text-[11px]">
                                                                            <span>{{ number_format($unit->quantity, 2) }}
                                                                                {{ $unit->unit_type }}</span>
                                                                            <button type="button"
                                                                                onclick="openHistoryModal({{ $unit->id }}, 'unit')"
                                                                                class="text-primaryBrown hover:text-darkText cursor-pointer"
                                                                                title="سجل حركة التوب">
                                                                                <i class="fa-solid fa-receipt"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                @empty
                                                                    <div
                                                                        class="col-span-full text-center py-2 text-xs text-mutedBrown">
                                                                        لا توجد وحدات فعلية مسجلة.
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                                <tr id="emptyStateRow">
                                    <td colspan="9" class="text-center py-8 text-mutedBrown">
                                        <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                                        لا توجد أصناف في المخزن حالياً.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modals Section -->

            <!-- Modal 1: Feature Explanation Modal (!) -->
            <div id="guideModal"
                class="hidden fixed inset-0 bg-darkText/60 backdrop-blur-sm z-50 items-center justify-center p-4">
                <div
                    class="bg-white rounded-2xl max-w-2xl w-full border border-secondaryCream shadow-2xl overflow-hidden relative">
                    <div class="bg-primaryBrown text-white p-4 flex justify-between items-center">
                        <h3 class="font-bold text-base flex items-center gap-2">
                            <i class="fa-solid fa-circle-question"></i>
                            دليل وفلسفة نظام إدارة المخزن
                        </h3>
                        <button type="button" onclick="closeGuideModal()"
                            class="text-white/80 hover:text-white text-lg cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-5 overflow-y-auto max-h-[75vh] space-y-4 text-xs sm:text-sm text-darkText leading-relaxed">
                        <div class="p-3 bg-softPink/20 rounded-xl border border-roseAccent/30">
                            <strong class="text-primaryBrown block mb-1 font-bold text-sm">المستويات الخمسة لإدارة
                                المخزون:</strong>
                            يعتمد النظام على هيكلية متكاملة تفصل التعريف عن الرصيد وعن الحركة لضمان أعلى درجات التتبع المحاسبي.
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="bg-primaryBg p-2 rounded-lg text-primaryBrown font-bold shrink-0">1</div>
                                <div>
                                    <strong class="block font-bold"> Master Item (تعريف الصنف الأساسي):</strong>
                                    يمثل التعريف العام للخامة مثل (قماش Rosaline) بدقة وبدون تكرار البيانات.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-primaryBg p-2 rounded-lg text-primaryBrown font-bold shrink-0">2</div>
                                <div>
                                    <strong class="block font-bold"> Variants (الألوان والأنواع):</strong>
                                    يحدد الاختلاف الفيزيائي للصنف مثل (Rosaline - Black)، مما يمنع تكرار تكويد الأصناف.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-primaryBg p-2 rounded-lg text-primaryBrown font-bold shrink-0">3</div>
                                <div>
                                    <strong class="block font-bold"> Aggregate Stock (الرصيد المجمع):</strong>
                                    سجل سريع لقراءة إجمالي رصيد الصنف واللون فوراً لسرعة الأداء بدون حسابات بطيئة.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-primaryBg p-2 rounded-lg text-primaryBrown font-bold shrink-0">4</div>
                                <div>
                                    <strong class="block font-bold"> Physical Units (التوبات/الوحدات):</strong>
                                    يمثل كل ثوب أو طرد برقم كود فريد (Barcode)، مما يتيح الصرف والاستهلاك الدقيق للتوب المحدد.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="bg-primaryBg p-2 rounded-lg text-primaryBrown font-bold shrink-0">5</div>
                                <div>
                                    <strong class="block font-bold"> Transactions Ledger (سجل الحركات):</strong>
                                    الدفتر المحاسبي القياسي الذي يستحيل تعديل أرقامه؛ يسجل أي إضافة، صرف، تلف، أو تسوية مع هوية
                                    المستخدم والسبب.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-secondaryCream bg-primaryBg/30 text-left">
                        <button type="button" onclick="closeGuideModal()"
                            class="bg-primaryBrown text-white font-bold px-5 py-2 rounded-xl text-xs cursor-pointer">فهمت
                            ذلك</button>
                    </div>
                </div>
            </div>

            <!-- Modal 2: Stock Out Modal -->
            <div id="stockOutModal"
                class="hidden fixed inset-0 bg-darkText/60 backdrop-blur-sm z-50 items-center justify-center p-4">
                <div
                    class="bg-white rounded-2xl max-w-lg w-full border border-secondaryCream shadow-2xl overflow-hidden relative">
                    <div class="p-4 text-white flex justify-between items-center bg-rose-700">
                        <h3 class="font-bold text-sm flex items-center gap-2">
                            <i class="fa-solid fa-arrow-up-from-line"></i>
                            <span>صرف مخزون سريع (Stock Out)</span>
                        </h3>
                        <button type="button" onclick="closeStockOutModal()"
                            class="text-white/80 hover:text-white cursor-pointer">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="{{ route('inventory.transactions.store') }}" method="POST"
                        class="p-5 space-y-3 text-xs sm:text-sm">
                        @csrf
                        <input type="hidden" name="action_type" value="issue">

                        <div>
                            <label class="block font-bold text-darkText mb-1">نوع الحركة الفرعي <span
                                    class="text-red-500">*</span></label>
                            <select name="type" required
                                class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl p-2.5">
                                <option value="issue">صرف لأمر إنتاج (Issue)</option>
                                <option value="adjustment_out">تسوية بالنقص (Adjustment Out)</option>
                                <option value="damage">تسجيل هالك/تالف (Damage)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-darkText mb-1">اختر الصنف / المتغير <span
                                    class="text-red-500">*</span></label>
                            <select name="inventory_item_variant_id" required
                                class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl p-2.5">
                                <option value="">اختر العنصر...</option>
                                @foreach ($items as $i)
                                    @foreach ($i->variants as $v)
                                        <option value="{{ $v->id }}">{{ $i->name }} -
                                            {{ $v->color->name ?? '' }} ({{ $v->sku }})</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-darkText mb-1">الكمية <span
                                        class="text-red-500">*</span></label>
                                <input type="number" step="0.001" min="0.001" name="quantity" required
                                    placeholder="0.00"
                                    class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl p-2.5">
                            </div>
                            <div>
                                <label class="block font-bold text-darkText mb-1">تكلفة الوحدة (إن وجد)</label>
                                <input type="number" step="0.01" min="0" name="unit_cost" placeholder="0.00"
                                    class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl p-2.5">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-darkText mb-1">ملاحظات / رقم المرجع</label>
                            <textarea name="notes" rows="2" placeholder="أدخل أي تفاصيل إضافية هنا..."
                                class="w-full bg-primaryBg/40 border border-secondaryCream rounded-xl p-2.5"></textarea>
                        </div>

                        <div class="pt-3 border-t border-secondaryCream flex justify-end gap-2">
                            <button type="button" onclick="closeStockOutModal()"
                                class="px-4 py-2 bg-secondaryCream text-darkText font-bold rounded-xl cursor-pointer">إلغاء</button>
                            <button type="submit"
                                class="px-5 py-2 text-white font-bold rounded-xl cursor-pointer bg-rose-700 hover:bg-rose-800">
                                تأكيد الحركة
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Vanilla JS Implementation -->
        <script>
            // Modal functions
            function openGuideModal() {
                const modal = document.getElementById('guideModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeGuideModal() {
                const modal = document.getElementById('guideModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function openStockOutModal() {
                const modal = document.getElementById('stockOutModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeStockOutModal() {
                const modal = document.getElementById('stockOutModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            // Table Expand / Collapse
            function toggleItem(itemId) {
                const detailsRow = document.getElementById(`item-details-${itemId}`);
                const icon = document.getElementById(`icon-${itemId}`);

                if (!detailsRow || !icon) return;

                if (detailsRow.classList.contains('hidden')) {
                    detailsRow.classList.remove('hidden');
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-down');
                } else {
                    detailsRow.classList.add('hidden');
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-left');
                }
            }

            // History Modal Action (Temporary behavior as requested)
            function openHistoryModal(id, level) {
                alert(`سيتم عرض سجل حركات الـ Ledger للمستوى (${level}) ذو المعرف رقم: ${id}`);
            }

            // Event Listeners for Filters and Modal closings
            document.addEventListener('DOMContentLoaded', function() {

                // --- Live Search and Filtering Logic ---
                const searchInput = document.getElementById('inventorySearch');
                const categoryFilter = document.getElementById('categoryFilter');
                const trackingTypeFilter = document.getElementById('trackingTypeFilter');
                const itemRows = document.querySelectorAll('.item-row');

                function filterInventory() {
                    const query = searchInput.value.toLowerCase().trim();
                    const category = categoryFilter.value;
                    const trackingType = trackingTypeFilter.value;

                    itemRows.forEach(row => {
                        const itemId = row.getAttribute('data-id');
                        const itemName = row.getAttribute('data-name');
                        const itemCode = row.getAttribute('data-code');
                        const itemCat = row.getAttribute('data-category');
                        const itemTrack = row.getAttribute('data-tracking');

                        const detailsRow = document.getElementById(`item-details-${itemId}`);
                        const icon = document.getElementById(`icon-${itemId}`);

                        // Check conditions
                        const matchesSearch = itemName.includes(query) || itemCode.includes(query);
                        const matchesCategory = category === '' || itemCat === category;
                        const matchesTracking = trackingType === '' || itemTrack === trackingType;

                        if (matchesSearch && matchesCategory && matchesTracking) {
                            row.style.display = ''; // Show row
                        } else {
                            row.style.display = 'none'; // Hide row

                            // Force close expanded variants if parent is hidden
                            if (detailsRow && !detailsRow.classList.contains('hidden')) {
                                detailsRow.classList.add('hidden');
                                if (icon) {
                                    icon.classList.remove('fa-chevron-down');
                                    icon.classList.add('fa-chevron-left');
                                }
                            }
                        }
                    });
                }

                if (searchInput) searchInput.addEventListener('input', filterInventory);
                if (categoryFilter) categoryFilter.addEventListener('change', filterInventory);
                if (trackingTypeFilter) trackingTypeFilter.addEventListener('change', filterInventory);


                // --- Modal Outside Click Logic ---
                const guideModal = document.getElementById('guideModal');
                if (guideModal) {
                    guideModal.addEventListener('click', function(event) {
                        if (event.target === this) {
                            closeGuideModal();
                        }
                    });
                }

                const stockOutModal = document.getElementById('stockOutModal');
                if (stockOutModal) {
                    stockOutModal.addEventListener('click', function(event) {
                        if (event.target === this) {
                            closeStockOutModal();
                        }
                    });
                }

                // --- Keyboard Escape Logic for Modals ---
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        if (guideModal && !guideModal.classList.contains('hidden')) {
                            closeGuideModal();
                        }
                        if (stockOutModal && !stockOutModal.classList.contains('hidden')) {
                            closeStockOutModal();
                        }
                    }
                });

            });
        </script>
    @endsection
