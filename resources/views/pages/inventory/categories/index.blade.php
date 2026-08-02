@extends('layouts.app')

@section('title', 'تصنيفات الأصناف - Item Categories')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-tags text-activeGold"></i>
                <span data-ar="تصنيفات الأصناف" data-en="Item Categories">تصنيفات الأصناف</span>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                <a href="{{ route('inventory.index') }}" class="hover:text-activeGold transition">
                    <span data-ar="المخزون" data-en="Inventory">المخزون</span>
                </a>
                <i class="fa-solid fa-chevron-left text-[10px] mx-1 rtl:inline ltr:hidden"></i>
                <i class="fa-solid fa-chevron-right text-[10px] mx-1 hidden ltr:inline"></i>
                <span data-ar="إدارة التصنيفات" data-en="Manage Categories">إدارة التصنيفات</span>
            </p>
        </div>

        <button onclick="openCategoryModal()"
            class="bg-activeGold hover:bg-[#725c3d] text-white px-5 py-2.5 rounded-lg shadow-md transition flex items-center gap-2 font-medium text-sm w-full sm:w-auto justify-center">
            <i class="fa-solid fa-plus"></i>
            <span data-ar="إضافة تصنيف جديد" data-en="Add New Category">إضافة تصنيف جديد</span>
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4">#</th>
                        <th scope="col" class="px-6 py-4">
                            <span data-ar="اسم التصنيف" data-en="Category Name">اسم التصنيف</span>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            <span data-ar="الوصف" data-en="Description">الوصف</span>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            <span data-ar="الحالة" data-en="Status">الحالة</span>
                        </th>
                        <th scope="col" class="px-6 py-4 text-center">
                            <span data-ar="الإجراءات" data-en="Actions">الإجراءات</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody" class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                        <tr id="category-row-{{ $category->id }}" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-bold text-activeGold">{{ $category->name }}</td>
                            <td class="px-6 py-4 text-gray-500 max-w-xs truncate" title="{{ $category->description }}">
                                {{ $category->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($category->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                                        <span data-ar="نشط" data-en="Active">نشط</span>
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                        <span data-ar="غير نشط" data-en="Inactive">غير نشط</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 flex justify-center items-center gap-3">
                                <button onclick="editCategory({{ $category->id }})"
                                    class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 p-2 rounded-lg transition"
                                    title="تعديل">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <button onclick="confirmDelete({{ $category->id }})"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition"
                                    title="حذف">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-state-row">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <i class="fa-solid fa-folder-open text-4xl text-gray-300"></i>
                                    <p class="text-base font-medium">
                                        <span data-ar="لا توجد تصنيفات مضافة حتى الآن" data-en="No categories added yet">لا
                                            توجد تصنيفات مضافة حتى الآن</span>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="categoryModal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center items-center p-4 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 transition-transform duration-300"
            id="categoryModalContent">
            <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800" id="modalTitle">
                    <span data-ar="إضافة تصنيف" data-en="Add Category">إضافة تصنيف</span>
                </h3>
                <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-red-500 transition">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form id="categoryForm" onsubmit="submitCategoryForm(event)" class="p-5 space-y-4">
                <input type="hidden" id="categoryId" name="id">
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        <span data-ar="اسم التصنيف *" data-en="Category Name *">اسم التصنيف *</span>
                    </label>
                    <input type="text" id="name" name="name" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-activeGold/50 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800">
                    <span class="text-xs text-red-500 hidden mt-1" id="error-name"></span>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        <span data-ar="الوصف (اختياري)" data-en="Description (Optional)">الوصف (اختياري)</span>
                    </label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-activeGold/50 focus:border-activeGold transition bg-gray-50 focus:bg-white text-gray-800 resize-none"></textarea>
                    <span class="text-xs text-red-500 hidden mt-1" id="error-description"></span>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="is_active" name="is_active" class="sr-only peer" checked
                            value="1">
                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] rtl:after:right-[2px] rtl:after:left-auto after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-activeGold">
                        </div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">
                        <span data-ar="نشط" data-en="Active">نشط</span>
                    </span>
                </div>

                <div class="pt-4 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" onclick="closeCategoryModal()"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                        <span data-ar="إلغاء" data-en="Cancel">إلغاء</span>
                    </button>
                    <button type="submit" id="submitBtn"
                        class="px-6 py-2 rounded-lg text-sm font-medium text-white bg-activeGold hover:bg-[#725c3d] shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span data-ar="حفظ البيانات" data-en="Save Data">حفظ البيانات</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal"
        class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center items-center p-4 transition-opacity duration-300 opacity-0">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm p-6 text-center transform scale-95 transition-transform duration-300"
            id="deleteModalContent">
            <div class="w-16 h-16 rounded-full bg-red-100 text-red-500 mx-auto flex items-center justify-center mb-4">
                <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">
                <span data-ar="تأكيد الحذف" data-en="Confirm Delete">تأكيد الحذف</span>
            </h3>
            <p class="text-gray-500 text-sm mb-6">
                <span data-ar="هل أنت متأكد من حذف هذا التصنيف؟ لا يمكن التراجع عن هذا الإجراء."
                    data-en="Are you sure you want to delete this category? This action cannot be undone.">هل أنت متأكد من
                    حذف هذا التصنيف؟ لا يمكن التراجع عن هذا الإجراء.</span>
            </p>

            <input type="hidden" id="deleteCategoryId">

            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition w-full">
                    <span data-ar="إلغاء" data-en="Cancel">إلغاء</span>
                </button>
                <button onclick="executeDelete()" id="confirmDeleteBtn"
                    class="px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-red-500 hover:bg-red-600 shadow-md transition w-full flex justify-center items-center gap-2">
                    <i class="fa-solid fa-trash-can"></i>
                    <span data-ar="حذف" data-en="Delete">حذف</span>
                </button>
            </div>
        </div>
    </div>

    <div id="toastContainer"
        class="fixed bottom-4 right-4 rtl:right-auto rtl:left-4 z-[60] flex flex-col gap-2 pointer-events-none"></div>

    <script>
        // --- Modal Logic ---
        const categoryModal = document.getElementById('categoryModal');
        const categoryModalContent = document.getElementById('categoryModalContent');
        const deleteModal = document.getElementById('deleteModal');
        const deleteModalContent = document.getElementById('deleteModalContent');
        const categoryForm = document.getElementById('categoryForm');

        function openCategoryModal(isEdit = false) {

            // فقط عند الإضافة الجديدة
            if (!isEdit) {
                categoryForm.reset();

                document.getElementById('categoryId').value = '';
                document.getElementById('formMethod').value = 'POST';
                document.getElementById('is_active').checked = true;
            }

            // مسح أخطاء التحقق
            document.querySelectorAll('[id^="error-"]').forEach(el => {
                el.classList.add('hidden');
                el.innerText = '';
            });

            const titleAr = isEdit ?
                'تعديل التصنيف' :
                'إضافة تصنيف جديد';

            const titleEn = isEdit ?
                'Edit Category' :
                'Add New Category';

            const currentLang = localStorage.getItem('lang') || 'ar';

            document.getElementById('modalTitle').innerHTML = `
        <span data-ar="${titleAr}" data-en="${titleEn}">
            ${currentLang === 'en' ? titleEn : titleAr}
        </span>
    `;

            categoryModal.classList.remove('hidden');

            setTimeout(() => {
                categoryModal.classList.remove('opacity-0');
                categoryModalContent.classList.remove('scale-95');
            }, 10);
        }

        function closeCategoryModal() {
            categoryModal.classList.add('opacity-0');
            categoryModalContent.classList.add('scale-95');
            setTimeout(() => categoryModal.classList.add('hidden'), 300);
        }

        function confirmDelete(id) {
            document.getElementById('deleteCategoryId').value = id;
            deleteModal.classList.remove('hidden');
            setTimeout(() => {
                deleteModal.classList.remove('opacity-0');
                deleteModalContent.classList.remove('scale-95');
            }, 10);
        }

        function closeDeleteModal() {
            deleteModal.classList.add('opacity-0');
            deleteModalContent.classList.add('scale-95');
            setTimeout(() => deleteModal.classList.add('hidden'), 300);
        }

        // --- Toast Notification ---
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' :
                'bg-red-50 border-red-200 text-red-800';
            const icon = type === 'success' ? 'fa-check-circle text-green-500' : 'fa-circle-exclamation text-red-500';

            toast.className =
                `flex items-center gap-3 p-4 border rounded-lg shadow-lg ${bgColor} transform translate-y-10 opacity-0 transition-all duration-300 w-80 bg-white pointer-events-auto`;
            toast.innerHTML = `
            <i class="fa-solid ${icon} text-xl"></i>
            <p class="font-medium text-sm flex-1">${message}</p>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
        `;

            document.getElementById('toastContainer').appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 10);

            // Auto remove
            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // --- CRUD Operations (AJAX) ---
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function submitCategoryForm(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin"></i> <span data-ar="جاري الحفظ..." data-en="Saving...">جاري الحفظ...</span>';

            // Clear previous errors
            document.querySelectorAll('[id^="error-"]').forEach(el => {
                el.classList.add('hidden');
                el.innerText = '';
            });

            const id = document.getElementById('categoryId').value;
            const method = document.getElementById('formMethod').value;
            const url = method === 'POST' ? '{{ route('item-categories.store') }}' : `/item-categories/${id}`;

            // Gather Data
            const formData = {
                name: document.getElementById('name').value,
                description: document.getElementById('description').value,
                is_active: document.getElementById('is_active').checked ? 1 : 0
            };

            if (method === 'PUT') {
                formData._method = 'PUT';
            }

            try {
                const response = await fetch(url, {
                    method: method === 'PUT' ? 'POST' :
                    'POST', // Laravel uses POST with _method=PUT for forms, or we can send JSON
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) { // Validation Errors
                        for (const [key, messages] of Object.entries(data.errors)) {
                            const errorEl = document.getElementById(`error-${key}`);
                            if (errorEl) {
                                errorEl.innerText = messages[0];
                                errorEl.classList.remove('hidden');
                            }
                        }
                    } else {
                        showToast(data.message || 'حدث خطأ غير متوقع', 'error');
                    }
                } else {
                    // Success
                    showToast(data.message, 'success');
                    closeCategoryModal();

                    // For a smooth experience, reload the page after 1s to show new data
                    // In a SPA you would manipulate the DOM, but reloading is safest to keep state synced 
                    setTimeout(() => window.location.reload(), 1000);
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('حدث خطأ في الاتصال بالخادم', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;

                // Re-apply language to button (hacky but needed since we overwrote innerHTML)
                const currentLang = localStorage.getItem('lang') || 'ar';
                if (typeof applyLanguage === 'function') applyLanguage(currentLang);
            }
        }

        async function editCategory(id) {
            try {
                const response = await fetch(`/item-categories/${id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    showToast(result.message || 'حدث خطأ أثناء جلب البيانات', 'error');
                    return;
                }

                if (result.success) {
                    const category = result.data;

                    // افتح المودال أولاً
                    openCategoryModal(true);

                    // ثم املأ البيانات
                    document.getElementById('categoryId').value = category.id;
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('name').value = category.name;
                    document.getElementById('description').value = category.description || '';
                    document.getElementById('is_active').checked = Boolean(category.is_active);
                }

            } catch (error) {
                console.error(error);
                showToast('حدث خطأ أثناء جلب البيانات', 'error');
            }
        }
        async function executeDelete() {
            const id = document.getElementById('deleteCategoryId').value;
            const btn = document.getElementById('confirmDeleteBtn');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const response = await fetch(`/item-categories/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    showToast(data.message, 'success');
                    closeDeleteModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'لا يمكن الحذف', 'error');
                    closeDeleteModal();
                }
            } catch (error) {
                showToast('حدث خطأ في الاتصال', 'error');
                closeDeleteModal();
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                const currentLang = localStorage.getItem('lang') || 'ar';
                if (typeof applyLanguage === 'function') applyLanguage(currentLang);
            }
        }

        // On Load: Make sure dynamic text picks up the correct language
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof applyLanguage === 'function') {
                applyLanguage(localStorage.getItem('lang') || 'ar');
            }
        });
    </script>
@endsection
