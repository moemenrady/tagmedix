<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-4 sm:p-8">
        
        <form action="{{ route($type . '.store') }}" method="POST" class="bg-gray-50 p-6 rounded-xl border border-gray-100 mb-8">
            @csrf
            <h3 class="text-lg font-bold text-gray-700 mb-4"><i class="fa-solid fa-circle-plus text-[#8A704C] ml-2"></i> إضافة {{ $title }} جديد</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                <div class="lg:col-span-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">الاسم <span class="text-red-500">*</span></label>
                    <div class="relative flex items-center">
                        <span class="absolute right-4 text-[#8A704C]">
                            <i class="fa {{ $icon }}"></i>
                        </span>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full pr-11 pl-4 py-3.5 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:border-transparent transition-all text-base shadow-sm @error('name') border-red-500 @enderror" 
                            placeholder="مثال: أحمر، XL، كاجوال">
                    </div>
                    @error('name') <span class="text-sm text-red-500 mt-2 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="lg:col-span-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">المعرف (الكود) <span class="text-red-500">*</span></label>
                    <div class="relative flex items-center">
                        <span class="absolute right-4 text-gray-400">
                            <i class="fa fa-hashtag"></i>
                        </span>
                        <input type="text" name="identifier" value="{{ old('identifier') }}" required
                            class="w-full pr-11 pl-4 py-3.5 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#8A704C] focus:border-transparent transition-all text-base shadow-sm font-mono @error('identifier') border-red-500 @enderror" 
                            placeholder="مثال: col_red, sz_xl">
                    </div>
                    @error('identifier') <span class="text-sm text-red-500 mt-2 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="lg:col-span-4 pt-7">
                    <button type="submit" class="w-full bg-[#8A704C] hover:bg-[#725c3e] text-white font-bold py-3.5 px-6 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-3 text-lg transform hover:-translate-y-0.5">
                        <i class="fa fa-plus-circle text-xl"></i> حفظ وإضافة
                    </button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
            <table class="w-full text-right border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-sidebar text-white text-sm font-bold tracking-wide">
                        <th class="p-4 w-16 text-center">#</th>
                        <th class="p-4">الاسم</th>
                        <th class="p-4">المعرف</th>
                        <th class="p-4 text-center w-40">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-base text-gray-700 bg-white">
                    @forelse($items as $index => $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 text-center font-bold text-gray-500">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold text-gray-900">{{ $item->name }}</td>
                            <td class="p-4 text-left" dir="ltr">
                                <span class="px-3 py-1.5 bg-gray-100 border border-gray-200 text-gray-700 rounded-lg text-sm font-mono shadow-sm">
                                    {{ $item->identifier }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openEditModal({{ $item->id }}, '{{ $item->name }}', '{{ $item->identifier }}', '{{ $type }}')" class="modern-card text-blue-600 hover:text-white hover:bg-blue-600 p-2.5 rounded-lg transition-all border border-blue-100 shadow-sm" title="تعديل">
                                        <i class="fa-solid fa-pen text-sm"></i>
                                    </button>
                                    
                                    <form action="{{ route($type . '.destroy', $item->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في الحذف؟');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="modern-card text-red-600 hover:text-white hover:bg-red-600 p-2.5 rounded-lg transition-all border border-red-100 shadow-sm" title="حذف">
                                            <i class="fa-regular fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center text-gray-400 bg-gray-50">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <i class="fa-solid fa-box-open text-4xl text-gray-300"></i>
                                    <span class="text-lg font-medium">لم يتم إضافة أي {{ $title }} بعد.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center" dir="rtl">
    <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white dropdown-enter-active">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">تعديل البيانات</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">الاسم</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#8A704C] focus:outline-none text-base">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">المعرف (الكود)</label>
                <input type="text" id="edit_identifier" name="identifier" required class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#8A704C] focus:outline-none text-base font-mono text-left" dir="ltr">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-[#8A704C] hover:bg-[#725c3e] text-white font-bold py-3 px-4 rounded-xl shadow-md transition-all">
                    حفظ التعديلات
                </button>
                <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-xl transition-all">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // سكربت بسيط للتعامل مع الـ Modal الخاص بالتعديل
    function openEditModal(id, name, identifier, type) {
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_identifier').value = identifier;
        
        // تحديث رابط الـ Form ليتم الإرسال للـ Route الصحيح
        // ستحتاج للتأكد من أن الـ Routes الخاصة بك تستقبل الـ ID في المسار مثلا: /colors/{color}
        document.getElementById('editForm').action = `order-classifications/${type}/${id}`; 
        
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>