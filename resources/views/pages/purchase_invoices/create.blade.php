@extends('layouts.app')

@section('title', 'إنشاء فاتورة شراء جديدة')
@section('page_title', 'إنشاء فاتورة شراء مواد خام ومستلزمات')

@section('content')
<div class="space-y-6 pb-16 font-sans w-full" x-data="purchaseInvoiceForm(@js($inventoryItems))">

    <!-- Flash Error Messages -->
    @if ($errors->any())
        <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm">
            <p class="font-bold mb-1">يرجى تصحيح الأخطاء التالية:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('purchase-invoices.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Top Header Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-cart-flatbed"></i>
                    </span>
                    <div>
                        <h2 class="text-xl font-black text-gray-900">فاتورة شراء جديدة</h2>
                        <p class="text-xs text-gray-400 mt-0.5">رقم مقترح: <span class="font-bold text-gray-700">{{ $nextInvoiceNumber }}</span></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <a href="{{ route('purchase-invoices.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-bold transition">
                    إلغاء
                </a>
                <button type="submit" class="flex-1 md:flex-initial bg-[#8A704C] hover:bg-[#735d3f] text-white px-6 py-2.5 rounded-xl font-bold transition shadow-md flex items-center justify-center gap-2 text-sm">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>حفظ واعتماد الفاتورة</span>
                </button>
            </div>
        </div>

        <!-- Supplier & Metadata Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-sm font-black text-gray-800 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-truck-field text-[#8A704C]"></i>
                <span>بيانات المورد والفاتورة</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Supplier -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">المورد *</label>
                    <select name="supplier_id" required class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm font-medium">
                        <option value="">-- اختر المورد --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ (old('supplier_id', request('supplier_id')) == $supplier->id) ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Supplier Invoice Number -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">رقم فاتورة المورد الورقية</label>
                    <input type="text" name="supplier_invoice_number" value="{{ old('supplier_invoice_number') }}" placeholder="رقم الفاتورة الورقية إن وجد"
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                </div>

                <!-- Invoice Date -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">تاريخ الفاتورة *</label>
                    <input type="date" name="invoice_date" required value="{{ old('invoice_date', date('Y-m-d')) }}"
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm font-medium">
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">تاريخ الاستحقاق (اختياري)</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                </div>
            </div>
        </div>

        <!-- Line Items Table Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                <h3 class="text-sm font-black text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-[#8A704C]"></i>
                    <span>أصناف الفاتورة والخامات المشتراة</span>
                </h3>

                <button type="button" @click="addItem()" class="bg-[#8A704C]/10 text-[#8A704C] hover:bg-[#8A704C] hover:text-white px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>إضافة بند صنف</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/70 text-gray-500 text-xs">
                        <tr>
                            <th class="p-3 w-1/3">الصنف المخزني *</th>
                            <th class="p-3 w-1/6">اللون / المتغير</th>
                            <th class="p-3 w-28">الكمية *</th>
                            <th class="p-3 w-28">الوحدة</th>
                            <th class="p-3 w-32">سعر الوحدة (ج.م) *</th>
                            <th class="p-3 w-32 text-left">الإجمالي (ج.م)</th>
                            <th class="p-3 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-gray-50/40 transition">
                                <!-- Item select -->
                                <td class="p-3">
                                    <select :name="`items[${index}][inventory_item_id]`" required
                                        x-model="item.inventory_item_id" @change="onItemChange(index)"
                                        class="w-full rounded-xl border-gray-300 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                                        <option value="">-- اختر الصنف --</option>
                                        <template x-for="inv in inventoryItems" :key="inv.id">
                                            <option :value="inv.id" x-text="`${inv.name} (${inv.code})`"></option>
                                        </template>
                                    </select>
                                </td>

                                <!-- Variant select if available -->
                                <td class="p-3">
                                    <select :name="`items[${index}][inventory_item_variant_id]`"
                                        x-model="item.inventory_item_variant_id"
                                        class="w-full rounded-xl border-gray-300 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                                        <option value="">-- عام / افتراضي --</option>
                                        <template x-for="v in getVariantsForItem(item.inventory_item_id)" :key="v.id">
                                            <option :value="v.id" x-text="v.color_name || v.sku || `متغير #${v.id}`"></option>
                                        </template>
                                    </select>
                                </td>

                                <!-- Quantity -->
                                <td class="p-3">
                                    <input type="number" step="0.001" min="0.001" required
                                        :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calculateTotals()"
                                        class="w-full rounded-xl border-gray-300 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none font-bold">
                                </td>

                                <!-- Unit type -->
                                <td class="p-3">
                                    <input type="text" :name="`items[${index}][unit_type]`" x-model="item.unit_type"
                                        class="w-full rounded-xl border-gray-300 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none">
                                </td>

                                <!-- Unit price -->
                                <td class="p-3">
                                    <input type="number" step="0.01" min="0" required
                                        :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" @input="calculateTotals()"
                                        class="w-full rounded-xl border-gray-300 border px-3 py-2 text-xs focus:ring-1 focus:ring-[#8A704C] outline-none font-bold">
                                </td>

                                <!-- Line Total -->
                                <td class="p-3 text-left font-black text-gray-900 text-sm">
                                    <span x-text="(item.quantity * item.unit_price).toFixed(2)"></span>
                                </td>

                                <!-- Remove row -->
                                <td class="p-3 text-center">
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                        class="text-red-400 hover:text-red-600 transition p-1">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="button" @click="addItem()" class="text-xs font-bold text-[#8A704C] hover:underline flex items-center gap-1.5">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>إضافة سطر آخر</span>
                </button>
            </div>
        </div>

        <!-- Financial Summary, Warehouse & Payment Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Notes & Settings (2 cols) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-gray-800 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-[#8A704C]"></i>
                    <span>إعدادات التوريد والدفع</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Status / Receiving action -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">حالة الفاتورة واستلام المخزن *</label>
                        <select name="status" required class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm font-bold">
                            <option value="received">معتمدة - استلام الأصناف في المخزن فوراً</option>
                            <option value="draft">مسودة - حفظ بدون إضافة للمخزن حالياً</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">عند اختيار "معتمدة" يتم زيادة رصيد المخزن آلياً لكل صنف.</p>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">طريقة الدفع للمسدد</label>
                        <select name="payment_method" class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">
                            <option value="cash">نقداً (كاش)</option>
                            <option value="bank_transfer">تحويل بنكي</option>
                            <option value="vodafone_cash">فودافون كاش / محفظة</option>
                            <option value="cheque">شيك بنكي</option>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">ملاحظات الفاتورة</label>
                    <textarea name="notes" rows="3" placeholder="أي شروط، أرقام شحنات، أو ملاحظات خاصة بالفاتورة..."
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 focus:ring-2 focus:ring-[#8A704C] outline-none text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Financial Totals Calculation Box (1 col) -->
            <div class="bg-gray-50/70 p-6 rounded-2xl border border-gray-200/70 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-gray-800 pb-2 border-b border-gray-200 flex items-center justify-between">
                    <span>ملخص الحساب</span>
                    <i class="fa-solid fa-calculator text-[#8A704C]"></i>
                </h3>

                <div class="space-y-3 text-sm">
                    <!-- Subtotal -->
                    <div class="flex justify-between items-center text-gray-600">
                        <span>إجمالي البنود:</span>
                        <span class="font-bold text-gray-900" x-text="subtotal.toFixed(2) + ' ج.م'"></span>
                    </div>

                    <!-- Discount -->
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-xs text-gray-600">خصم تجاري:</span>
                        <input type="number" step="0.01" min="0" name="discount" x-model.number="discount" @input="calculateTotals()"
                            class="w-28 rounded-lg border-gray-300 border px-2 py-1 text-xs text-left font-bold outline-none">
                    </div>

                    <!-- Tax -->
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-xs text-gray-600">ضريبة / دمغات:</span>
                        <input type="number" step="0.01" min="0" name="tax" x-model.number="tax" @input="calculateTotals()"
                            class="w-28 rounded-lg border-gray-300 border px-2 py-1 text-xs text-left font-bold outline-none">
                    </div>

                    <!-- Shipping Cost -->
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-xs text-gray-600">مصاريف نقل وشحن:</span>
                        <input type="number" step="0.01" min="0" name="shipping_cost" x-model.number="shippingCost" @input="calculateTotals()"
                            class="w-28 rounded-lg border-gray-300 border px-2 py-1 text-xs text-left font-bold outline-none">
                    </div>

                    <div class="h-px bg-gray-200 my-2"></div>

                    <!-- Net Total -->
                    <div class="flex justify-between items-center text-base font-black text-gray-900">
                        <span>الصافي الكلي:</span>
                        <span class="text-lg text-[#8A704C]" x-text="totalAmount.toFixed(2) + ' ج.م'"></span>
                    </div>

                    <div class="h-px bg-gray-200 my-2"></div>

                    <!-- Paid Amount -->
                    <div class="flex justify-between items-center gap-2">
                        <span class="text-xs font-bold text-emerald-700">المبلغ المسدد الآن:</span>
                        <input type="number" step="0.01" min="0" name="paid_amount" x-model.number="paidAmount" @input="calculateTotals()"
                            class="w-28 rounded-lg border-emerald-300 border px-2 py-1 text-xs text-left font-bold outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <!-- Remaining Amount -->
                    <div class="flex justify-between items-center text-sm font-black pt-1">
                        <span class="text-red-600">المتبقي (آجل للمورد):</span>
                        <span class="text-red-600" x-text="remainingAmount.toFixed(2) + ' ج.م'"></span>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-[#8A704C] hover:bg-[#735d3f] text-white py-3 rounded-xl font-bold transition shadow-md flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-check"></i>
                        <span>حفظ الفاتورة</span>
                    </button>
                </div>
            </div>

        </div>

    </form>

</div>

<script>
function purchaseInvoiceForm(inventoryItems) {
    return {
        inventoryItems: inventoryItems,
        items: [
            {
                inventory_item_id: '',
                inventory_item_variant_id: '',
                quantity: 1,
                unit_type: 'meter',
                unit_price: 0,
            }
        ],
        subtotal: 0,
        discount: 0,
        tax: 0,
        shippingCost: 0,
        totalAmount: 0,
        paidAmount: 0,
        remainingAmount: 0,

        init() {
            this.calculateTotals();
        },

        addItem() {
            this.items.push({
                inventory_item_id: '',
                inventory_item_variant_id: '',
                quantity: 1,
                unit_type: 'meter',
                unit_price: 0,
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },

        getVariantsForItem(itemId) {
            if (!itemId) return [];
            const found = this.inventoryItems.find(i => i.id == itemId);
            return found ? (found.variants || []) : [];
        },

        onItemChange(index) {
            const itemId = this.items[index].inventory_item_id;
            const found = this.inventoryItems.find(i => i.id == itemId);
            if (found) {
                this.items[index].unit_type = found.unit_type || 'meter';
                this.items[index].inventory_item_variant_id = '';
            }
            this.calculateTotals();
        },

        calculateTotals() {
            let sum = 0;
            this.items.forEach(item => {
                const q = parseFloat(item.quantity) || 0;
                const p = parseFloat(item.unit_price) || 0;
                sum += (q * p);
            });

            this.subtotal = sum;
            const d = parseFloat(this.discount) || 0;
            const t = parseFloat(this.tax) || 0;
            const s = parseFloat(this.shippingCost) || 0;

            this.totalAmount = Math.max(0, this.subtotal - d + t + s);

            const paid = parseFloat(this.paidAmount) || 0;
            this.remainingAmount = Math.max(0, this.totalAmount - paid);
        }
    };
}
window.purchaseInvoiceForm = purchaseInvoiceForm;

document.addEventListener('alpine:init', () => {
    Alpine.data('purchaseInvoiceForm', purchaseInvoiceForm);
});
</script>
@endsection
