@extends('layouts.app')

@section('content')
    <style>
        :root {
            --text-main: #ffffff;
            --text-muted: #9ca3af;
            --glass-border: rgba(255, 255, 255, 0.1);
            --primary: #00f2fe;
            --success: #2ed573;
            --danger: #ff4757;
        }

        /* ====== تنسيقات صفحة رأس المال ====== */
        .capital-container {
            max-width: 900px;
            margin: 0 auto;
            animation: fadeIn 0.6s ease forwards;
            direction: rtl;
        }

        /* الهيدر والرقم الكبير */
        .capital-hero {
            background: linear-gradient(145deg, rgba(20, 25, 35, 0.6), rgba(10, 12, 18, 0.4));
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .capital-hero::before {
            content: "";
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(0, 242, 254, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
        }

        /* تأثير عبارة اللهم بارك (Typing Effect) */
        .typing-container {
            display: inline-block;
            margin-bottom: 15px;
            padding: 5px 15px;
            background: rgba(0, 242, 254, 0.1);
            border: 1px solid rgba(0, 242, 254, 0.3);
            border-radius: 20px;
        }

        .typing-text {
            color: var(--primary);
            font-size: 1.1rem;
            font-weight: bold;
            border-left: 2px solid var(--primary);
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
            animation: blink-caret 0.75s step-end infinite;
        }

        @keyframes blink-caret {

            from,
            to {
                border-color: transparent
            }

            50% {
                border-color: var(--primary);
            }
        }

        .capital-label {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .capital-amount {
            font-size: 4rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 20px rgba(0, 242, 254, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .currency {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 500;
        }

        /* الأزرار */
        .btn-add-funds,
        .btn-add-expense {
            color: #fff;
            border: none;
            padding: 14px 30px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-add-funds {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: #111;
            box-shadow: 0 10px 20px rgba(150, 201, 61, 0.3);
        }

        .btn-add-expense {
            background: linear-gradient(135deg, #ff4757, #ff6b81);
            box-shadow: 0 10px 20px rgba(255, 71, 87, 0.3);
        }

        .btn-add-funds:hover,
        .btn-add-expense:hover {
            transform: translateY(-3px) scale(1.05);
        }

        /* تنسيقات قسم الفلتر */
        .filter-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 20px;
            /* مسافة بين الفلتر والمربعات */
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 0.85rem;
            color: var(--text-muted);
            padding-right: 5px;
        }

        .filter-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 10px;
            color: #fff;
            outline: none;
            font-size: 0.9rem;
        }

        .filter-input:focus {
            border-color: var(--primary);
        }

        .btn-filter-submit {
            background: var(--primary);
            color: #000;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-filter-reset {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px;
            text-decoration: none;
            text-align: center;
            font-size: 0.9rem;
        }

        /* ====== مربعات الإحصائيات (تتأثر بالفلتر) ====== */
        .summary-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            margin-top: 20px;
            border-top: 1px dashed var(--glass-border);
            padding-top: 20px;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-radius: 16px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            transition: transform 0.3s ease, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.gain {
            border-bottom: 3px solid var(--success);
        }

        .stat-card.gain:hover {
            box-shadow: 0 10px 20px rgba(46, 213, 115, 0.15);
        }

        .stat-card.loss {
            border-bottom: 3px solid var(--danger);
        }

        .stat-card.loss:hover {
            box-shadow: 0 10px 20px rgba(255, 71, 87, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .gain .stat-icon {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success);
        }

        .loss .stat-icon {
            background: rgba(255, 71, 87, 0.1);
            color: var(--danger);
        }

        .stat-content h4 {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .stat-content h2 {
            margin: 5px 0 0 0;
            font-size: 1.5rem;
            color: #fff;
        }

        /* استكمال التنسيقات القديمة للحركات والمودال... */
        .transactions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 0 10px;
        }

        .transactions-header h3 {
            font-size: 1.5rem;
            color: var(--text-main);
        }

        .tx-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .tx-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.3s;
        }

        .tx-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(-5px);
        }

        .tx-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .tx-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .tx-gain .tx-icon {
            background: rgba(46, 213, 115, 0.1);
            color: var(--success);
        }

        .tx-loss .tx-icon {
            background: rgba(255, 71, 87, 0.1);
            color: var(--danger);
        }

        .tx-details h4 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .tx-details p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .tx-amount {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .tx-gain .tx-amount {
            color: var(--success);
        }

        .tx-loss .tx-amount {
            color: var(--danger);
        }

        .tx-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-icon {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-icon:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .btn-icon.delete:hover {
            background: rgba(255, 71, 87, 0.2);
            color: var(--danger);
        }

        /* Modal Styles */
        .glass-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .glass-modal-overlay.active {
            display: flex;
        }

        .glass-modal {
            background: #111827;
            border-radius: 20px;
            padding: 25px;
            width: 90%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.3s ease;
        }

        .glass-input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            outline: none;
        }

        .glass-input:focus {
            border-color: var(--primary);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-primary {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background: #3b82f6;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background: #374151;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }

        /* Modal Balance Stats */
        .balance-stats {
            display: flex;
            justify-content: space-between;
            background: rgba(0, 0, 0, 0.3);
            padding: 15px;
            border-radius: 12px;
            margin: 15px 0;
            border: 1px dashed var(--glass-border);
        }

        .stat-box {
            text-align: center;
            flex: 1;
        }

        .stat-box h5 {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .stat-box p {
            font-size: 1.1rem;
            font-weight: bold;
            color: #fff;
            margin: 0;
        }

        .stat-divider {
            width: 1px;
            background: var(--glass-border);
            margin: 0 15px;
        }

        @media (max-width: 768px) {
            .capital-amount {
                font-size: 2.8rem;
            }

            .btn-add-funds,
            .btn-add-expense {
                width: 100%;
                justify-content: center;
            }

            .tx-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .tx-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>

    <div class="capital-container">

        <div class="capital-hero">
            <div class="typing-container">
                <span class="typing-text" id="typing-effect"></span>
            </div>

            <p class="capital-label">إجمالي رأس المال الحالي</p>
            <h1 class="balance-amount">{{ number_format($totalCapital ?? 0, 2) }} <span class="currency">ج.م</span></h1>

            <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                <button class="btn-add-funds" onclick="openModal('addCapitalModal')">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4" />
                    </svg>
                    إضافة نقود
                </button>
                <button class="btn-add-expense" onclick="openModal('addExpenseModal')">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M20 12H4" />
                    </svg>
                    إضافة مصروف
                </button>
            </div>
        </div>

        <div class="filter-card">
            <form action="{{ route('transactions.index') }}" method="GET" class="filter-grid">
                <div class="filter-group">
                    <label>بحث في الملاحظات</label>
                    <input type="text" name="note" value="{{ request('note') }}" class="filter-input"
                        placeholder="اكتب للبحث...">
                </div>
                <div class="filter-group">
                    <label>نوع الحركة</label>
                    <select name="type" class="filter-input">
                        <option value="">الكل</option>
                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>إضافة نقود فقط</option>
                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>مصاريف فقط</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>تصنيف المصروف</label>
                    <select name="expense_type_id" class="filter-input">
                        <option value="">كل الأنواع</option>
                        @foreach ($expenseTypes ?? [] as $type)
                            <option value="{{ $type->id }}"
                                {{ request('expense_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>من تاريخ</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="filter-input">
                </div>
                <div class="filter-group" style="flex-direction: row;">
                    <button type="submit" class="btn-filter-submit" style="flex: 2;">بحث</button>
                    <a href="{{ route('transactions.index') }}" class="btn-filter-reset" style="flex: 1;">❌</a>
                </div>
            </form>

            <div class="summary-stats-grid">
                <div class="stat-card gain" style="cursor: pointer;" onclick="openModal('groupedCapitalModal')">
                    <div class="stat-icon">📈</div>
                    <div class="stat-content">
                        <h4>إجمالي الإضافات (لنتائج البحث)</h4>
                        <h2>{{ number_format($filteredCapitalAdded ?? 0, 2) }} <span
                                style="font-size: 1rem; color:var(--text-muted)">ج.م</span></h2>
                    </div>
                </div>
                <div class="stat-card loss">
                    <div class="stat-icon">📉</div>
                    <div class="stat-content">
                        <h4>إجمالي المصروفات (لنتائج البحث)</h4>
                        <h2>{{ number_format($filteredExpenses ?? 0, 2) }} <span
                                style="font-size: 1rem; color:var(--text-muted)">ج.م</span></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="transactions-header">
            <h3>سجل الحركات</h3>
        </div>

        <div class="tx-list">
            @forelse($transactions ?? [] as $tx)
                @if ($tx->type == 'in')
                    <div class="tx-item tx-gain"
                        onclick="viewCapitalModal('{{ $tx->amount }}', '{{ $tx->note }}', '{{ $tx->created_at->format('Y-m-d') }}', '{{ $tx->balance_before ?? 0 }}', '{{ $tx->balance_after ?? 0 }}')">
                        <div class="tx-info">
                            <div class="tx-icon">⬆️</div>
                            <div class="tx-details">
                                <h4>زيادة رأس المال</h4>
                                {{ $tx->created_at->format('d M Y') }} - {{ Str::limit($tx->note, 20) }}
                                {{ $tx->partner->name ?? '—' }}
                            </div>
                        </div>
                        <div class="tx-actions">
                            <div class="tx-amount">+ {{ number_format($tx->amount, 2) }} ج.م</div>
                            <button class="btn-icon"
                                onclick="event.stopPropagation(); editTx('{{ $tx->id }}', '{{ $tx->amount }}', '{{ $tx->note }}', 'capital')">✏️</button>
                            <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST"
                                onsubmit="return confirm('تأكيد الحذف؟');" style="display:inline;"
                                onclick="event.stopPropagation();">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon delete">🗑️</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="tx-item tx-loss"
                        onclick="viewExpenseModal('{{ $tx->amount }}', '{{ $tx->expense->name ?? 'مصروف عام' }}', '{{ $tx->note }}', '{{ $tx->created_at->format('Y-m-d') }}')">
                        <div class="tx-info">
                            <div class="tx-icon">⬇️</div>
                            <div class="tx-details">
                                <h4>{{ $tx->expenseType->name ?? 'مصروف' }}</h4>
                                <p>{{ $tx->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="tx-actions">
                            <div class="tx-amount">- {{ number_format($tx->amount, 2) }} ج.م</div>
                            <button class="btn-icon"
                                onclick="event.stopPropagation(); editTx('{{ $tx->id }}', '{{ $tx->amount }}', '{{ $tx->note }}', 'expense')">✏️</button>
                            <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST"
                                onsubmit="return confirm('تأكيد الحذف؟');" style="display:inline;"
                                onclick="event.stopPropagation();">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon delete">🗑️</button>
                            </form>
                        </div>
                    </div>
                @endif
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 2rem;">لا توجد حركات مطابقة للبحث حتى
                    الآن.</div>
            @endforelse
        </div>
    </div>

    <div class="glass-modal-overlay" id="addCapitalModal">

        <div class="glass-modal">

            <h3 style="color: #2ed573;">💰 زيادة رأس المال</h3>

            <form action="{{ route('transactions.store') }}" method="POST" class="ajax-form">

                @csrf

                <input type="hidden" name="type" value="in">



                <input type="number" step="0.01" name="amount" class="glass-input"
                    placeholder="المبلغ (مثال: 5000)" required>
                <select name="partner_id" class="glass-input" required>
                    <option value="">اختر الشريك</option>
                    @foreach ($partners as $partner)
                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                    @endforeach
                </select>
                <textarea name="note" class="glass-input" rows="3" placeholder="ملاحظات (اختياري)..."></textarea>



                <div class="modal-actions">

                    <button type="button" class="btn-cancel" onclick="closeModal('addCapitalModal')">إلغاء</button>

                    <button type="submit" class="btn-primary" style="background: #2ed573; color: #111;">إضافة

                        للمحفظة</button>

                </div>

            </form>

        </div>

    </div>



    <div class="glass-modal-overlay" id="viewCapitalModal">

        <div class="glass-modal">

            <h3 style="color: #2ed573;">⬆️ تفاصيل زيادة رأس المال</h3>

            <div style="text-align: center; margin-bottom: 20px;">

                <h2 id="vc-amount" style="color: #2ed573; font-size: 2.5rem; margin:0;">0 ج.م</h2>

                <p id="vc-date" style="color: var(--text-muted); font-size: 0.9rem;">التاريخ</p>

            </div>



            <div class="balance-stats">

                <div class="stat-box">

                    <h5>الرصيد قبله</h5>

                    <p id="vc-before">0</p>

                </div>

                <div class="stat-divider"></div>

                <div class="stat-box">

                    <h5>الرصيد بعده</h5>

                    <p id="vc-after" style="color: #2ed573;">0</p>

                </div>

            </div>



            <p style="color: var(--text-main); font-size: 1rem; line-height: 1.6;" id="vc-note"></p>



            <div class="modal-actions">

                <button type="button" class="btn-cancel" style="width: 100%;"
                    onclick="closeModal('viewCapitalModal')">إغلاق</button>

            </div>

        </div>

    </div>

    <div class="glass-modal-overlay" id="addExpenseModal">

        <div class="glass-modal">

            <h3 style="color: #ff4757;">💸 إضافة مصروف</h3>



            <form action="{{ route('transactions.store') }}" method="POST">

                @csrf

                <input type="hidden" name="type" value="out">



                <input type="number" step="0.01" name="amount" class="glass-input" placeholder="قيمة المصروف"
                    required>



                {{-- نوع المصروف --}}

                <select name="expense_type_id" class="glass-input" required>

                    <option value="">اختر نوع المصروف</option>

                    @foreach ($expenseTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach

                </select>



                <textarea name="note" class="glass-input" rows="3" placeholder="ملاحظات (اختياري)..."></textarea>



                <div class="modal-actions">

                    <button type="button" class="btn-cancel" onclick="closeModal('addExpenseModal')">إلغاء</button>



                    <button type="submit" class="btn-primary" style="background: #ff4757;">حفظ المصروف</button>

                </div>

            </form>

        </div>

    </div>

    <div class="glass-modal-overlay" id="viewExpenseModal">

        <div class="glass-modal">

            <h3 style="color: #ff4757;">⬇️ تفاصيل المصروف</h3>

            <div style="text-align: center; margin-bottom: 20px;">

                <h2 id="ve-amount" style="color: #ff4757; font-size: 2.5rem; margin:0;">0 ج.م</h2>

                <p id="ve-type" style="color: var(--text-main); font-size: 1.1rem; font-weight:bold;">نوع المصروف</p>

                <p id="ve-date" style="color: var(--text-muted); font-size: 0.9rem;">التاريخ</p>

            </div>



            <div
                style="background: rgba(255, 71, 87, 0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(255, 71, 87, 0.2);">

                <p style="color: var(--text-main); font-size: 1rem; line-height: 1.6; margin:0;" id="ve-note"></p>

            </div>



            <div class="modal-actions">

                <button type="button" class="btn-cancel" style="width: 100%;"
                    onclick="closeModal('viewExpenseModal')">إغلاق</button>

            </div>

        </div>

    </div>

    <div class="glass-modal-overlay" id="groupedCapitalModal">
        <div class="glass-modal" style="max-width: 500px; max-height: 80vh; overflow-y: auto;">
            <h3 style="color: #2ed573; text-align: center; margin-bottom: 20px;">👥 تفاصيل الإضافات حسب الشريك</h3>

            @forelse($groupedCapitalTransactions ?? [] as $partnerName => $txs)
                @php
                    $partnerTotal = $txs->sum('amount');
                @endphp
                <div
                    style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; margin-bottom: 15px; padding: 15px; border: 1px solid var(--glass-border);">

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(255,255,255,0.2); padding-bottom: 10px; margin-bottom: 10px;">
                        <h4 style="color: var(--primary); margin: 0; font-size: 1.1rem;">{{ $partnerName }}</h4>
                        <span
                            style="color: #2ed573; font-weight: bold; font-size: 1.1rem;">{{ number_format($partnerTotal, 2) }}
                            ج.م</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach ($txs as $tx)
                            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; cursor: pointer; transition: all 0.3s;"
                                onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateX(-5px)';"
                                onmouseout="this.style.background='rgba(0,0,0,0.2)'; this.style.transform='translateX(0)';"
                                onclick="viewCapitalModal('{{ $tx->amount }}', '{{ addslashes($tx->note) }}', '{{ $tx->created_at->format('Y-m-d') }}', '{{ $tx->balance_before ?? 0 }}', '{{ $tx->balance_after ?? 0 }}')">

                                <div style="font-size: 0.9rem; color: var(--text-muted);">
                                    {{ $tx->created_at->format('d M Y') }}
                                </div>
                                <div style="font-weight: bold; font-size: 1rem; color: #fff;">
                                    + {{ number_format($tx->amount, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 2rem 0;">
                    لا توجد إضافات رأس مال في نتائج البحث الحالية.
                </div>
            @endforelse

            <div class="modal-actions" style="margin-top: 20px;">
                <button type="button" class="btn-cancel" style="width: 100%; background: #374151;"
                    onclick="closeModal('groupedCapitalModal')">إغلاق</button>
            </div>
        </div>
    </div>

    <div class="glass-modal-overlay" id="editTxModal">

        <div class="glass-modal">

            <h3>✏️ تعديل الحركة</h3>

            <form id="editTxForm" method="POST" class="ajax-form">

                @csrf

                @method('PUT')



                <input type="number" step="0.01" name="amount" id="edit-amount" class="glass-input" required>

                <textarea name="note" id="edit-note" class="glass-input" rows="3"></textarea>



                <div class="modal-actions">

                    <button type="button" class="btn-cancel" onclick="closeModal('editTxModal')">إلغاء</button>

                    <button type="submit" class="btn-primary">حفظ التعديلات</button>

                </div>

            </form>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // سكربت الـ Typing Effect (للغة العربية من اليمين لليسار)
        document.addEventListener('DOMContentLoaded', function() {
            const text = "اللهم زد و بارك  ✦";
            const speed = 100; // سرعة الكتابة بالملي ثانية
            let i = 0;
            const element = document.getElementById('typing-effect');

            function typeWriter() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(typeWriter, speed);
                }
            }
            // تشغيل التأثير بعد نصف ثانية من تحميل الصفحة
            setTimeout(typeWriter, 500);
        });

        function viewCapitalModal(amount, note, date, before, after) {
            document.getElementById('vc-amount').innerText = amount + ' ج.م';
            document.getElementById('vc-date').innerText = date;
            document.getElementById('vc-before').innerText = before + ' ج.م';
            document.getElementById('vc-after').innerText = after + ' ج.م';
            document.getElementById('vc-note').innerText = note || 'لا توجد ملاحظات';

            openModal('viewCapitalModal');
        }

        function viewExpenseModal(amount, type, note, date) {
            document.getElementById('ve-amount').innerText = amount + ' ج.م';
            document.getElementById('ve-type').innerText = type;
            document.getElementById('ve-date').innerText = date;
            document.getElementById('ve-note').innerText = note || 'لا توجد ملاحظات';

            openModal('viewExpenseModal');
        }

        function editTx(id, amount, note, type) {
            document.getElementById('edit-amount').value = amount;
            document.getElementById('edit-note').value = note;

            const form = document.getElementById('editTxForm');
            form.action = `/transactions/${id}`;

            openModal('editTxModal');
        }

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
    </script>
@endpush
