@extends('layouts.app') {{-- قم بتغيير هذا حسب اسم ملف اللي آوت لديك --}}

@section('title', 'Users Management')
@section('page_title', 'Users Management')

@section('content')

<!-- عرض رسائل النجاح أو الأخطاء -->
@if(session('success'))
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center animate-fade-in-down" id="alert-success">
    <div class="flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-xl"></i>
        <p class="font-medium">{{ session('success') }}</p>
    </div>
    <button onclick="document.getElementById('alert-success').remove()" class="text-green-700 hover:text-green-900">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
@endif

@if($errors->any())
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex justify-between items-start animate-fade-in-down" id="alert-error">
    <div class="flex items-start gap-3">
        <i class="fa-solid fa-triangle-exclamation text-xl mt-0.5"></i>
        <div>
            <p class="font-bold">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <button onclick="document.getElementById('alert-error').remove()" class="text-red-700 hover:text-red-900">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
@endif

<!-- القسم العلوي: البحث وزر الإضافة -->
<div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
    <!-- البحث -->
    <form action="{{ route('users.index') }}" method="GET" class="w-full sm:w-1/2 md:w-1/3 relative">
        <input type="text" name="search" value="{{ request('search') }}" 
            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#8A704C] focus:ring-1 focus:ring-[#8A704C] transition bg-white shadow-sm text-sm" 
            placeholder="Search by name or email...">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        @if(request('search'))
            <a href="{{ route('users.index') }}" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-red-400 hover:text-red-600">
                <i class="fa-solid fa-xmark"></i>
            </a>
        @endif
    </form>

    <!-- زر فتح اللوحة الجانبية (Add User) -->
    <button onclick="openUserPanel('add')" class="w-full sm:w-auto bg-[#8A704C] hover:bg-[#735d3f] text-white px-5 py-2.5 rounded-lg font-medium transition shadow-md flex items-center justify-center gap-2 text-sm">
        <i class="fa-solid fa-user-plus"></i>
        <span>Add New User</span>
    </button>
</div>

