@extends('layouts.app')

@section('title', 'تصنيفات الطلبات')
@section('page_title', 'تصنيفات الطلبات')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" dir="rtl">

        @if (session('success'))
            <div
                class="mb-6 p-4 bg-green-50 border-r-4 border-green-500 text-green-700 rounded-l-xl shadow-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">خصائص المنتجات</h2>
            <p class="text-base text-gray-500">إدارة الألوان، المقاسات، الأنماط، الأنواع، أنواع البنطلونات، وأنواع الأكمام
                الخاصة بنظام Tagmedix.</p>
        </div>

        {{-- شريط التبويبات (Tabs) المعدل ليشمل 6 عناصر بدلاً من 5 --}}
        <div
            class="grid grid-cols-2 md:flex md:flex-row gap-3 mb-8 bg-white p-3 rounded-2xl shadow-sm border border-gray-100">
            <a href="{{ route('order_classifications.index', ['tab' => 'colors']) }}"
                class="modern-card flex-1 text-center py-3 px-6 rounded-xl text-base font-semibold transition-all duration-300 flex items-center justify-center gap-2 {{ $activeTab === 'colors' ? 'bg-[#8A704C] text-white shadow-md transform scale-[1.02]' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fa-solid fa-palette"></i> الألوان
            </a>

            <a href="{{ route('order_classifications.index', ['tab' => 'sizes']) }}"
                class="modern-card flex-1 text-center py-3 px-6 rounded-xl text-base font-semibold transition-all duration-300 flex items-center justify-center gap-2 {{ $activeTab === 'sizes' ? 'bg-[#8A704C] text-white shadow-md transform scale-[1.02]' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fa-solid fa-maximize"></i> المقاسات
            </a>

            <a href="{{ route('order_classifications.index', ['tab' => 'styles']) }}"
                class="modern-card flex-1 text-center py-3 px-6 rounded-xl text-base font-semibold transition-all duration-300 flex items-center justify-center gap-2 {{ $activeTab === 'styles' ? 'bg-[#8A704C] text-white shadow-md transform scale-[1.02]' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fa-solid fa-shirt"></i> الأنماط
            </a>

            <a href="{{ route('order_classifications.index', ['tab' => 'types']) }}"
                class="modern-card flex-1 text-center py-3 px-6 rounded-xl text-base font-semibold transition-all duration-300 flex items-center justify-center gap-2 {{ $activeTab === 'types' ? 'bg-[#8A704C] text-white shadow-md transform scale-[1.02]' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fa-solid fa-layer-group"></i> الأنواع
            </a>

            {{-- التبويب الخاص بأنواع البنطلونات --}}
            <a href="{{ route('order_classifications.index', ['tab' => 'pant_types']) }}"
                class="modern-card flex-1 text-center py-3 px-6 rounded-xl text-base font-semibold transition-all duration-300 flex items-center justify-center gap-2 {{ $activeTab === 'pant_types' ? 'bg-[#8A704C] text-white shadow-md transform scale-[1.02]' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fa-solid fa-tags"></i> أنواع البنطلونات
            </a>

            {{-- التبويب الجديد الخاص بأنواع الأكمام --}}
            <a href="{{ route('order_classifications.index', ['tab' => 'sleeve_types']) }}"
                class="modern-card flex-1 text-center py-3 px-6 rounded-xl text-base font-semibold transition-all duration-300 flex items-center justify-center gap-2 {{ $activeTab === 'sleeve_types' ? 'bg-[#8A704C] text-white shadow-md transform scale-[1.02]' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <i class="fa-solid fa-ruler-horizontal"></i> أنواع الأكمام
            </a>
        </div>

        {{-- عرض الكرت بناءً على التبويب النشط --}}
        <div class="transition-all duration-300">
            @if ($activeTab === 'colors')
                @include('pages.order_classifications.components.classification_card', [
                    'type' => 'colors',
                    'title' => 'لون',
                    'icon' => 'fa-palette',
                    'items' => $colors,
                ])
            @elseif ($activeTab === 'sizes')
                @include('pages.order_classifications.components.classification_card', [
                    'type' => 'sizes',
                    'title' => 'مقاس',
                    'icon' => 'fa-maximize',
                    'items' => $sizes,
                ])
            @elseif ($activeTab === 'styles')
                @include('pages.order_classifications.components.classification_card', [
                    'type' => 'styles',
                    'title' => 'نمط',
                    'icon' => 'fa-shirt',
                    'items' => $styles,
                ])
            @elseif ($activeTab === 'types')
                @include('pages.order_classifications.components.classification_card', [
                    'type' => 'types',
                    'title' => 'نوع',
                    'icon' => 'fa-layer-group',
                    'items' => $types,
                ])
            @elseif ($activeTab === 'pant_types')
                @include('pages.order_classifications.components.classification_card', [
                    'type' => 'pant_types',
                    'title' => 'نوع بنطلون',
                    'icon' => 'fa-tags',
                    'items' => $pantTypes,
                ])
            @elseif ($activeTab === 'sleeve_types')
                @include('pages.order_classifications.components.classification_card', [
                    'type' => 'sleeve_types',
                    'title' => 'نوع كم',
                    'icon' => 'fa-ruler-horizontal',
                    'items' => $sleeveTypes,
                ])
            @endif
        </div>
    </div>
@endsection
