@extends('layouts.app')

@section('title', 'مسار الإنتاج - Work Flow')
@section('page_title', 'إدارة مسار الإنتاج')

@section('content')
    <div class="h-full flex flex-col font-sans transition-all duration-300">

        {{-- رأس الصفحة والفلترة --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
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

        {{-- لوحة العمل (Kanban Board) - مهيئة تماماً للموبايل --}}
        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4 custom-scrollbar snap-x snap-mandatory">
            <div class="flex gap-4 h-full items-start px-1 sm:min-w-max">

                @php
                    // المراحل الأربعة فقط حسب طلبك
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
                            'id' => 'ready-to-ship',
                            'title_ar' => 'التقفيل',
                            'title_en' => 'Finishing',
                            'color' => 'teal',
                            'icon' => 'fa-check-double',
                        ],
                    ];
                @endphp

                @foreach ($stages as $stage)
                    {{-- تعديل العرض ليكون متجاوب جداً مع شاشات الموبايل (85vw) --}}
                    <div
                        class="w-[85vw] sm:w-80 h-full flex flex-col bg-gray-50/50 border border-gray-200 rounded-xl flex-shrink-0 shadow-sm snap-center">
                        <div class="p-4 border-b border-gray-200 bg-white rounded-t-xl flex justify-between items-center">
                            <div class="flex justify-between items-center gap-2 w-full">
                                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid {{ $stage['icon'] }} text-{{ $stage['color'] }}-500"></i>
                                    <span data-ar="{{ $stage['title_ar'] }}"
                                        data-en="{{ $stage['title_en'] }}">{{ $stage['title_ar'] }}</span>
                                </h3>

                                <div class="flex items-center gap-2">
                                    {{-- زر تصدير الإكسيل يظهر فقط في مرحلة "لم يتم البدء" --}}
                                    @if ($stage['id'] === 'new')
                                        <a href="{{ route('production.workflow.export-not-started') }}"
                                            class="bg-[#107c41] text-white px-2.5 py-1 rounded hover:bg-green-800 transition flex items-center gap-1 shadow-sm"
                                            title="تصدير إكسيل">
                                            <i class="fa-solid fa-file-excel"></i>
                                            <span class="text-xs font-bold hidden sm:inline">إكسيل</span>
                                        </a>
                                    @endif

                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $stageCounts[$stage['id']] ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- منطقة الإسقاط والبطاقات --}}
                        <div class="flex-1 p-3 overflow-y-auto space-y-3 kanban-column custom-scrollbar"
                            data-status="{{ $stage['id'] }}" ondragover="allowDrop(event)"
                            ondrop="handleDrop(event, '{{ $stage['id'] }}')">

                            @foreach ($ordersByStage[$stage['id']] ?? [] as $order)
                                @php
                                    $task = $order->productionTasks->last();
                                    $isUrgent = $task && $task->priority == 'urgent';
                                @endphp
                                <div class="relative p-3 rounded-xl bg-white border border-gray-200 shadow-sm hover:shadow-md cursor-pointer transition-all duration-300 {{ $isUrgent ? 'border-r-4 border-r-red-500' : 'border-r-4 border-r-[#8A704C]' }}"
                                    draggable="true" ondragstart="drag(event)" id="order-{{ $order->id }}"
                                    data-order-id="{{ $order->id }}">

                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-xs font-bold text-gray-500">#{{ $order->order_number }}</span>
                                        <div class="flex gap-2 items-center">
                                            @if ($isUrgent)
                                                <span
                                                    class="text-[10px] font-bold bg-red-100 text-red-600 px-2 py-0.5 rounded-full">عاجل</span>
                                            @endif
                                            {{-- زر النقل اليدوي (مهم جداً للموبايل) --}}
                                            <button type="button"
                                                onclick="event.stopPropagation(); openMoveModal('{{ $order->id }}', '{{ $stage['id'] }}')"
                                                class="text-gray-500 hover:text-white hover:bg-[#8A704C] bg-gray-100 px-3 py-1.5 rounded-lg text-xs transition border border-gray-200 shadow-sm"
                                                title="نقل الأوردر">
                                                <i class="fa-solid fa-arrows-turn-right"></i> نقل
                                            </button>
                                        </div>
                                    </div>

                                    <div onclick="window.location='{{ route('orders.show', $order->id) }}'">
                                        <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $order->customer_name }}</h4>
                                        <p class="text-xs text-gray-500 mb-2 flex items-center gap-1">
                                            <i class="fa-solid fa-layer-group"></i> {{ $order->items->sum('quantity') }}
                                            قطعة
                                        </p>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- نافذة النقل (تسأل عن الموظف دائماً في مراحل التشغيل والتقفيل) --}}
    <div id="statusModal"
        class="fixed inset-0 bg-black/60 z-[60] hidden flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-4 bg-[#8A704C] text-white flex justify-between items-center">
                <h3 class="font-bold text-lg" id="statusModalTitle">تأكيد النقل</h3>
                <button onclick="closeStatusModal()" class="text-white/80 hover:text-white transition"><i
                        class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="changeStatusForm" class="p-5 space-y-4">
                <input type="hidden" id="modal_order_id">

                <div id="targetStageDiv" class="hidden">
                    <label class="block text-sm font-bold text-gray-700 mb-1">نقل إلى مرحلة:</label>
                    <select id="modal_to_status" onchange="toggleEmployeeField(this.value)"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none">
                        <option value="">اختر المرحلة...</option>
                        @foreach ($stages as $st)
                            <option value="{{ $st['id'] }}">{{ $st['title_ar'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- حقل الموظف --}}
                <div id="employeeSelectionDiv" class="hidden bg-blue-50 border border-blue-100 p-3 rounded-lg">
                    <label class="block text-sm font-bold text-blue-900 mb-1">
                        الموظف المنفذ (الذي قام بالعمل) <span class="text-red-500">*</span>
                    </label>
                    <select name="employee_id" id="employee_id"
                        class="w-full border border-blue-200 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 outline-none">
                        <option value="">اختر الموظف...</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">وقت التنفيذ الفعلي <span
                            class="text-red-500">*</span></label>
                    <input type="datetime-local" name="executed_at" id="executed_at" required
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none ltr-input"
                        dir="ltr">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">ملاحظات (اختياري)</label>
                    <textarea name="note" id="status_note" rows="2"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#8A704C] outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" onclick="cancelDrop()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition">إلغاء</button>
                    <button type="button" onclick="confirmStatusChange()"
                        class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> حفظ النقل
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toast Message (رسالة النجاح) --}}
    <div id="modernToast" class="fixed top-6 right-6 z-[99999] hidden">
        <div class="bg-white rounded-2xl shadow-2xl border border-green-200 overflow-hidden w-[360px]">
            <div class="h-1 bg-green-500" id="toastProgress"></div>
            <div class="p-5 flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-check text-green-600 text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 text-base">تم التنفيذ بنجاح</h3>
                    <p id="toastMessage" class="text-sm text-gray-600 mt-1 leading-6"></p>
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

        /* تأثيرات رسالة النجاح */
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
        document.addEventListener("DOMContentLoaded", function() {
            let now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('executed_at').value = now.toISOString().slice(0, 16);
        });

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        let draggedElement = null;
        let originalParent = null;
        let pendingDropData = {};

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

        function openMoveModal(orderId, currentStatus) {
            draggedElement = document.getElementById('order-' + orderId);
            originalParent = draggedElement.closest('.kanban-column');
            openStatusChangeModal(orderId, currentStatus, null, null, true);
        }

        function openStatusChangeModal(orderId, fromStatus, toStatus, dropZoneEl, isManualMove) {
            pendingDropData = {
                orderId,
                fromStatus,
                toStatus,
                dropZoneEl,
                isManualMove
            };

            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('changeStatusForm').reset();

            let now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('executed_at').value = now.toISOString().slice(0, 16);

            let targetStageDiv = document.getElementById('targetStageDiv');
            let targetStageSelect = document.getElementById('modal_to_status');

            if (isManualMove) {
                targetStageDiv.classList.remove('hidden');
                targetStageSelect.value = "";
                toggleEmployeeField("");
                document.getElementById('statusModalTitle').innerText = `نقل الطلب #${orderId}`;
            } else {
                targetStageDiv.classList.add('hidden');
                targetStageSelect.value = toStatus;
                toggleEmployeeField(toStatus);
                document.getElementById('statusModalTitle').innerText = `تأكيد النقل`;
            }

            document.getElementById('statusModal').classList.remove('hidden');
        }

        // الدالة المسؤولة عن إظهار حقل الموظف الإجباري للقص والخياطة والتقفيل
        function toggleEmployeeField(statusId) {
            let requiresEmployee = ['cutting', 'sewing', 'ready-to-ship'].includes(statusId);
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

            console.log('================ MOVE STATUS DEBUG ================');
            console.log('pendingDropData:', pendingDropData);

            let finalTargetStatus = pendingDropData.isManualMove ?
                document.getElementById('modal_to_status').value :
                pendingDropData.toStatus;

            let empId = document.getElementById('employee_id').value;
            let executedAt = document.getElementById('executed_at').value;
            let note = document.getElementById('status_note').value;

            console.log('Order ID:', pendingDropData.orderId);
            console.log('From Status:', pendingDropData.fromStatus);
            console.log('To Status:', finalTargetStatus);
            console.log('Employee ID:', empId);
            console.log('Executed At:', executedAt);
            console.log('Note:', note);

            if (!finalTargetStatus) {
                console.error('ERROR: No target status selected');
                alert('يجب اختيار المرحلة التي سيتم النقل إليها.');
                return;
            }

            let requiresEmployee = [
                'cutting',
                'sewing',
                'ready-to-ship'
            ].includes(finalTargetStatus);

            console.log('Requires Employee:', requiresEmployee);

            if (requiresEmployee && !empId) {
                console.error('ERROR: Employee is required for:', finalTargetStatus);
                alert('يجب اختيار الموظف المنفذ لهذه المرحلة.');
                return;
            }

            if (!executedAt) {
                console.error('ERROR: executed_at is empty');
                alert('يجب تحديد وقت التنفيذ.');
                return;
            }

            let payload = {
                to_status: finalTargetStatus,
                employee_id: empId || null,
                executed_at: executedAt,
                note: note || null
            };

            console.log('Sending payload:', payload);

            let url = `/production/workflow/order/${pendingDropData.orderId}/move`;

            console.log('POST URL:', url);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                })
                .then(async res => {

                    console.log('HTTP Status:', res.status);
                    console.log('HTTP OK:', res.ok);

                    let rawText = await res.text();

                    console.log('RAW SERVER RESPONSE:', rawText);

                    let data;

                    try {
                        data = JSON.parse(rawText);
                    } catch (e) {
                        console.error('SERVER DID NOT RETURN JSON');
                        console.error(rawText);

                        throw new Error(
                            `Server returned invalid response. HTTP ${res.status}`
                        );
                    }

                    if (!res.ok) {
                        console.error('SERVER ERROR DATA:', data);

                        throw new Error(
                            data.message ||
                            data.error ||
                            `HTTP Error ${res.status}`
                        );
                    }

                    return data;
                })
                .then(data => {

                    console.log('SUCCESS RESPONSE:', data);

                    if (data.success) {

                        let destColumn =
                            pendingDropData.dropZoneEl ||
                            document.querySelector(
                                `.kanban-column[data-status="${finalTargetStatus}"]`
                            );

                        console.log('Destination Column:', destColumn);
                        console.log('Dragged Element:', draggedElement);

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

                        console.error('Application returned success=false:', data);

                        alert(
                            data.message ||
                            'حدث خطأ أثناء نقل الطلب.'
                        );
                    }
                })
                .catch(err => {

                    console.error('================ MOVE STATUS FAILED ================');
                    console.error(err);

                    alert(
                        'حدث خطأ أثناء نقل الطلب.\n\n' +
                        err.message +
                        '\n\nافتح Console لمعرفة التفاصيل.'
                    );
                });
        }

        function showSuccessToast(user, order, stage) {
            const stagesText = {
                "new": "لم يتم البدء",
                "cutting": "القص",
                "sewing": "الخياطة",
                "ready-to-ship": "التقفيل"
            };

            let toast = document.getElementById("modernToast");
            let progress = document.getElementById("toastProgress");

            document.getElementById("toastMessage").innerHTML = `
            أهلاً <b>${user}</b> 👋<br>
            تم نقل الطلب <b>${order}</b> إلى مرحلة <span class="text-green-600 font-bold">${stagesText[stage]}</span>
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
                setTimeout(() => toast.classList.add("hidden"), 400);
            }, 4000);
        }
    </script>
@endpush