<!-- جدول المستخدمين -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-left text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-medium">
                <tr>
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Joined Date</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-6 py-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-gray-900 font-semibold">{{ $user->name }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">{{ $user->email }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->role === 'admin')
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-[#8A704C] text-xs font-semibold flex items-center gap-1.5 w-max">
                                <i class="fa-solid fa-shield-halved"></i> Admin
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold flex items-center gap-1.5 w-max">
                                <i class="fa-solid fa-user-gear"></i> Supervisor
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <!-- زر التعديل يفتح نفس اللوحة الجانبية مع تمرير البيانات -->
                        <button onclick="openUserPanel('edit', {{ $user }})" class="w-8 h-8 rounded-md bg-gray-100 text-gray-600 hover:bg-[#8A704C] hover:text-white transition inline-flex items-center justify-center" title="Edit User">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        
                        <!-- زر الحذف -->
                        @if(auth()->id() !== $user->id)
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-md bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition inline-flex items-center justify-center" title="Delete User">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        @else
                            <button class="w-8 h-8 rounded-md bg-gray-100 text-gray-300 cursor-not-allowed inline-flex items-center justify-center" title="Cannot delete yourself">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium text-gray-600">No users found</p>
                            <p class="text-sm">Try adjusting your search or add a new user.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- الـ Pagination -->
    @if($users->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- ========================================================= -->
<!-- Side Panel (Offcanvas) بديل الـ Modals -->
<!-- ========================================================= -->
<div id="userPanelOverlay" class="fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm" onclick="closeUserPanel()"></div>

<div id="userPanel" class="fixed top-0 right-0 h-full w-full sm:w-[450px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col border-l border-gray-200">
    
    <!-- Panel Header -->
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <div>
            <h2 id="panelTitle" class="text-lg font-bold text-gray-800">Add New User</h2>
            <p id="panelDesc" class="text-xs text-gray-500 mt-1">Fill in the details to create a new user account.</p>
        </div>
        <button onclick="closeUserPanel()" class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Panel Body (Form) -->
    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        <form id="userForm" method="POST" action="{{ route('users.store') }}">
            @csrf
            <!-- يتم تغيير هذا الحقل ديناميكياً بواسطة JS في حالة التعديل -->
            <div id="methodContainer"></div>

            <div class="space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-regular fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="name" id="userName" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" id="userEmail" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm">
                    </div>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">System Role <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="fa-solid fa-layer-group absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <select name="role" id="userRole" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm appearance-none bg-white">
                            <option value="" disabled selected>Select a role...</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5 mt-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-lock text-[#8A704C]"></i> Security
                    </h3>
                    
                    <div id="passwordHelp" class="text-xs text-amber-600 bg-amber-50 p-2 rounded mb-4 hidden border border-amber-200">
                        <i class="fa-solid fa-circle-info mr-1"></i> Leave password fields blank if you don't want to change the current password.
                    </div>

                    <div class="space-y-4">
                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password <span id="passReqStar" class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" name="password" id="userPassword" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm" minlength="8">
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span id="passConfReqStar" class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="fa-solid fa-check-double absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" name="password_confirmation" id="userPasswordConf" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm" minlength="8">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Panel Footer -->
    <div class="p-5 border-t border-gray-200 bg-gray-50 flex justify-end gap-3 shrink-0">
        <button type="button" onclick="closeUserPanel()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
            Cancel
        </button>
        <button type="submit" form="userForm" id="submitBtn" class="px-5 py-2 text-sm font-medium text-white bg-[#8A704C] hover:bg-[#735d3f] rounded-lg transition shadow-md flex items-center gap-2">
            <i class="fa-solid fa-check"></i> <span>Save User</span>
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // متغيرات الـ DOM
    const panel = document.getElementById('userPanel');
    const overlay = document.getElementById('userPanelOverlay');
    const form = document.getElementById('userForm');
    const methodContainer = document.getElementById('methodContainer');
    
    // حقول الفورم
    const inputName = document.getElementById('userName');
    const inputEmail = document.getElementById('userEmail');
    const inputRole = document.getElementById('userRole');
    const inputPass = document.getElementById('userPassword');
    const inputPassConf = document.getElementById('userPasswordConf');
    
    // العناصر المتغيرة بناءً على الحالة
    const panelTitle = document.getElementById('panelTitle');
    const panelDesc = document.getElementById('panelDesc');
    const submitBtnSpan = document.querySelector('#submitBtn span');
    const passReqStar = document.getElementById('passReqStar');
    const passConfReqStar = document.getElementById('passConfReqStar');
    const passwordHelp = document.getElementById('passwordHelp');

    // فتح اللوحة (Add / Edit)
    function openUserPanel(mode, userData = null) {
        // إظهار اللوحة
        overlay.classList.remove('hidden');
        setTimeout(() => overlay.classList.add('opacity-100'), 10);
        panel.classList.remove('translate-x-full');

        // تنظيف الفورم
        form.reset();
        methodContainer.innerHTML = '';

        if (mode === 'add') {
            // إعدادات وضع الإضافة
            panelTitle.innerText = 'Add New User';
            panelDesc.innerText = 'Fill in the details to create a new user account.';
            submitBtnSpan.innerText = 'Create User';
            form.action = "{{ route('users.store') }}";
            
            // الباسورد مطلوب
            inputPass.required = true;
            inputPassConf.required = true;
            passReqStar.classList.remove('hidden');
            passConfReqStar.classList.remove('hidden');
            passwordHelp.classList.add('hidden');

        } else if (mode === 'edit' && userData) {
            // إعدادات وضع التعديل
            panelTitle.innerText = 'Edit User';
            panelDesc.innerText = `Update information for ${userData.name}.`;
            submitBtnSpan.innerText = 'Save Changes';
            
            // إضافة ميثود PUT
            methodContainer.innerHTML = '@method("PUT")';
            // تعديل مسار الفورم
            form.action = `/users/${userData.id}`;

            // ملء البيانات
            inputName.value = userData.name;
            inputEmail.value = userData.email;
            inputRole.value = userData.role;

            // الباسورد اختياري في التعديل
            inputPass.required = false;
            inputPassConf.required = false;
            passReqStar.classList.add('hidden');
            passConfReqStar.classList.add('hidden');
            passwordHelp.classList.remove('hidden');
        }
    }

    // إغلاق اللوحة
    function closeUserPanel() {
        panel.classList.add('translate-x-full');
        overlay.classList.remove('opacity-100');
        setTimeout(() => overlay.classList.add('hidden'), 300);
    }
</script>

<style>
    /* أنيميشن ناعم للإشعارات (Alerts) */
    @keyframes fadeInDown {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.4s ease-out forwards;
    }
</style>
@endpush