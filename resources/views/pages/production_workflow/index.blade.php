@extends('layouts.app')

@section('title', 'مسار الإنتاج - Work Flow')
@section('page_title', 'إدارة مسار الإنتاج')

@section('content')
    <div class="h-full flex flex-col font-sans transition-all duration-300">

        {{-- رأس الصفحة والفلترة السريعة (الكبسولات) --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">

            {{-- فلاتر الوقت (الكبسولات) --}}
            <div
                class="flex bg-white rounded-lg p-1 shadow-sm border border-gray-200 overflow-x-auto w-full md:w-auto custom-scrollbar">
                <button
                    class="flex-1 md:flex-none px-4 py-2 rounded-md bg-red-50 text-red-600 font-bold text-sm border border-red-100 transition hover:bg-red-100 flex items-center justify-center gap-2 min-w-max">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span data-ar="متأخر ({{ $lateTasks }})" data-en="Late ({{ $lateTasks }})">متأخر
                        ({{ $lateTasks }})</span>
                </button>
                <button
                    class="flex-1 md:flex-none px-4 py-2 rounded-md bg-[#8A704C] text-white font-bold text-sm mx-1 shadow-md flex items-center justify-center gap-2 min-w-max">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span data-ar="تسليم اليوم ({{ $todayTasks }})" data-en="Today's Delivery ({{ $todayTasks }})">تسليم
                        اليوم ({{ $todayTasks }})</span>
                </button>
                <button
                    class="flex-1 md:flex-none px-4 py-2 rounded-md text-gray-600 hover:bg-gray-100 font-bold text-sm transition flex items-center justify-center gap-2 min-w-max">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span data-ar="قادم ({{ $futureTasks }})" data-en="Upcoming ({{ $futureTasks }})">قادم
                        ({{ $futureTasks }})</span>
                </button>
            </div>

            {{-- أزرار تحكم الإدارة (تظهر للآدمن فقط) --}}
            @if (auth()->user()->isAdmin())
                <div class="flex gap-2 w-full md:w-auto">
                    <button onclick="openAssignModal()"
                        class="w-full md:w-auto bg-gray-900 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition shadow-lg flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span data-ar="تكليف مشرف بطلبات جديدة" data-en="Assign Supervisor to New Orders">تكليف مشرف بطلبات
                            جديدة</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- لوحة العمل (Kanban Board) --}}
        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4 custom-scrollbar">
            <div class="flex gap-4 h-full min-w-max items-start px-1">

                {{-- أعمدة الحالات (Columns) --}}
                @php
                    $stages = [
                        [
                            'id' => 'new',
                            'title_ar' => 'لم يتم البدء',
                            'title_en' => 'Not Started',
                            'color' => 'gray',
                            'icon' => 'fa-box',
                        ],
                        [
                            'id' => 'cutting',
                            'title_ar' => 'القص',
                            'title_en' => 'Cutting',
                            'color' => 'blue',
                            'icon' => 'fa-scissors',
                        ],
                        [
                            'id' => 'sewing',
                            'title_ar' => 'الخياطة',
                            'title_en' => 'Sewing',
                            'color' => 'purple',
                            'icon' => 'fa-tape',
                        ],
                        [
                            'id' => 'ironing',
                            'title_ar' => 'الكوي',
                            'title_en' => 'Ironing',
                            'color' => 'orange',
                            'icon' => 'fa-shirt',
                        ],
                        [
                            'id' => 'finishing',
                            'title_ar' => 'التقفيل',
                            'title_en' => 'Finishing',
                            'color' => 'teal',
                            'icon' => 'fa-check-double',
                        ],
                        [
                            'id' => 'ready-to-ship',
                            'title_ar' => 'جاهز للشحن',
                            'title_en' => 'Ready to Ship',
                            'color' => 'green',
                            'icon' => 'fa-truck-fast',
                        ],
                    ];
                @endphp

                @foreach ($stages as $stage)
                    <div
                        class="w-80 h-full flex flex-col bg-gray-50/50 border border-gray-200 rounded-xl flex-shrink-0 shadow-sm">
                        <div class="p-4 border-b border-gray-200 bg-white rounded-t-xl flex justify-between items-center">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid {{ $stage['icon'] }} text-{{ $stage['color'] }}-500"></i>
                                <span data-ar="{{ $stage['title_ar'] }}"
                                    data-en="{{ $stage['title_en'] }}">{{ $stage['title_ar'] }}</span>
                            </h3>
                            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs font-bold">
                                {{ $stageCounts[$stage['id']] ?? 0 }}
                            </span>
                        </div>

                        {{-- منطقة الإسقاط (Drop Zone) --}}
                        <div class="flex-1 p-3 overflow-y-auto space-y-3 kanban-column custom-scrollbar"
                            data-status="{{ $stage['id'] }}" ondragover="allowDrop(event)"
                            ondrop="handleDrop(event, '{{ $stage['id'] }}')">

                            {{-- بطاقات الأوردرات --}}
                            @foreach ($ordersByStage[$stage['id']] ?? [] as $order)
                                @php
                                    $task = $order->productionTasks->last();
                                    $isUrgent = $task && $task->priority == 'urgent';
                                    $isMine = $order->is_my_order;
                                @endphp
                                <div class="relative p-3 rounded-xl transition-all duration-300
                                {{ $isMine
                                    ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-500 shadow-lg ring-2 ring-green-200'
                                    : 'bg-white border border-gray-200 opacity-75 hover:opacity-100' }}
                                {{ $isUrgent ? 'border-red-500' : '' }}"
                                    cursor-pointer hover:shadow-md transition relative group" draggable="true"
                                    ondragstart="drag(event)"
                                    onclick="window.location='{{ route('orders.show', $order->id) }}'"
                                    id="order-{{ $order->id }}" data-order-id="{{ $order->id }}">

                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold text-gray-500">#{{ $order->order_number }}</span>
                                        <div class="flex gap-1 items-center">
                                            @if ($isUrgent)
                                                <span data-ar="عاجل" data-en="Urgent"
                                                    class="text-[10px] font-bold bg-red-100 text-red-600 px-2 py-0.5 rounded-full">عاجل</span>
                                            @endif

                                            {{-- زر بديل للسحب والإفلات مخصص للموبايل والديسكتوب --}}
                                            <button type="button"
                                                onclick="event.stopPropagation(); openMoveModal('{{ $order->id }}', '{{ $stage['id'] }}')"
                                                class="text-gray-400 hover:text-[#8A704C] bg-gray-50 hover:bg-gray-100 px-2 py-1 rounded text-xs transition border border-gray-100"
                                                title="نقل الأوردر">
                                                <i class="fa-solid fa-right-left"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $order->customer_name }}</h4>

                                    <p class="text-xs text-gray-500 mb-3 flex items-center gap-1">
                                        <i class="fa-solid fa-layer-group"></i> {{ $order->items->sum('quantity') }}
                                        <span data-ar="قطعة" data-en="Pieces">قطعة</span>
                                    </p>

                                    <div
                                        class="flex justify-between items-center pt-2 border-t border-gray-100 text-[11px] text-gray-500">
                                        <div class="flex items-center gap-2">

                                            <i class="fa-solid fa-user-tie text-[#8A704C]"></i>

                                            @if ($task && $task->supervisor)
                                                @if ($isMine)
                                                    <span class="font-bold text-green-700">

                                                        {{ $task->supervisor->name }}

                                                    </span>
                                                @else
                                                    <span class="font-semibold text-gray-700">

                                                        {{ $task->supervisor->name }}

                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">

                                                    غير مكلف

                                                </span>
                                            @endif

                                        </div>
                                        @if ($task && $task->required_date)
                                            <div
                                                class="flex items-center gap-1 font-bold {{ \Carbon\Carbon::parse($task->required_date)->isPast() ? 'text-red-500' : '' }}">
                                                <i class="fa-regular fa-clock"></i>
                                                {{ \Carbon\Carbon::parse($task->required_date)->format('Y-m-d') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>

    {{-- ==================== النوافذ المنبثقة (Modals) ==================== --}}

    {{-- 1. Modal تكليف المهام من الآدمن للمشرف --}}
    @if (auth()->user()->isAdmin())
        <div id="assignModal"
            class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="p-4 bg-gray-900 text-white flex justify-between items-center">
                    <h3 class="font-bold text-lg" data-ar="تكليف مشرف بأوردرات" data-en="Assign Supervisor to Orders">تكليف
                        مشرف بأوردرات</h3>
                    <button onclick="closeAssignModal()" class="text-gray-400 hover:text-white transition"><i
                            class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form action="{{ route('production.workflow.assign') ?? '#' }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1" data-ar="المشرف المسؤول"
                            data-en="Supervisor">المشرف المسؤول</label>
                        <select name="supervisor_id" required
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none">
                            <option value="" data-ar="اختر المشرف..." data-en="Select Supervisor...">اختر المشرف...
                            </option>
                            @foreach ($supervisors as $supervisor)
                                <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1" data-ar="الطلبات المتاحة"
                            data-en="Available Orders">الطلبات المتاحة</label>
                        <select name="order_ids[]" multiple required
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none h-32 custom-scrollbar">
                            @forelse ($unassignedOrders as $uOrder)
                                <option value="{{ $uOrder->id }}">#{{ $uOrder->order_number }} -
                                    {{ $uOrder->customer_name }} ({{ $uOrder->items->sum('quantity') }})</option>
                            @empty
                                <option disabled data-ar="لا يوجد أوردرات جديدة غير مكلفة"
                                    data-en="No unassigned orders available">لا يوجد أوردرات جديدة غير مكلفة</option>
                            @endforelse
                        </select>
                        <small class="text-gray-500" data-ar="يمكنك تحديد أكثر من أوردر"
                            data-en="You can select multiple orders">يمكنك تحديد أكثر من أوردر</small>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1" data-ar="تاريخ التسليم المطلوب"
                                data-en="Required Date">تاريخ التسليم المطلوب</label>
                            <input type="date" name="required_date" required
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1" data-ar="الأولوية"
                                data-en="Priority">الأولوية</label>
                            <select name="priority"
                                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none">
                                <option value="low" data-ar="منخفضة" data-en="Low">منخفضة</option>
                                <option value="medium" selected data-ar="متوسطة" data-en="Medium">متوسطة</option>
                                <option value="high" data-ar="عالية" data-en="High">عالية</option>
                                <option value="urgent" data-ar="عاجل جداً" data-en="Urgent">عاجل جداً</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1" data-ar="تعليمات الإدارة"
                            data-en="Instructions">تعليمات الإدارة</label>
                        <textarea name="instruction" rows="2"
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" onclick="closeAssignModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition"
                            data-ar="إلغاء" data-en="Cancel">إلغاء</button>
                        <button type="submit"
                            class="px-4 py-2 bg-[#8A704C] text-white font-bold rounded-lg hover:bg-[#7a6242] transition"
                            data-ar="إصدار التكليف" data-en="Assign">إصدار التكليف</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- 2. Modal تغيير الحالة (الـ Drag & Drop أو التحريك اليدوي) --}}
    <div id="statusModal"
        class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-4 bg-[#8A704C] text-white flex justify-between items-center">
                <h3 class="font-bold text-lg" id="statusModalTitle">تأكيد انتقال المرحلة</h3>
                <button onclick="closeStatusModal()" class="text-white/80 hover:text-white transition"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="changeStatusForm" class="p-5 space-y-4">
                <input type="hidden" id="modal_order_id">

                {{-- حقل اختيار المرحلة (يظهر فقط عند النقل بالزر وليس السحب) --}}
                <div id="targetStageDiv" class="hidden">
                    <label class="block text-sm font-bold text-gray-700 mb-1" data-ar="نقل إلى مرحلة:"
                        data-en="Move to stage:">نقل إلى مرحلة:</label>
                    <select id="modal_to_status" onchange="toggleEmployeeField(this.value)"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none">
                        <option value="" data-ar="اختر المرحلة..." data-en="Select stage...">اختر المرحلة...
                        </option>
                        @foreach ($stages as $st)
                            <option value="{{ $st['id'] }}" data-ar="{{ $st['title_ar'] }}"
                                data-en="{{ $st['title_en'] }}">{{ $st['title_ar'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- حقل الموظف يظهر فقط في المراحل التي تتطلب تعيين عامل --}}
                <div id="employeeSelectionDiv" class="hidden">
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        <span data-ar="الموظف المنفذ" data-en="Assigned Employee">الموظف المنفذ</span> <span
                            class="text-red-500">*</span>
                    </label>
                    <select name="employee_id" id="employee_id"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none">
                        <option value="" data-ar="اختر الموظف..." data-en="Select Employee...">اختر الموظف...
                        </option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        <span data-ar="وقت التنفيذ الفعلي" data-en="Execution Time">وقت التنفيذ الفعلي</span> <span
                            class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="executed_at" id="executed_at" required
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none ltr-input"
                        dir="ltr">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1" data-ar="ملاحظات (اختياري)"
                        data-en="Notes (Optional)">ملاحظات (اختياري)</label>
                    <textarea name="note" id="status_note" rows="2"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" onclick="cancelDrop()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition"
                        data-ar="إلغاء" data-en="Cancel">إلغاء</button>
                    <button type="button" onclick="confirmStatusChange()"
                        class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span data-ar="حفظ التغيير" data-en="Save Change">حفظ التغيير</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div id="modernToast" class="fixed top-6 right-6 z-[99999] hidden">

        <div class="bg-white rounded-2xl shadow-2xl border border-green-200 overflow-hidden w-[360px]">

            <div class="h-1 bg-green-500" id="toastProgress"></div>

            <div class="p-5 flex items-start gap-4">

                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center shrink-0">

                    <i class="fa-solid fa-check text-green-600 text-xl"></i>

                </div>

                <div class="flex-1">

                    <h3 class="font-bold text-gray-800 text-base">
                        تم التنفيذ بنجاح
                    </h3>

                    <p id="toastMessage" class="text-sm text-gray-600 mt-1 leading-6">
                    </p>

                </div>

            </div>

        </div>

    </div>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .drag-over {
            background-color: #fef9c3 !important;
            border: 2px dashed #8A704C !important;
        }

        .ltr-input {
            text-align: left;
        }

        html[dir="rtl"] .ltr-input {
            text-align: right;
            direction: ltr;
        }
    </style>
    <style>
        #modernToast {

            transform: translateX(120%);
            opacity: 0;
            transition: .45s cubic-bezier(.2, .8, .2, 1);

        }

        #modernToast.show {

            transform: translateX(0);
            opacity: 1;

        }

        #toastProgress {

            width: 100%;
            transition: 4s linear;

        }
    </style>
