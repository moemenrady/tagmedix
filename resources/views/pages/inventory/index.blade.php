@extends('layouts.app')

@section('content')
<div class="p-6 bg-[#f8fafc] min-h-screen" dir="rtl">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">المخزون</h1>
            <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                <a href="#" class="hover:text-slate-600 transition">الرئيسية</a>
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                <span class="text-slate-500">المخزون</span>
            </div>
        </div>
        <div class="flex items-center gap-3 self-end sm:self-center">
            <button class="bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 hover:bg-slate-50 transition shadow-sm">
                <i class="fa-solid fa-tags text-slate-400"></i>
                إضافة صنف جديد
            </button>
            <button class="bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 hover:bg-slate-800 transition shadow-sm">
                <i class="fa-solid fa-plus"></i>
                إضافة شراء جديد
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400">إجمالي الأصناف</p>
                <p class="text-2xl font-bold text-slate-800">128</p>
                <p class="text-[11px] text-slate-400">صنف</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 text-lg">
                <i class="fa-solid fa-basket-shopping"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400">إجمالي الكميات</p>
                <p class="text-2xl font-bold text-slate-800">5,430</p>
                <p class="text-[11px] text-slate-400">الوحدة</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 text-lg">
                <i class="fa-solid fa-cubes"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400">إجمالي المشتريات</p>
                <p class="text-2xl font-bold text-slate-800">245,780</p>
                <p class="text-[11px] text-slate-400">ج.م</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500 text-lg">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400">إجمالي المدفوع</p>
                <p class="text-2xl font-bold text-slate-800">180,250</p>
                <p class="text-[11px] text-slate-400">ج.م</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 text-lg">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-400">المتبقي للموردين</p>
                <p class="text-2xl font-bold text-slate-800">65,530</p>
                <p class="text-[11px] text-slate-400">ج.م</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center text-sky-500 text-lg">
                <i class="fa-solid fa-credit-card"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-8 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h3 class="text-base font-bold text-slate-800">الأصناف</h3>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" placeholder="بحث عن صنف..." class="w-full pl-3 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-slate-400 transition">
                </div>
                <button class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 hover:bg-slate-50 transition shadow-sm">
                    <i class="fa-solid fa-sliders text-slate-400"></i>
                    فلاتر
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-500 text-xs font-bold">
                        <th class="p-4 w-12 text-center">#</th>
                        <th class="p-4">اسم الصنف</th>
                        <th class="p-4">النوع</th>
                        <th class="p-4">الوحدة</th>
                        <th class="p-4 text-center">الكمية المتوفرة</th>
                        <th class="p-4">أقل كمية تنبيه</th>
                        <th class="p-4">آخر سعر شراء</th>
                        <th class="p-4">إجمالي تكلفة المخزون</th>
                        <th class="p-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-center font-medium text-slate-400">1</td>
                        <td class="p-4 font-semibold text-slate-800">قماش قطن ساده</td>
                        <td class="p-4">قماش</td>
                        <td class="p-4">متر</td>
                        <td class="p-4 text-center font-semibold">100</td>
                        <td class="p-4 text-slate-500">85.00 ج.م</td>
                        <td class="p-4 font-medium">72,250.00 ج.م</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 font-medium text-[11px]">متوفر</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-ellipsis"></i></button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-center font-medium text-slate-400">2</td>
                        <td class="p-4 font-semibold text-slate-800">قماش ليكرا</td>
                        <td class="p-4">قماش</td>
                        <td class="p-4">متر</td>
                        <td class="p-4 text-center font-semibold">80</td>
                        <td class="p-4 text-slate-500">120.00 ج.م</td>
                        <td class="p-4 font-medium">38,400.00 ج.م</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 font-medium text-[11px]">متوفر</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-ellipsis"></i></button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-center font-medium text-slate-400">3</td>
                        <td class="p-4 font-semibold text-slate-800">خيط بوليستر</td>
                        <td class="p-4">إكسسوار</td>
                        <td class="p-4">لفة</td>
                        <td class="p-4 text-center font-semibold">50</td>
                        <td class="p-4 text-slate-500">15.00 ج.م</td>
                        <td class="p-4 font-medium">900.00 ج.م</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 font-medium text-[11px]">منخفض</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-ellipsis"></i></button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-center font-medium text-slate-400">4</td>
                        <td class="p-4 font-semibold text-slate-800">سحاب مخفي</td>
                        <td class="p-4">إكسسوار</td>
                        <td class="p-4">قطعة</td>
                        <td class="p-4 text-center font-semibold">40</td>
                        <td class="p-4 text-slate-500">8.00 ج.م</td>
                        <td class="p-4 font-medium">360.00 ج.م</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 font-medium text-[11px]">منخفض</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-ellipsis"></i></button>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 text-center font-medium text-slate-400">5</td>
                        <td class="p-4 font-semibold text-slate-800">أزرار بلاستيك</td>
                        <td class="p-4">إكسسوار</td>
                        <td class="p-4">قطعة</td>
                        <td class="p-4 text-center font-semibold">100</td>
                        <td class="p-4 text-slate-500">1.50 ج.م</td>
                        <td class="p-4 font-medium">375.00 ج.م</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 font-medium text-[11px]">متوفر</span>
                        </td>
                        <td class="p-4 text-center">
                            <button class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-ellipsis"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 font-medium">
            <div>عرض 1 - 5 من 128 صنف</div>
            <div class="flex items-center gap-1">
                <button class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                <button class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center font-semibold">1</button>
                <button class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition">2</button>
                <button class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition">3</button>
                <span class="px-1 text-slate-400">...</span>
                <button class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition">10</button>
                <button class="w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between overflow-hidden">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">أصناف منخفضة المخزون</h3>
                    <a href="#" class="text-rose-500 hover:text-rose-600 text-xs font-semibold transition">عرض الكل</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 border-b border-slate-100 font-bold">
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3">الصنف</th>
                                <th class="p-3 text-center">الكمية المتوفرة</th>
                                <th class="p-3 text-center">أقل كمية تنبيه</th>
                                <th class="p-3 text-center">الإجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">1</td>
                                <td class="p-3 font-semibold text-slate-800">خيط بوليستر</td>
                                <td class="p-3 text-center font-medium text-rose-500">60 لفة</td>
                                <td class="p-3 text-center">50</td>
                                <td class="p-3 text-center">
                                    <button class="px-2 py-1 bg-rose-50 text-rose-600 rounded-lg font-semibold text-[11px] hover:bg-rose-100 transition">إعادة طلب</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">2</td>
                                <td class="p-3 font-semibold text-slate-800">سحاب مخفي</td>
                                <td class="p-3 text-center font-medium text-rose-500">45 قطعة</td>
                                <td class="p-3 text-center">40</td>
                                <td class="p-3 text-center">
                                    <button class="px-2 py-1 bg-rose-50 text-rose-600 rounded-lg font-semibold text-[11px] hover:bg-rose-100 transition">إعادة طلب</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">3</td>
                                <td class="p-3 font-semibold text-slate-800">بطانة قطن</td>
                                <td class="p-3 text-center font-medium text-rose-500">30 متر</td>
                                <td class="p-3 text-center">30</td>
                                <td class="p-3 text-center">
                                    <button class="px-2 py-1 bg-rose-50 text-rose-600 rounded-lg font-semibold text-[11px] hover:bg-rose-100 transition">إعادة طلب</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">4</td>
                                <td class="p-3 font-semibold text-slate-800">إيلاستيك وسط</td>
                                <td class="p-3 text-center font-medium text-rose-500">25 متر</td>
                                <td class="p-3 text-center">25</td>
                                <td class="p-3 text-center">
                                    <button class="px-2 py-1 bg-rose-50 text-rose-600 rounded-lg font-semibold text-[11px] hover:bg-rose-100 transition">إعادة طلب</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-4 bg-slate-50/60 border-t border-slate-100 text-slate-400 font-medium text-[11px]">
                4 أصناف تحتاج إعادة طلب
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between overflow-hidden">
            <div>
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">آخر المشتريات</h3>
                    <a href="#" class="text-sky-500 hover:text-sky-600 text-xs font-semibold transition">عرض الكل</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 border-b border-slate-100 font-bold">
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3">الصنف</th>
                                <th class="p-3 text-center">الكمية</th>
                                <th class="p-3">المورد</th>
                                <th class="p-3 text-center">التاريخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">1</td>
                                <td class="p-3 font-semibold text-slate-800">قماش...</td>
                                <td class="p-3 text-center font-semibold text-slate-700">100 متر</td>
                                <td class="p-3 text-slate-500">أقمشة النور</td>
                                <td class="p-3 text-center font-medium text-slate-400">2024-05-22</td>
                            </tr>
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">2</td>
                                <td class="p-3 font-semibold text-slate-800">قماش...</td>
                                <td class="p-3 text-center font-semibold text-slate-700">50 متر</td>
                                <td class="p-3 text-slate-500">أقمشة النور</td>
                                <td class="p-3 text-center font-medium text-slate-400">2024-05-21</td>
                            </tr>
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">3</td>
                                <td class="p-3 font-semibold text-slate-800">قماش...</td>
                                <td class="p-3 text-center font-semibold text-slate-700">20 لفة</td>
                                <td class="p-3 text-slate-500">النساجون المتحدة</td>
                                <td class="p-3 text-center font-medium text-slate-400">2024-05-20</td>
                            </tr>
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">4</td>
                                <td class="p-3 font-semibold text-slate-800">قماش...</td>
                                <td class="p-3 text-center font-semibold text-slate-700">50 قطعة</td>
                                <td class="p-3 text-slate-500">النساجون المتحدة</td>
                                <td class="p-3 text-center font-medium text-slate-400">2024-05-19</td>
                            </tr>
                            <tr>
                                <td class="p-3 text-center font-medium text-slate-400">5</td>
                                <td class="p-3 font-semibold text-slate-800">مورد إكسسوارات</td>
                                <td class="p-3 text-center font-semibold text-slate-700">500 قطعة</td>
                                <td class="p-3 text-slate-500">مورد إكسسوارات</td>
                                <td class="p-3 text-center font-medium text-slate-400">2024-05-18</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-3 bg-slate-50/60 border-t border-slate-100 flex justify-center">
                <button class="w-full py-2 bg-white border border-slate-200 text-slate-700 text-[11px] font-bold rounded-xl shadow-sm hover:bg-slate-50 transition">
                    عرض كل المشتريات
                </button>
            </div>
        </div>

    </div>
</div>
@endsection