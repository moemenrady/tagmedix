<meta name="csrf-token" content="{{ csrf_token() }}">

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1
        style="font-size: 2.2rem; font-weight: 800; background: linear-gradient(to left, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
        إدارة المصروفات
    </h1>

    <a href="#addExpenseModal" class="btn-primary" style="width:auto; padding:10px 20px; text-decoration:none;">
        <span>+</span> تسجيل مصروف
    </a>
</div>

{{-- GROUPED BY TYPE --}}
<div style="display: flex; flex-direction: column; gap: 2.5rem;">
    @foreach ($expenseTypes as $type)
        <div>
            <h3 style="color: var(--primary); margin-bottom: 1rem; display:flex; align-items:center; gap:10px;">
                <span style="width:8px;height:25px;background:var(--primary);border-radius:4px;"></span>
                {{ $type->name }}
            </h3>

            <div style="display:flex; flex-direction:column; gap:0.8rem;">

                @forelse($type->expenses()->latest()->get() as $expense)
                    <div
                        style="background: var(--card-bg); border:1px solid var(--glass-border); padding:1.2rem; border-radius:15px; display:flex; justify-content:space-between; align-items:center;">

                        <div>
                            <strong style="color:#fff; font-size:1.1rem;">
                                {{ number_format($expense->amount, 2) }} ج.م
                            </strong>

                            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:4px;">
                                {{ $expense->note ?? '-' }}
                            </p>

                            <small style="color:var(--accent);">
                                بواسطة: {{ $expense->user->name ?? 'Admin' }}
                                | {{ $expense->created_at->diffForHumans() }}
                            </small>
                        </div>

                        <div style="display:flex; gap:8px;">

                            {{-- EDIT --}}
                            <a href="#editExpenseModal{{ $expense->id }}"
                                style="padding:8px; border-radius:8px; background:rgba(255,255,255,0.05); color:var(--text-muted); text-decoration:none;">
                                📝
                            </a>

                            {{-- DELETE --}}
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST"
                                class="ajax-form" onsubmit="return confirm('هل أنت متأكد؟')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    style="padding:8px; border-radius:8px; background:rgba(255,107,107,0.1); color:#ff6b6b; border:none; cursor:pointer;">
                                    🗑️
                                </button>
                            </form>

                        </div>
                    </div>

                    {{-- EDIT MODAL --}}
                    <div id="editExpenseModal{{ $expense->id }}" class="glass-modal-overlay-simple">
                        <div class="glass-modal">
                            <h3>تعديل مصروف</h3>

                            <form action="{{ route('expenses.update', $expense->id) }}" method="POST"
                                class="ajax-form">

                                @csrf
                                @method('PUT')

                                <select name="expense_type_id" class="glass-input" required>
                                    @foreach ($expenseTypes as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $expense->expense_type_id == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="number" name="amount" value="{{ $expense->amount }}" class="glass-input"
                                    step="0.01" required>

                                <textarea name="note" class="glass-input">{{ $expense->note }}</textarea>

                                <div class="modal-actions">
                                    <a href="#" class="btn-cancel">إلغاء</a>
                                    <button type="submit" class="btn-primary">تحديث</button>
                                </div>

                            </form>
                        </div>
                    </div>

                @empty
                    <p style="color:var(--text-muted); padding-right:20px;">
                        لا يوجد مصاريف لهذا النوع
                    </p>
                @endforelse

            </div>
        </div>
    @endforeach
</div>

{{-- ADD MODAL --}}
<div id="addExpenseModal" class="glass-modal-overlay-simple">
    <div class="glass-modal">
        <h3>تسجيل مصروف جديد</h3>

        <form id="addExpenseForm" action="{{ route('expenses.store') }}" method="POST" class="ajax-form">

            @csrf

            <select name="expense_type_id" class="glass-input" required>
                <option value="">اختر النوع</option>
                @foreach ($expenseTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>

            <input type="number" name="amount" class="glass-input" placeholder="المبلغ" step="0.01" required>

            <textarea name="note" class="glass-input" placeholder="ملاحظات"></textarea>

            <div class="modal-actions">
                <a href="#" class="btn-cancel">إلغاء</a>
                <button type="submit" class="btn-primary">حفظ</button>
            </div>

        </form>
    </div>
</div>
