@extends('layouts.app')

@section('title', 'أسعار ارتجاع الشحن')
@section('page_title', 'أسعار ارتجاع الشحن (Turbo)')

@section('content')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div dir="rtl" class="space-y-6 pb-12 font-sans" x-data="turboReturnApp()">
        @if (session('success'))
            <div class="bg-emerald-50 border-r-4 border-emerald-500 p-4 rounded-xl flex items-center justify-between shadow-sm mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <i class="fa-solid fa-circle-check text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-emerald-900 text-sm">عملية ناجحة</h4>
                        <p class="text-emerald-700 text-xs mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-50 border-r-4 border-rose-500 p-4 rounded-xl flex items-center justify-between shadow-sm mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-rose-900 text-sm">حدث خطأ</h4>
                        <ul class="text-rose-700 text-xs mt-0.5 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-money-bill-transfer text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">إدارة تكلفة الارتجاع</h2>
                    <p class="text-xs text-gray-400">تحديد تكلفة الارتجاع التلقائية لكل محافظة ومنطقة في نظام شحن Turbo</p>
                </div>
            </div>

            <button type="button" @click="openCreateModal()" class="px-4 py-2 bg-gradient-to-r from-amber-600 to-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm hover:shadow-md flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                إضافة تكلفة جديدة
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                            <th class="py-4 px-6">المحافظة</th>
                            <th class="py-4 px-6">المنطقة</th>
                            <th class="py-4 px-6">تكلفة الارتجاع</th>
                            <th class="py-4 px-6 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($amounts as $amount)
                            <tr class="hover:bg-amber-50/30 transition duration-150">
                                <td class="py-4 px-6 font-bold text-gray-900">{{ $amount->government_name }}</td>
                                <td class="py-4 px-6 text-gray-600">{{ $amount->area_name ?: 'جميع المناطق' }}</td>
                                <td class="py-4 px-6 font-bold text-amber-700">{{ number_format($amount->return_amount, 2) }} ج.م</td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal({{ json_encode($amount) }})" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-800 transition flex items-center justify-center">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                        <form action="{{ route('turbo-return-amounts.destroy', $amount->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه التكلفة؟');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 transition flex items-center justify-center">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400">لا توجد تكاليف ارتجاع مضافة. الرجاء إضافة تكلفة جديدة لتفعيل النظام التلقائي.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Form Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
            <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800" x-text="isEditing ? 'تعديل التكلفة' : 'إضافة تكلفة جديدة'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition"><i class="fa-solid fa-xmark"></i></button>
                </div>
                
                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">المحافظة <span class="text-rose-500">*</span></label>
                            <select name="government_name" x-model="form.government_name" @change="fetchAreas" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                <option value="">-- اختر المحافظة --</option>
                                @foreach($governments as $gov)
                                    <option value="{{ $gov['name'] }}" data-id="{{ $gov['id'] }}">{{ $gov['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">المنطقة (اختياري)</label>
                            <select name="area_name" x-model="form.area_name" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                <option value="">-- جميع المناطق --</option>
                                <template x-for="area in areas" :key="area.id">
                                    <option :value="area.name" x-text="area.name"></option>
                                </template>
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">اتركها فارغة لتطبيق التكلفة على المحافظة بأكملها</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">تكلفة الارتجاع (ج.م) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="return_amount" x-model="form.return_amount" required class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition hover:bg-gray-300">إلغاء</button>
                        <button type="submit" class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-xl transition hover:bg-amber-700" x-text="isEditing ? 'حفظ التعديلات' : 'إضافة'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function turboReturnApp() {
            return {
                showModal: false,
                isEditing: false,
                formAction: '{{ route('turbo-return-amounts.store') }}',
                areas: [],
                form: {
                    id: null,
                    government_name: '',
                    area_name: '',
                    return_amount: ''
                },
                
                openCreateModal() {
                    this.isEditing = false;
                    this.formAction = '{{ route('turbo-return-amounts.store') }}';
                    this.form = { id: null, government_name: '', area_name: '', return_amount: '' };
                    this.areas = [];
                    this.showModal = true;
                },
                
                openEditModal(amount) {
                    this.isEditing = true;
                    this.formAction = '{{ url('turbo-return-amounts') }}/' + amount.id;
                    this.form = { ...amount };
                    
                    // Trigger area fetch if government is selected
                    this.fetchAreas();
                    
                    this.showModal = true;
                },
                
                fetchAreas() {
                    if (!this.form.government_name) {
                        this.areas = [];
                        return;
                    }
                    
                    // Find gov ID from the select element options
                    const select = document.querySelector('select[name="government_name"]');
                    const option = Array.from(select.options).find(opt => opt.value === this.form.government_name);
                    
                    if (option && option.dataset.id) {
                        fetch(`{{ url('turbo-return-amounts/api/areas') }}/${option.dataset.id}`)
                            .then(res => res.json())
                            .then(data => {
                                this.areas = data;
                            })
                            .catch(err => console.error(err));
                    }
                }
            };
        }
    </script>
@endsection
