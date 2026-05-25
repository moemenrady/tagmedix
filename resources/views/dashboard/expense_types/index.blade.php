@extends('layouts.app')

@section('content')
    <style>
        .types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 2rem;
        }

        .type-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .type-card:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .type-card h4 {
            color: var(--text-main);
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .type-card .total-spent {
            color: #ff4757;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .type-card .label {
            color: var(--text-muted);
            font-size: 0.8rem;
            display: block;
        }

        .type-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            border-top: 1px solid var(--glass-border);
            padding-top: 15px;
        }

        .btn-add-type {
            background: var(--primary);
            color: #000;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        /* مودال */
        .glass-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;

            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(6px);

            display: none;
            /* مهم جدا */
            align-items: center;
            justify-content: center;

            z-index: 9999;
        }

        .glass-modal-overlay.active {
            display: flex;
        }

        .glass-modal {
            background: #111827;
            padding: 25px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: scaleIn 0.3s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* inputs */
        .glass-input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #0f172a;
            color: #fff;
            margin-bottom: 15px;
        }

        /* buttons */
        .modal-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .btn-primary {
            background: #22c55e;
            color: #000;
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .btn-cancel {
            background: #ef4444;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        /* buttons icons */
        .btn-icon {
            background: rgba(255, 255, 255, 0.05);
            border: none;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
            font-size: 0.9rem;
        }

        .btn-icon.delete {
            background: rgba(255, 0, 0, 0.2);
        }
    </style>

    <div class="capital-container">
        <div class="transactions-header">
            <h3>أنواع المصاريف</h3>
            <button class="btn-add-type" onclick="openModal('addTypeModal')">+ إضافة نوع جديد</button>
        </div>

        <div class="types-grid">
            @foreach ($expenseTypes as $type)
                @php
                    // حساب إجمالي المصاريف لهذا النوع
                    $totalSpent = $type->transactions->sum('amount');
                @endphp
                <div class="type-card"
                    onclick="window.location='{{ route('transactions.index', ['expense_type_id' => $type->id]) }}'">
                    <span class="label">اسم النوع</span>
                    <h4>{{ $type->name }}</h4>

                    <span class="label">إجمالي المنصرف</span>
                    <div class="total-spent">{{ number_format($totalSpent, 2) }} ج.م</div>

                    <div class="type-actions">
                        <button class="btn-icon"
                            onclick="event.stopPropagation(); editType('{{ $type->id }}', '{{ $type->name }}', '{{ $type->setter_name }}')"
                            >✏️
                            تعديل</button>
                            <form action="{{ route('expense-types.destroy', $type->id) }}" method="POST"
                                onsubmit="return confirm('حذف النوع سيحذف كافة مصاريفه المرتبطة! هل أنت متأكد؟');"
                                onclick="event.stopPropagation();">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon delete">🗑️ حذف</button>
                            </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="glass-modal-overlay" id="addTypeModal">
        <div class="glass-modal">
            <h3>🆕 إضافة نوع مصروف</h3>
            <form action="{{ route('expense-types.store') }}" method="POST">
                @csrf
                <input type="text" name="name" class="glass-input" placeholder="مثال: إيجار، رواتب، صيانة..."
                    required>
                <input type="text" name="setter_name" class="glass-input" placeholder="اسم المُدخل" required>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('addTypeModal')">إلغاء</button>
                    <button type="submit" class="btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>

    <div class="glass-modal-overlay" id="editTypeModal">
        <div class="glass-modal">
            <h3>✏️ تعديل النوع</h3>
            <form id="editTypeForm" method="POST">
                @csrf @method('PUT')
                <input type="text" name="name" id="edit-type-name" class="glass-input" required>
                <input type="text" name="setter_name" id="edit-setter-name" class="glass-input" required>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('editTypeModal')">إلغاء</button>
                    <button type="submit" class="btn-primary">تحديث</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editType(id, name, setterName) {
            const form = document.getElementById('editTypeForm');
            form.action = `/expense-types/${id}`;

            document.getElementById('edit-type-name').value = name;
            document.getElementById('edit-setter-name').value = setterName;

            openModal('editTypeModal');
        }
    </script>
    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        /* قفل لما تدوس برا */
        document.querySelectorAll('.glass-modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>
@endsection