@endsection

@push('scripts')
    <script>
        function showSuccessToast(user, order, stage) {

            const stages = {

                new: "لم يتم البدء",

                cutting: "القص",

                sewing: "الخياطة",

                ironing: "الكوي",

                finishing: "التقفيل",

                "ready-to-ship": "جاهز للشحن"

            };

            let toast = document.getElementById("modernToast");

            let progress = document.getElementById("toastProgress");

            document.getElementById("toastMessage").innerHTML = `
        أهلاً <b>${user}</b> 👋<br>
        تم نقل الطلب
        <b>${order}</b>
        إلى مرحلة
        <span class="text-green-600 font-bold">${stages[stage]}</span>
    `;

            progress.style.transition = "none";
            progress.style.width = "100%";

            toast.classList.remove("hidden");

            requestAnimationFrame(() => {

                toast.classList.add("show");

                progress.style.transition = "4s linear";
                progress.style.width = "0%";

            });

            setTimeout(() => {

                toast.classList.remove("show");

                setTimeout(() => {

                    toast.classList.add("hidden");

                }, 400);

            }, 4000);

        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('executed_at').value = now.toISOString().slice(0, 16);

            // تطبيق الترجمة فوراً على العناصر الجديدة داخل الصفحة
            if (typeof applyLanguage === 'function') {
                const currentLang = localStorage.getItem("lang") || "ar";
                applyLanguage(currentLang);
            }
        });

        function openAssignModal() {
            document.getElementById('assignModal').classList.remove('hidden');
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        let draggedElement = null;
        let originalParent = null;
        let pendingDropData = {};

        // === Drag and Drop Functions ===
        function drag(ev) {
            draggedElement = ev.target;
            originalParent = ev.target.closest('.kanban-column');
            ev.dataTransfer.setData("orderId", ev.target.getAttribute('data-order-id'));
            ev.target.classList.add('opacity-50');
        }

        function allowDrop(ev) {
            ev.preventDefault();
            let dropZone = ev.target.closest('.kanban-column');
            if (dropZone) dropZone.classList.add('drag-over');
        }

        document.addEventListener('dragleave', function(ev) {
            let dropZone = ev.target.closest('.kanban-column');
            if (dropZone) dropZone.classList.remove('drag-over');
        });

        document.addEventListener('dragend', function(ev) {
            if (draggedElement) draggedElement.classList.remove('opacity-50');
            document.querySelectorAll('.kanban-column').forEach(zone => zone.classList.remove('drag-over'));
        });

        function handleDrop(ev, newStatus) {
            ev.preventDefault();
            document.querySelectorAll('.kanban-column').forEach(zone => zone.classList.remove('drag-over'));

            if (!draggedElement) return;
            draggedElement.classList.remove('opacity-50');

            let orderId = ev.dataTransfer.getData("orderId");
            let dropZone = ev.target.closest('.kanban-column');
            let currentStatus = originalParent.getAttribute('data-status');

            if (currentStatus === newStatus) return;

            openStatusChangeModal(orderId, currentStatus, newStatus, dropZone, false);
        }

        // === Manual Move Function (For Mobile / Button Click) ===
        function openMoveModal(orderId, currentStatus) {
            draggedElement = document.getElementById('order-' + orderId);
            originalParent = draggedElement.closest('.kanban-column');

            openStatusChangeModal(orderId, currentStatus, null, null, true);
        }

        // === Unified Modal Opener ===
        function openStatusChangeModal(orderId, fromStatus, toStatus, dropZoneEl, isManualMove) {
            pendingDropData = {
                orderId,
                fromStatus,
                toStatus,
                dropZoneEl,
                isManualMove
            };
            const lang = localStorage.getItem("lang") || "ar";

            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('changeStatusForm').reset();

            // Set date to now
            let now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('executed_at').value = now.toISOString().slice(0, 16);

            let titleEl = document.getElementById('statusModalTitle');
            let targetStageSelect = document.getElementById('modal_to_status');
            let targetStageDiv = document.getElementById('targetStageDiv');

            if (isManualMove) {
                // فتح عن طريق الزر: إظهار قائمة اختيار المرحلة
                targetStageDiv.classList.remove('hidden');
                targetStageSelect.value = "";
                toggleEmployeeField(""); // إخفاء الموظف مبدئياً

                titleEl.setAttribute('data-ar', `نقل الطلب #${orderId}`);
                titleEl.setAttribute('data-en', `Move Order #${orderId}`);
                titleEl.innerText = lang === 'ar' ? `نقل الطلب #${orderId}` : `Move Order #${orderId}`;
            } else {
                // فتح عن طريق السحب والإفلات: إخفاء قائمة المراحل واعتماد المرحلة المسحوب إليها
                targetStageDiv.classList.add('hidden');
                targetStageSelect.value = toStatus;
                toggleEmployeeField(toStatus);

                let stageNameAr = getStageName(toStatus, 'ar');
                let stageNameEn = getStageName(toStatus, 'en');
                titleEl.setAttribute('data-ar', `تأكيد النقل إلى: ${stageNameAr}`);
                titleEl.setAttribute('data-en', `Confirm move to: ${stageNameEn}`);
                titleEl.innerText = lang === 'ar' ? `تأكيد النقل إلى: ${stageNameAr}` : `Confirm move to: ${stageNameEn}`;
            }

            document.getElementById('statusModal').classList.remove('hidden');
        }

        function toggleEmployeeField(statusId) {
            let requiresEmployee = ['cutting', 'sewing', 'ironing'].includes(statusId);
            let employeeDiv = document.getElementById('employeeSelectionDiv');
            let employeeInput = document.getElementById('employee_id');

            if (requiresEmployee) {
                employeeDiv.classList.remove('hidden');
                employeeInput.setAttribute('required', 'required');
            } else {
                employeeDiv.classList.add('hidden');
                employeeInput.removeAttribute('required');
            }
        }

        function cancelDrop() {
            if (draggedElement && originalParent && !pendingDropData.isManualMove) {
                originalParent.appendChild(draggedElement);
            }
            closeStatusModal();
        }

        function confirmStatusChange() {
            const lang = localStorage.getItem("lang") || "ar";

            // تحديد الوجهة النهائية
            let finalTargetStatus = pendingDropData.isManualMove ?
                document.getElementById('modal_to_status').value :
                pendingDropData.toStatus;

            if (!finalTargetStatus) {
                alert(lang === 'ar' ? 'يجب اختيار المرحلة التي سيتم النقل إليها.' : 'Please select the target stage.');
                return;
            }

            let empId = document.getElementById('employee_id').value;
            let requiresEmployee = ['cutting', 'sewing', 'ironing'].includes(finalTargetStatus);

            if (requiresEmployee && !empId) {
                alert(lang === 'ar' ? 'يجب اختيار الموظف المنفذ لهذه المرحلة.' :
                    'You must select an employee for this stage.');
                return;
            }

            let executedAt = document.getElementById('executed_at').value;
            if (!executedAt) {
                alert(lang === 'ar' ? 'تاريخ ووقت التنفيذ مطلوب.' : 'Execution date and time are required.');
                return;
            }

            let note = document.getElementById('status_note').value;

            fetch(`/production/workflow/order/${pendingDropData.orderId}/move`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        to_status: finalTargetStatus,
                        employee_id: empId,
                        executed_at: executedAt,
                        note: note
                    })
                })
                .then(async res => {

                    const data = await res.json().catch(() => null);

                    if (!res.ok) {
                        console.log(res.status);
                        console.log(data);
                        throw new Error(JSON.stringify(data));
                    }

                    return data;
                })
                .then(data => {

                    if (data.success) {

                        let destColumn = pendingDropData.dropZoneEl ||
                            document.querySelector(
                                `.kanban-column[data-status="${finalTargetStatus}"]`
                            );

                        if (destColumn && draggedElement) {
                            destColumn.appendChild(draggedElement);
                        }

                        closeStatusModal();

                        showSuccessToast(
                            data.user,
                            data.order,
                            data.stage
                        );

                    } else {

                        alert(data.message);

                    }

                })
                .catch(err => {
                    console.error(err);
                });
        }

        function getStageName(status, lang) {
            const stages = {
                'new': {
                    ar: 'لم يتم البدء',
                    en: 'Not Started'
                },
                'cutting': {
                    ar: 'القص',
                    en: 'Cutting'
                },
                'sewing': {
                    ar: 'الخياطة',
                    en: 'Sewing'
                },
                'ironing': {
                    ar: 'الكوي',
                    en: 'Ironing'
                },
                'finishing': {
                    ar: 'التقفيل',
                    en: 'Finishing'
                },
                'ready-to-ship': {
                    ar: 'جاهز للشحن',
                    en: 'Ready to Ship'
                }
            };
            return stages[status] ? stages[status][lang] : status;
        }
    </script>
@endpush
