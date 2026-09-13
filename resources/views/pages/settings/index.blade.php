@extends('layouts.app')

@section('content')
    <div dir="rtl" class="space-y-6 pb-12 font-sans">
        <div
            class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="w-10 h-10 rounded-xl bg-gray-50 text-gray-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-gear text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">الإعدادات</h2>
                    <p class="text-xs text-gray-400">إدارة إعدادات النظام</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @if (auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ route('turbo-return-amounts.index') }}"
                    class="block bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                    <div
                        class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center text-amber-600 mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-money-bill-transfer text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2">أسعار ارتجاع الشحن (Turbo)</h3>
                    <p class="text-xs text-gray-500">تحديد التكلفة التلقائية للارتجاع لكل محافظة ومنطقة في نظام الشحن.</p>
                </a>
            <a href="{{ route('settings.order-item-stander.index') }}"
    class="block bg-white p-6 rounded-2xl border border-secondaryCream/80 shadow-sm hover:shadow-xl hover:border-roseAccent/50 transition-all duration-300 group relative overflow-hidden">
    
    {{-- تأثير خلفية خفيف عند الهوفر --}}
    <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-roseAccent/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>

    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-softBeige/30 to-roseAccent/20 flex items-center justify-center text-primaryBrown mb-4 group-hover:scale-110 group-hover:bg-primaryBrown group-hover:text-white transition-all duration-300 shadow-sm">
        <i class="fa-solid fa-ruler-combined text-2xl"></i>
    </div>
    
    <h3 class="font-bold text-darkText text-lg mb-2 group-hover:text-primaryBrown transition-colors">معايير عناصر الطلب (Standards)</h3>
    <p class="text-xs text-mutedBrown/90 leading-relaxed">تخصيص أسعار ومواصفات عناصر التصنيع (نوع البنطلون، الستايل، اللون، المقاس، والكم) للطلبات.</p>
    
    <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-primaryBrown group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform">
        <span>استعراض المعايير</span>
        <i class="fa-solid fa-arrow-left rtl:rotate-180 text-[10px]"></i>
    </div>
</a>
            @endif
        </div>
    </div>
@endsection
