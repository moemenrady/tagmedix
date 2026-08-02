@extends('layouts.app') {{-- قم بتغيير هذا حسب اسم ملف اللي آوت لديك --}}

@section('title', 'Employees Management')
@section('page_title', 'Employees Management')

@section('content')

    <!-- عرض رسائل النجاح أو الأخطاء -->
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center animate-fade-in-down"
            id="alert-success">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="document.getElementById('alert-success').remove()" class="text-green-700 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex justify-between items-start animate-fade-in-down"
            id="alert-error">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-xl mt-0.5"></i>
                <div>
                    <p class="font-bold">يرجى تصحيح الأخطاء التالية:</p>
                    <ul class="list-disc list-inside text-sm mt-1">
                        @foreach ($errors->all() as $error)
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
        <form action="{{ route('employees.index') }}" method="GET" class="w-full sm:w-1/2 md:w-1/3 relative">
            <input type="text" name="search" value="{{ request('search') }}"
                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#8A704C] focus:ring-1 focus:ring-[#8A704C] transition bg-white shadow-sm text-sm"
                placeholder="Search by name, phone, address...">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            @if (request('search'))
                <a href="{{ route('employees.index') }}"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-red-400 hover:text-red-600">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>

        <!-- زر فتح اللوحة الجانبية (Add Employee) -->
        <button onclick="openEmployeePanel('add')"
            class="w-full sm:w-auto bg-[#8A704C] hover:bg-[#735d3f] text-white px-5 py-2.5 rounded-lg font-medium transition shadow-md flex items-center justify-center gap-2 text-sm">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add New Employee</span>
        </button>
    </div>

    <!-- جدول الموظفين -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-medium">
                    <tr>
                        <th class="px-6 py-4">Employee Info</th>
                        <th class="px-6 py-4">Salary Details</th>
                        <th class="px-6 py-4">Hire Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold shrink-0">
                                    {{ strtoupper(mb_substr($employee->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-gray-900 font-semibold">{{ $employee->name }}</p>
                                    <p class="text-gray-500 text-xs mt-0.5 flex items-center gap-1">
                                        <i class="fa-solid fa-phone text-[10px]"></i> {{ $employee->phone ?? 'N/A' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($employee->salary_type === 'monthly')
                                    <span
                                        class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold flex items-center gap-1.5 w-max mb-1">
                                        <i class="fa-solid fa-calendar-days"></i> Monthly
                                    </span>
                                    <p class="text-gray-600 text-xs font-medium">
                                        {{ number_format($employee->monthly_salary, 2) }}</p>
                                @else
                                    <span
                                        class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-semibold flex items-center gap-1.5 w-max">
                                        <i class="fa-solid fa-scissors"></i> Piece Rate
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($employee->active)
                                    <span
                                        class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold flex items-center gap-1 w-max">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Active
                                    </span>
                                @else
                                    <span
                                        class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold flex items-center gap-1 w-max">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <!-- زر التعديل -->
                                <button onclick="openEmployeePanel('edit', {{ $employee }})"
                                    class="w-8 h-8 rounded-md bg-gray-100 text-gray-600 hover:bg-[#8A704C] hover:text-white transition inline-flex items-center justify-center"
                                    title="Edit Employee">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>

                                <!-- زر الحذف -->
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 rounded-md bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition inline-flex items-center justify-center"
                                        title="Delete Employee">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                    <p class="text-lg font-medium text-gray-600">No employees found</p>
                                    <p class="text-sm">Try adjusting your search or add a new employee.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- الـ Pagination -->
        @if ($employees->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================= -->
    <!-- Side Panel (Offcanvas) بديل الـ Modals -->
    <!-- ========================================================= -->
    <div id="employeePanelOverlay"
        class="fixed inset-0 bg-black/40 z-40 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm"
        onclick="closeEmployeePanel()"></div>

    <div id="employeePanel"
        class="fixed top-0 right-0 h-full w-full sm:w-[450px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col border-l border-gray-200">

        <!-- Panel Header -->
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <div>
                <h2 id="panelTitle" class="text-lg font-bold text-gray-800">Add New Employee</h2>
                <p id="panelDesc" class="text-xs text-gray-500 mt-1">Fill in the details to add a new employee.</p>
            </div>
            <button onclick="closeEmployeePanel()"
                class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Panel Body (Form) -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
            <form id="employeeForm" method="POST" action="{{ route('employees.store') }}">
                @csrf
                <!-- يتم تغيير هذا الحقل ديناميكياً بواسطة JS في حالة التعديل -->
                <div id="methodContainer"></div>

                <div class="space-y-5">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <i
                                class="fa-regular fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="name" id="empName" required
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm">
                        </div>
                    </div>

                    <!-- Phone & Hire Date -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <div class="relative">
                                <i
                                    class="fa-solid fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="phone" id="empPhone"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                            <div class="relative">
                                <input type="date" name="hire_date" id="empHireDate"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm text-gray-700">
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <div class="relative">
                            <i class="fa-solid fa-map-location-dot absolute left-3 top-3 text-gray-400"></i>
                            <textarea name="address" id="empAddress" rows="2"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm"></textarea>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5 mt-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-money-check-dollar text-[#8A704C]"></i> Employment Details
                        </h3>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="active" id="empActive" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm appearance-none bg-white">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Salary Type -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Salary Type <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="salary_type" id="empSalaryType" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm appearance-none bg-white">
                                    <option value="" disabled selected>Select Type...</option>
                                    <option value="monthly">Monthly Salary</option>
                                    <option value="piece_rate">Piece Rate (بالإنتاج)</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Monthly Salary Amount -->
                        <div id="monthlySalaryContainer" class="hidden transition-all duration-300">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary Amount</label>
                            <div class="relative">
                                <i
                                    class="fa-solid fa-coins absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="number" step="0.01" min="0" name="monthly_salary"
                                    id="empMonthlySalary"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#8A704C] focus:border-[#8A704C] outline-none transition text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Panel Footer -->
        <div class="p-5 border-t border-gray-200 bg-gray-50 flex justify-end gap-3 shrink-0">
            <button type="button" onclick="closeEmployeePanel()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                Cancel
            </button>
            <button type="submit" form="employeeForm" id="submitBtn"
                class="px-5 py-2 text-sm font-medium text-white bg-[#8A704C] hover:bg-[#735d3f] rounded-lg transition shadow-md flex items-center gap-2">
                <i class="fa-solid fa-check"></i> <span>Save Employee</span>
            </button>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // متغيرات الـ DOM
        const panel = document.getElementById('employeePanel');
        const overlay = document.getElementById('employeePanelOverlay');
        const form = document.getElementById('employeeForm');
        const methodContainer = document.getElementById('methodContainer');

        // حقول الفورم
        const inputName = document.getElementById('empName');
        const inputPhone = document.getElementById('empPhone');
        const inputHireDate = document.getElementById('empHireDate');
        const inputAddress = document.getElementById('empAddress');
        const inputActive = document.getElementById('empActive');
        const inputSalaryType = document.getElementById('empSalaryType');
        const inputMonthlySalary = document.getElementById('empMonthlySalary');
        const monthlySalaryContainer = document.getElementById('monthlySalaryContainer');

        // العناصر المتغيرة بناءً على الحالة
        const panelTitle = document.getElementById('panelTitle');
        const panelDesc = document.getElementById('panelDesc');
        const submitBtnSpan = document.querySelector('#submitBtn span');

        // منطق إظهار حقل الراتب الشهري
        inputSalaryType.addEventListener('change', function() {
            toggleMonthlySalary(this.value);
        });

        function toggleMonthlySalary(type) {
            if (type === 'monthly') {
                monthlySalaryContainer.classList.remove('hidden');
            } else {
                monthlySalaryContainer.classList.add('hidden');
                inputMonthlySalary.value = ''; // تصفير الحقل إذا اختار Piece Rate
            }
        }

        // فتح اللوحة (Add / Edit)
        function openEmployeePanel(mode, empData = null) {
            // إظهار اللوحة
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.add('opacity-100'), 10);
            panel.classList.remove('translate-x-full');

            // تنظيف الفورم
            form.reset();
            methodContainer.innerHTML = '';
            toggleMonthlySalary(''); // إخفاء حقل الراتب مبدئياً

            if (mode === 'add') {
                // إعدادات وضع الإضافة
                panelTitle.innerText = 'Add New Employee';
                panelDesc.innerText = 'Fill in the details to add a new employee.';
                submitBtnSpan.innerText = 'Save Employee';
                form.action = "{{ route('employees.store') }}";
                inputActive.value = "1"; // الافتراضي فعال

            } else if (mode === 'edit' && empData) {
                // إعدادات وضع التعديل
                panelTitle.innerText = 'Edit Employee';
                panelDesc.innerText = `Update information for ${empData.name}.`;
                submitBtnSpan.innerText = 'Save Changes';

                // إضافة ميثود PUT
                methodContainer.innerHTML = '@method('PUT')';
                // تعديل مسار الفورم
                form.action = `/employees/${empData.id}`; // تأكد من الـ Route Name

                // ملء البيانات
                inputName.value = empData.name;
                inputPhone.value = empData.phone || '';
                inputAddress.value = empData.address || '';
                inputSalaryType.value = empData.salary_type;
                inputActive.value = empData.active ? "1" : "0";

                if (empData.hire_date) {
                    // تحويل التاريخ لصيغة YYYY-MM-DD لتناسب حقل الـ Date
                    inputHireDate.value = empData.hire_date.substring(0, 10);
                }

                if (empData.salary_type === 'monthly') {
                    inputMonthlySalary.value = empData.monthly_salary || '';
                }

                toggleMonthlySalary(empData.salary_type);
            }
        }

        // إغلاق اللوحة
        function closeEmployeePanel() {
            panel.classList.add('translate-x-full');
            overlay.classList.remove('opacity-100');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }
    </script>

    <style>
        /* أنيميشن ناعم للإشعارات (Alerts) */
        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-down {
            animation: fadeInDown 0.4s ease-out forwards;
        }
    </style>
@endpush
