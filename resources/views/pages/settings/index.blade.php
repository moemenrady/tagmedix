@extends('layouts.app')

@section('title', 'الإعدادات')
@section('page_title', 'إدارة إعدادات النظام')

@section('content')
    <div dir="rtl" class="space-y-6 pb-12 font-sans">
        @if (session('success'))
            <div class="p-4 bg-green-50 border-r-4 border-green-500 text-green-700 rounded-xl shadow-sm flex items-center gap-3 animate-fade-in">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="w-10 h-10 rounded-xl bg-gray-50 text-[#8A704C] flex items-center justify-center shrink-0 shadow-inner">
                    <i class="fa-solid fa-gear text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">الإعدادات العامة</h2>
                    <p class="text-xs text-gray-400">إدارة إعدادات النظام ومواعيد التسليم التلقائية والأسعار</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @if (auth()->check() && auth()->user()->role === 'admin')
                {{-- كرت مدة تسليم الطلبات التلقائية --}}
                <div class="block bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center text-blue-600 mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-check text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2">مدة تسليم الطلبات (تلقائي)</h3>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        تحديد المدة التلقائية لحساب تاريخ التسليم عند إضافة أي طلب، وفارق وقت الطلب المستعجل.
                    </p>

                    <div class="space-y-2 mb-5 bg-gray-50 p-3.5 rounded-xl border border-gray-100 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 font-medium">الطلب العادي:</span>
                            <span class="font-black text-gray-800 bg-white px-2.5 py-1 rounded-lg border border-gray-200">
                                {{ $defaultDeliveryDays }} أيام
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-red-600 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-bolt text-red-500"></i> الطلب المستعجل:
                            </span>
                            <span class="font-black text-red-600 bg-red-50 px-2.5 py-1 rounded-lg border border-red-200">
                                {{ $urgentDeliveryDays }} أيام
                            </span>
                        </div>
                    </div>

                    <button type="button" onclick="openDeliverySettingsModal()"
                        class="w-full bg-[#8A704C] hover:bg-[#725c3e] text-white py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa-solid fa-pen-to-square"></i> تعديل مدة التسليم
                    </button>
                </div>

                {{-- كرت أسعار ارتجاع الشحن --}}
                <a href="{{ route('turbo-return-amounts.index') }}"
                    class="block bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center text-amber-600 mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-money-bill-transfer text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-2">أسعار ارتجاع الشحن (Turbo)</h3>
                    <p class="text-xs text-gray-500">تحديد التكلفة التلقائية للارتجاع لكل محافظة ومنطقة في نظام الشحن.</p>
                </a>

                {{-- كرت معايير عناصر الطلب --}}
                <a href="{{ route('settings.order-item-stander.index') }}"
                    class="block bg-white p-6 rounded-2xl border border-secondaryCream/80 shadow-sm hover:shadow-xl hover:border-roseAccent/50 transition-all duration-300 group relative overflow-hidden">
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

    {{-- Modal تعديل مدة تسليم الطلبات --}}
    <div id="deliverySettingsModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center p-4" dir="rtl">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white dropdown-enter-active">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-[#8A704C]/10 text-[#8A704C] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-stopwatch"></i>
                    </span>
                    <h3 class="text-lg font-bold text-gray-900">إعداد مدة تسليم الطلبات التلقائية</h3>
                </div>
                <button onclick="closeDeliverySettingsModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form action="{{ route('settings.delivery-duration.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        مدة تسليم الطلب العادي (بالأيام) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="default_delivery_days" id="default_delivery_days_input"
                            value="{{ $defaultDeliveryDays }}" required min="1" max="365"
                            class="w-full h-11 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#8A704C] focus:bg-white text-sm font-bold text-gray-800 font-mono text-left" dir="ltr">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">أيام</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">يُحسب تاريخ التسليم تلقائياً = تاريخ اليوم + هذا العدد من الأيام</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-red-600 mb-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-bolt text-red-500"></i> مدة تسليم الطلب المستعجل (بالأيام) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="urgent_delivery_days" id="urgent_delivery_days_input"
                            value="{{ $urgentDeliveryDays }}" required min="1" max="365"
                            class="w-full h-11 px-4 bg-red-50/50 border border-red-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:bg-white text-sm font-bold text-red-600 font-mono text-left" dir="ltr">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-red-400 pointer-events-none">أيام</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">يتم تطبيقه فور تفعيل خيار "مستعجل" في صفحة إضافة الطلب</p>
                </div>

                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <button type="submit" class="flex-1 bg-[#8A704C] hover:bg-[#725c3e] text-white font-bold py-2.5 px-4 rounded-xl shadow-md transition-all text-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> حفظ الإعدادات
                    </button>
                    <button type="button" onclick="closeDeliverySettingsModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 px-4 rounded-xl transition-all text-sm">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeliverySettingsModal() {
            document.getElementById('deliverySettingsModal').classList.remove('hidden');
        }
        function closeDeliverySettingsModal() {
            document.getElementById('deliverySettingsModal').classList.add('hidden');
        }
    </script>
@endsection
