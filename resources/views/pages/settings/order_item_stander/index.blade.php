@extends('layouts.app') {{-- أو اسم الـ layout الرئيسي الخاص بك --}}

@section('title', 'معايير عناصر الطلب')

@section('content')
<div class="container mx-auto max-w-7xl px-2 sm:px-4 py-4 sm:py-6 animate-fade-in">

    {{-- رسائل النجاح --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative mb-6 shadow-sm flex items-center gap-3 animate-slide-up">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span class="block sm:inline font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- رأس الصفحة --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8 gap-4 bg-white p-4 sm:p-5 rounded-2xl shadow-sm border border-secondaryCream">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-darkText flex items-center gap-2">
                <div class="w-10 h-10 rounded-full bg-softBeige flex items-center justify-center text-primaryBrown">
                    <i class="fa-solid fa-ruler-combined"></i>
                </div>
                <span data-ar="معايير عناصر الطلب" data-en="Order Item Standards">معايير عناصر الطلب</span>
            </h2>
            <p class="text-xs sm:text-sm text-mutedBrown mt-2" data-ar="إدارة وتخصيص أسعار ومعايير التصنيع للطلبات" data-en="Manage and customize manufacturing standards and prices">
                إدارة وتخصيص أسعار ومعايير التصنيع للطلبات
            </p>
        </div>
        
        @if(!isset($orderItemStander))
        <button type="button" onclick="document.getElementById('standerFormCard').classList.toggle('hidden')" class="w-full sm:w-auto bg-primaryBrown hover:bg-mutedBrown text-white px-5 py-2.5 rounded-xl shadow-[0_4px_12px_rgba(146,104,87,0.25)] hover:shadow-[0_6px_16px_rgba(146,104,87,0.35)] transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 font-bold text-sm">
            <i class="fa-solid fa-plus"></i>
            <span data-ar="إضافة معيار جديد" data-en="Add New Standard">إضافة معيار جديد</span>
        </button>
        @else
        <a href="{{ route('settings.order-item-stander.index') }}" class="w-full sm:w-auto bg-secondaryCream hover:bg-softBeige text-darkText px-5 py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 font-bold text-sm">
            <i class="fa-solid fa-arrow-right rtl:rotate-180"></i>
            <span data-ar="العودة للقائمة" data-en="Back to List">العودة للقائمة</span>
        </a>
        @endif
    </div>

    {{-- فورم الإضافة / التعديل --}}
    <div id="standerFormCard" class="{{ isset($orderItemStander) ? '' : 'hidden' }} bg-white rounded-2xl shadow-sm border border-secondaryCream p-5 sm:p-7 mb-8 transition-all duration-300">
        <h3 class="text-lg font-bold text-primaryBrown mb-5 flex items-center gap-2">
            <i class="fa-solid {{ isset($orderItemStander) ? 'fa-pen-to-square' : 'fa-wand-magic-sparkles' }}"></i>
            {{ isset($orderItemStander) ? 'تعديل المعيار #' . $orderItemStander->id : 'إضافة معيار جديد' }}
        </h3>

        <form action="{{ isset($orderItemStander) ? route('settings.order-item-stander.update', $orderItemStander->id) : route('settings.order-item-stander.store') }}" method="POST">
            @csrf
            @if(isset($orderItemStander))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                {{-- نوع البنطلون --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">نوع البنطلون</label>
                    <select name="pant_type_id" class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all hover:bg-primaryBg">
                        <option value="">بدون اختيار</option>
                        @foreach($pantTypes as $pt)
                            <option value="{{ $pt->id }}" {{ (isset($orderItemStander) && $orderItemStander->pant_type_id == $pt->id) ? 'selected' : '' }}>{{ $pt->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- النوع --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">النوع</label>
                    <select name="type_id" class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all hover:bg-primaryBg">
                        <option value="">بدون اختيار</option>
                        @foreach($types as $t)
                            <option value="{{ $t->id }}" {{ (isset($orderItemStander) && $orderItemStander->type_id == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- الستايل --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">الستايل</label>
                    <select name="style_id" class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all hover:bg-primaryBg">
                        <option value="">بدون اختيار</option>
                        @foreach($styles as $st)
                            <option value="{{ $st->id }}" {{ (isset($orderItemStander) && $orderItemStander->style_id == $st->id) ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- اللون --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">اللون</label>
                    <select name="color_id" class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all hover:bg-primaryBg">
                        <option value="">بدون اختيار</option>
                        @foreach($colors as $c)
                            <option value="{{ $c->id }}" {{ (isset($orderItemStander) && $orderItemStander->color_id == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- المقاس --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">المقاس</label>
                    <select name="size_id" class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all hover:bg-primaryBg">
                        <option value="">بدون اختيار</option>
                        @foreach($sizes as $sz)
                            <option value="{{ $sz->id }}" {{ (isset($orderItemStander) && $orderItemStander->size_id == $sz->id) ? 'selected' : '' }}>{{ $sz->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- نوع الكم --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">نوع الكم</label>
                    <select name="sleeve_type_id" class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all hover:bg-primaryBg">
                        <option value="">بدون اختيار</option>
                        @foreach($sleeveTypes as $slv)
                            <option value="{{ $slv->id }}" {{ (isset($orderItemStander) && $orderItemStander->sleeve_type_id == $slv->id) ? 'selected' : '' }}>{{ $slv->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- السعر --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">السعر (إلزامي)</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $orderItemStander->price ?? '') }}" required class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl pl-10 pr-4 py-2.5 text-sm font-bold focus:outline-none focus:border-roseAccent focus:ring-1 focus:ring-roseAccent transition-all hover:bg-primaryBg">
                        <i class="fa-solid fa-money-bill-wave absolute left-4 top-1/2 transform -translate-y-1/2 text-roseAccent"></i>
                    </div>
                    @error('price') <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                </div>

                {{-- تفعيل --}}
                <div class="flex items-center pt-2 sm:pt-7">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ (isset($orderItemStander) ? $orderItemStander->is_active : true) ? 'checked' : '' }}>
                        <div class="w-12 h-6 bg-secondaryCream peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primaryBrown group-hover:shadow-md transition-shadow"></div>
                        <span class="ml-3 rtl:mr-3 text-sm font-bold text-darkText group-hover:text-primaryBrown transition-colors">مُفعل ونشط</span>
                    </label>
                </div>

                {{-- الملاحظات --}}
                <div class="md:col-span-2 lg:col-span-3 space-y-1.5">
                    <label class="block text-sm font-bold text-darkText">ملاحظات إضافية</label>
                    <textarea name="notes" rows="2" class="w-full bg-primaryBg/50 border border-secondaryCream rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primaryBrown focus:ring-1 focus:ring-primaryBrown transition-all hover:bg-primaryBg">{{ old('notes', $orderItemStander->notes ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-secondaryCream flex flex-col-reverse sm:flex-row justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-mutedBrown bg-primaryBg hover:bg-secondaryCream rounded-xl transition-colors">
                    <i class="fa-solid fa-rotate-left mr-1"></i> إعادة ضبط
                </button>
                <button type="submit" class="w-full sm:w-auto px-8 py-2.5 text-sm font-bold text-white bg-primaryBrown hover:bg-mutedBrown rounded-xl shadow-md transition-transform transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> {{ isset($orderItemStander) ? 'تحديث البيانات' : 'حفظ المعيار' }}
                </button>
            </div>
        </form>
    </div>

    {{-- قائمة المعايير المدخلة --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(202,188,176,0.15)] border border-secondaryCream overflow-hidden">
        
        {{-- عرض الجدول للشاشات الكبيرة (Desktop) --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-sm text-right rtl:text-right text-darkText whitespace-nowrap">
                <thead class="text-xs text-mutedBrown uppercase bg-primaryBg/60 border-b border-secondaryCream">
                    <tr>
                        <th scope="col" class="px-5 py-4 font-extrabold rounded-tr-xl">#</th>
                        <th scope="col" class="px-5 py-4 font-extrabold">المواصفات (نوع / ستايل / الخ)</th>
                        <th scope="col" class="px-5 py-4 font-extrabold">المقاس واللون</th>
                        <th scope="col" class="px-5 py-4 font-extrabold">السعر</th>
                        <th scope="col" class="px-5 py-4 font-extrabold text-center">الحالة</th>
                        <th scope="col" class="px-5 py-4 font-extrabold text-center rounded-tl-xl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondaryCream/50">
                    @forelse($orderItemStanders as $item)
                    <tr class="hover:bg-primaryBg/30 transition-colors group">
                        <td class="px-5 py-4 font-bold text-primaryBrown">#{{ $item->id }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @if($item->pantType) <span class="bg-white border border-secondaryCream text-mutedBrown px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm">{{ $item->pantType->name }}</span> @endif
                                @if($item->type) <span class="bg-white border border-secondaryCream text-mutedBrown px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm">{{ $item->type->name }}</span> @endif
                                @if($item->style) <span class="bg-white border border-secondaryCream text-mutedBrown px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm">{{ $item->style->name }}</span> @endif
                                @if($item->sleeveType) <span class="bg-white border border-secondaryCream text-mutedBrown px-2.5 py-1 rounded-lg text-xs font-semibold shadow-sm">{{ $item->sleeveType->name }}</span> @endif
                                @if(!$item->pantType && !$item->type && !$item->style && !$item->sleeveType) 
                                    <span class="text-xs text-softBeige font-semibold">لم يحدد المواصفات</span> 
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                @if($item->size) 
                                    <span class="font-extrabold border-2 border-secondaryCream bg-primaryBg/50 px-2 py-0.5 rounded-md text-xs text-darkText">{{ $item->size->name }}</span> 
                                @endif
                                @if($item->color)
                                    <span class="flex items-center gap-1.5 text-xs font-bold bg-white border border-secondaryCream px-2 py-1 rounded-md shadow-sm">
                                        <span class="w-3.5 h-3.5 rounded-full shadow-inner border border-gray-200" style="background-color: {{ $item->color->hex ?? '#ccc' }};"></span>
                                        {{ $item->color->name }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 font-black text-roseAccent text-base">{{ number_format($item->price, 2) }} <span class="text-xs font-bold">ج.م</span></td>
                        <td class="px-5 py-4 text-center">
                            <form action="{{ route('settings.order-item-stander.toggle-status', $item->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="relative inline-flex items-center cursor-pointer group" title="تغيير الحالة">
                                    <div class="w-10 h-5 bg-secondaryCream peer-focus:outline-none rounded-full transition-colors {{ $item->is_active ? 'bg-primaryBrown' : '' }}">
                                        <div class="absolute top-[2px] left-[2px] bg-white border border-gray-300 rounded-full h-4 w-4 transition-all {{ $item->is_active ? 'translate-x-[20px] border-white' : '' }}"></div>
                                    </div>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2 opacity-100 lg:opacity-60 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('settings.order-item-stander.show', $item->id) }}" class="w-8 h-8 rounded-xl bg-softBeige/40 text-primaryBrown flex items-center justify-center hover:bg-primaryBrown hover:text-white transition-all shadow-sm hover:shadow-md" title="تعديل">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </a>
                                <form action="{{ route('settings.order-item-stander.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المعيار بشكل نهائي؟');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm hover:shadow-md" title="حذف">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-mutedBrown">
                            <i class="fa-solid fa-box-open text-4xl mb-3 text-secondaryCream block"></i>
                            <span class="font-bold text-base">لا توجد معايير مضافة حتى الآن.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- عرض البطاقات للشاشات الصغيرة (Mobile / Tablet) --}}
        <div class="lg:hidden grid grid-cols-1 gap-4 p-3 sm:p-4 bg-primaryBg/20">
            @forelse($orderItemStanders as $item)
            <div class="bg-white rounded-2xl shadow-sm border border-secondaryCream p-4 relative transition-transform hover:-translate-y-1">
                {{-- رأس البطاقة --}}
                <div class="flex justify-between items-start mb-4 border-b border-secondaryCream/50 pb-3">
                    <span class="font-black text-primaryBrown text-lg bg-softBeige/20 px-2 py-0.5 rounded-lg">#{{ $item->id }}</span>
                    <div class="flex gap-2">
                        <a href="{{ route('settings.order-item-stander.show', $item->id) }}" class="w-8 h-8 rounded-xl bg-softBeige/40 text-primaryBrown flex items-center justify-center hover:bg-primaryBrown hover:text-white transition-all">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </a>
                        <form action="{{ route('settings.order-item-stander.destroy', $item->id) }}" method="POST" onsubmit="return confirm('تأكيد الحذف؟');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- تفاصيل البطاقة --}}
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center bg-primaryBg/40 p-2 rounded-xl">
                        <div class="text-mutedBrown text-xs font-bold">السعر</div>
                        <div class="font-black text-roseAccent text-lg">{{ number_format($item->price, 2) }} ج.م</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-white border border-secondaryCream p-2 rounded-xl text-center shadow-sm">
                            <div class="text-[10px] text-mutedBrown mb-1">المقاس / اللون</div>
                            <div class="font-bold text-darkText flex items-center justify-center gap-1">
                                {{ $item->size->name ?? '-' }} 
                                @if($item->color)
                                    / <span class="w-2.5 h-2.5 rounded-full inline-block border border-gray-200" style="background-color: {{ $item->color->hex ?? '#ccc' }};"></span>
                                @endif
                            </div>
                        </div>
                        <div class="bg-white border border-secondaryCream p-2 rounded-xl text-center shadow-sm">
                            <div class="text-[10px] text-mutedBrown mb-1">النوع / الستايل</div>
                            <div class="font-bold text-darkText text-xs truncate">
                                {{ $item->type->name ?? 'بدون' }} / {{ $item->style->name ?? 'بدون' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- حالة التفعيل في الموبايل --}}
                <div class="pt-3 border-t border-secondaryCream/50 flex justify-between items-center">
                    <span class="text-xs font-bold text-mutedBrown">حالة التفعيل (نشط؟)</span>
                    <form action="{{ route('settings.order-item-stander.toggle-status', $item->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="relative inline-flex items-center cursor-pointer">
                            <div class="w-12 h-6 bg-secondaryCream peer-focus:outline-none rounded-full transition-colors {{ $item->is_active ? 'bg-primaryBrown' : '' }}">
                                <div class="absolute top-[2px] left-[2px] bg-white border border-gray-300 rounded-full h-5 w-5 transition-all shadow-sm {{ $item->is_active ? 'translate-x-[24px] border-white' : '' }}"></div>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center text-mutedBrown py-10 bg-white rounded-2xl border border-secondaryCream">
                <i class="fa-solid fa-box-open text-4xl mb-3 text-secondaryCream block"></i>
                <span class="font-bold text-sm">لا توجد بيانات للعرض</span>
            </div>
            @endforelse
        </div>

        {{-- الترقيم (Pagination) --}}
        @if($orderItemStanders->hasPages())
        <div class="p-4 border-t border-secondaryCream bg-white flex justify-center">
            {{ $orderItemStanders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection