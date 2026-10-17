@extends('layouts.app')

@section('title', 'تسجيل طلب مرتجع جديد')
@section('page_title', 'تسجيل مرتجع طلب / فحص استرجاع')

@section('content')
<div class="space-y-6 pb-16 font-sans w-full" x-data="returnOrderForm({{ $selectedOrder ? json_encode($selectedOrder) : 'null' }})">

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

    <!-- Step 1: Order Selection if not pre-selected -->
    @if (!$selectedOrder)
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-[#8A704C]"></i>
                <span>اختر الطلب المراد إرجاعه أو البحث برقم الأوردر</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Search input -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">أدخل رقم الأوردر للبحث الفوري</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="searchOrderNumber" placeholder="مثال: ORD-2026..."
                            class="flex-1 rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <button type="button" @click="fetchOrder()" class="bg-[#8A704C] hover:bg-[#735d3f] text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                            بحث
                        </button>
                    </div>
                </div>

                <!-- Quick Pick from recent orders -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">أو اختر من آخر الطلبات المسجلة</label>
                    <select @change="onOrderSelectChange($event.target.value)" class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <option value="">-- اختر من أحدث الأوردرات --</option>
                        @foreach ($recentOrders as $ro)
                            <option value="{{ $ro->id }}">{{ $ro->order_number }} - {{ $ro->customer_name }} ({{ number_format($ro->total_price, 2) }} ج.م)</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('returns.store') }}" method="POST" class="space-y-6" x-show="orderData" style="{{ $selectedOrder ? '' : 'display: none;' }}">
        @csrf
        <input type="hidden" name="order_id" :value="orderData ? orderData.id : ''">

        <!-- Header Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-rotate-left"></i>
                    </span>
                    <div>
                        <h2 class="text-xl font-black text-gray-900">
                            تسجيل مرتجع للطلب: <span class="text-[#8A704C]" x-text="orderData ? orderData.order_number : ''"></span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            العميل: <span class="font-bold text-gray-700" x-text="orderData ? (orderData.customer_name || (orderData.customer ? orderData.customer.name : '')) : ''"></span>
                            - إجمالي الطلب: <span class="font-bold text-gray-700" x-text="orderData ? orderData.total_price + ' ج.م' : ''"></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <a href="{{ route('returns.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-bold transition">
                    إلغاء
                </a>
                <button type="submit" class="flex-1 md:flex-initial bg-[#8A704C] hover:bg-[#735d3f] text-white px-6 py-2.5 rounded-xl font-bold transition shadow-md flex items-center justify-center gap-2 text-sm">
                    <i class="fa-solid fa-check"></i>
                    <span>حفظ طلب المرتجع</span>
                </button>
            </div>
        </div>

        <!-- Return Reason & Policy Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <h3 class="text-sm font-black text-gray-800 pb-2 border-b border-gray-100 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-question text-[#8A704C]"></i>
                <span>سبب الإرجاع والسياسة المالية</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Return Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">نوع الإرجاع *</label>
                    <select name="return_type" x-model="returnType" @change="toggleReturnType()" required class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <option value="full">مرتجع كلي (جميع بنود الطلب)</option>
                        <option value="partial">مرتجع جزئي (تحديد بنود معينة)</option>
                    </select>
                </div>

                <!-- Reason -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">سبب الإرجاع *</label>
                    <select name="reason" required class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-[#8A704C]">
                        <option value="مقاس غير مناسب (يحتاج مقاس آخر)">مقاس غير مناسب (يحتاج مقاس آخر)</option>
                        <option value="عيب خياطة / صناعة في المنتج">عيب خياطة / صناعة في المنتج</option>
                        <option value="رفض استلام من مندوب الشحن">رفض استلام من مندوب الشحن (Turbo)</option>
                        <option value="استبدال لون أو موديل مختلف">استبدال لون أو موديل مختلف</option>
                        <option value="المنتج لا يطابق المواصفات المطلوبة">المنتج لا يطابق المواصفات المطلوبة</option>
                        <option value="إلغاء الطلب بناءً على رغبة العميل">إلغاء الطلب بناءً على رغبة العميل</option>
                    </select>
                </div>

                <!-- Shipping fee deducted -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">مصاريف شحن المرتجع المخصومة (ج.م)</label>
                    <input type="number" step="0.01" min="0" name="shipping_cost_deducted" x-model.number="shippingCostDeducted" @input="recalcRefund()"
                        class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-[#8A704C]" value="0">
                    <p class="text-[11px] text-gray-400 mt-1">تخصم من المبلغ المسترد إذا كان العميل يتحمل مصاريف الشحن.</p>
                </div>
            </div>
        </div>

        <!-- Items in Order Selection Card -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <h3 class="text-sm font-black text-gray-800 pb-2 border-b border-gray-100 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-[#8A704C]"></i>
                    <span>حدد البنود والكميات المرتجعة</span>
                </span>
                <span class="text-xs text-gray-400 font-normal">يجب اختيار بند واحد على الأقل</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50/70 text-gray-500 text-xs">
                        <tr>
                            <th class="p-3.5 w-12 text-center">اختيار</th>
                            <th class="p-3.5">الصنف والموديل</th>
                            <th class="p-3.5">اللون والمقاس</th>
                            <th class="p-3.5 text-center">الكمية بالأوردر</th>
                            <th class="p-3.5 text-center w-32">الكمية المرتجعة *</th>
                            <th class="p-3.5 text-center">سعر الوحدة</th>
                            <th class="p-3.5 text-left">إجمالي البند</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in orderItems" :key="item.id">
                            <tr class="hover:bg-gray-50/40 transition">
                                <!-- Checkbox -->
                                <td class="p-3.5 text-center">
                                    <input type="checkbox" x-model="item.selected" @change="recalcRefund()"
                                        class="w-4 h-4 text-[#8A704C] rounded border-gray-300 focus:ring-[#8A704C] cursor-pointer">
                                </td>

                                <!-- Style / Type -->
                                <td class="p-3.5 font-bold text-gray-900">
                                    <div x-text="item.style ? item.style.name : 'منتج سكراب'"></div>
                                    <div class="text-[11px] text-gray-400 font-normal" x-text="item.type ? item.type.name : ''"></div>
                                </td>

                                <!-- Color & Size -->
                                <td class="p-3.5 text-xs text-gray-700">
                                    <span class="font-semibold" x-text="item.color ? item.color.name : '-'"></span> /
                                    <span class="font-semibold" x-text="item.size ? item.size.name : '-'"></span>
                                </td>

                                <!-- Order Qty -->
                                <td class="p-3.5 text-center font-bold text-gray-500" x-text="item.quantity"></td>

                                <!-- Returned Qty -->
                                <td class="p-3.5 text-center">
                                    <input type="number" min="1" :max="item.quantity" :disabled="!item.selected"
                                        x-model.number="item.return_quantity" @input="recalcRefund()"
                                        class="w-20 mx-auto text-center rounded-xl border-gray-300 border px-2 py-1.5 text-xs font-bold outline-none focus:ring-1 focus:ring-[#8A704C] disabled:bg-gray-100">
                                    <input type="hidden" :name="`items[${index}][order_item_id]`" :value="item.selected ? item.id : ''" :disabled="!item.selected">
                                    <input type="hidden" :name="`items[${index}][quantity]`" :value="item.selected ? item.return_quantity : ''" :disabled="!item.selected">
                                </td>

                                <!-- Unit Price -->
                                <td class="p-3.5 text-center text-xs font-bold text-gray-700" x-text="Number(item.price).toFixed(2) + ' ج.م'"></td>

                                <!-- Line Total -->
                                <td class="p-3.5 text-left font-black text-gray-900 text-sm" x-text="(item.selected ? (item.return_quantity * item.price) : 0).toFixed(2) + ' ج.م'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Refund Totals Box -->
            <div class="bg-gray-50/80 p-5 rounded-2xl border border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-xs text-gray-600 space-y-1">
                    <p>إجمالي قيمة البنود المرتجعة: <span class="font-bold text-gray-900" x-text="itemsTotal.toFixed(2) + ' ج.م'"></span></p>
                    <p>مصاريف الشحن المخصومة: <span class="font-bold text-red-600" x-text="'- ' + shippingCostDeducted.toFixed(2) + ' ج.م'"></span></p>
                </div>

                <div class="text-left rtl:text-right sm:rtl:text-left">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">صافي المبلغ المسترد للعميل</p>
                    <p class="text-2xl font-black text-[#8A704C]" x-text="netRefund.toFixed(2) + ' ج.م'"></p>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">ملاحظات إضافية على طلب المرتجع</label>
                <textarea name="notes" rows="2" placeholder="أي تفاصيل خاصة بمندوب الشحن، العميل، أو سبب الإرجاع..."
                    class="w-full rounded-xl border-gray-300 border px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#8A704C]"></textarea>
            </div>
        </div>

    </form>

</div>

<script>
function returnOrderForm(initialOrder) {
    return {
        orderData: initialOrder,
        orderItems: [],
        searchOrderNumber: '',
        returnType: 'full',
        shippingCostDeducted: 0,
        itemsTotal: 0,
        netRefund: 0,

        init() {
            if (this.orderData && this.orderData.items) {
                this.setupItems(this.orderData.items);
            }
        },

        setupItems(items) {
            this.orderItems = items.map(item => ({
                id: item.id,
                style: item.style,
                type: item.type,
                color: item.color,
                size: item.size,
                quantity: item.quantity,
                return_quantity: item.quantity,
                price: parseFloat(item.price) || 0,
                selected: true,
            }));
            this.recalcRefund();
        },

        toggleReturnType() {
            if (this.returnType === 'full') {
                this.orderItems.forEach(item => {
                    item.selected = true;
                    item.return_quantity = item.quantity;
                });
            }
            this.recalcRefund();
        },

        recalcRefund() {
            let sum = 0;
            this.orderItems.forEach(item => {
                if (item.selected) {
                    sum += (item.return_quantity * item.price);
                }
            });
            this.itemsTotal = sum;
            this.netRefund = Math.max(0, sum - (parseFloat(this.shippingCostDeducted) || 0));
        },

        onOrderSelectChange(orderId) {
            if (!orderId) return;
            window.location.href = `{{ route('returns.create') }}?order_id=${orderId}`;
        },

        fetchOrder() {
            if (!this.searchOrderNumber) return;
            window.location.href = `{{ route('returns.create') }}?search=${encodeURIComponent(this.searchOrderNumber)}`;
        }
    };
}
window.returnOrderForm = returnOrderForm;

document.addEventListener('alpine:init', () => {
    Alpine.data('returnOrderForm', returnOrderForm);
});
</script>
@endsection
