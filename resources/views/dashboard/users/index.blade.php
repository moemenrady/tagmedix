@extends('layouts.app')

@section('content')
    <style>
        /* --- التنسيقات الأساسية --- */
        .dashboard-header {
            text-align: center;
            margin-bottom: 2.5rem;
            animation: fadeInDown 0.8s ease forwards;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            padding: 0 15px;
        }

        .dashboard-header h1 {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, var(--text-main, #fff), var(--primary, #00f2fe));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            color: var(--text-muted, #a0aabf);
            font-size: 1.1rem;
        }

        /* --- تنسيقات الحاويات العامة --- */
        .section-container {
            max-width: 1200px;
            margin: 0 auto 3rem auto;
            padding: 0 15px;
            animation: fadeInUp 0.8s ease forwards;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-align: right;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- نظام الكروت (Cards Grid) --- */
        .cards-grid {
            display: grid;
            /* كروت متجاوبة: عرض الكرت لا يقل عن 300 بكسل، ويملأ المساحة المتاحة */
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        /* --- تنسيق الكرت الزجاجي --- */
        .glass-card {
            background: linear-gradient(145deg, rgba(30, 35, 45, 0.6), rgba(15, 20, 28, 0.4));
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1));
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 242, 254, 0.15);
            border-color: rgba(0, 242, 254, 0.3);
        }

        /* رأس الكرت (الاسم والحالة) */
        .card-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 10px;
        }

        .card-header-top h4 {
            margin: 0;
            color: #fff;
            font-size: 1.25rem;
            font-weight: bold;
        }

        /* تفاصيل الكرت (الصفوف الداخلية) */
        .card-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .card-info-label {
            color: var(--text-muted, #a0aabf);
        }

        .card-info-value {
            color: #fff;
            font-weight: 500;
        }

        /* تذييل الكرت (الأزرار) */
        .card-actions {
            margin-top: auto;
            /* لدفع الأزرار للأسفل دائماً */
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* --- حالة الحساب (البادج) --- */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-active {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.4);
        }

        .badge-blocked {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.4);
        }

        /* --- الأزرار --- */
        .buttons-group {
            display: flex;
            gap: 10px;
        }

        .btn-add-account,
        .btn-add-partner {
            border: none;
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-add-account {
            background: linear-gradient(135deg, #00f2fe, #4facfe);
            color: #000;
        }

        .btn-add-account:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 242, 254, 0.4);
        }

        .btn-add-partner {
            background: linear-gradient(135deg, #f2a65a, #f58529);
            color: #fff;
        }

        .btn-add-partner:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(245, 133, 41, 0.4);
        }

        /* أزرار الكروت المصغرة */
        .btn-action {
            flex: 1;
            /* لتأخذ الأزرار مساحة متساوية داخل الكرت */
            padding: 10px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .btn-edit {
            background: rgba(23, 162, 184, 0.8);
            border: 1px solid rgba(23, 162, 184, 0.4);
        }

        .btn-edit:hover {
            background: #17a2b8;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(23, 162, 184, 0.3);
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.8);
            border: 1px solid rgba(220, 53, 69, 0.4);
        }

        .btn-delete:hover {
            background: #dc3545;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }

        .btn-block-action {
            width: 100%;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.8), rgba(200, 35, 51, 0.8));
            border: 1px solid rgba(220, 53, 69, 0.5);
        }

        .btn-block-action:hover:not(:disabled) {
            background: linear-gradient(135deg, #dc3545, #c82333);
            transform: translateY(-2px);
        }

        .btn-block-action:disabled {
            background: rgba(108, 117, 125, 0.3);
            border-color: rgba(108, 117, 125, 0.2);
            color: #adb5bd;
            cursor: not-allowed;
        }

        /* رسالة حالة فارغة */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            border: 1px dashed rgba(255, 255, 255, 0.1);
            color: #a0aabf;
        }

        /* --- تنسيقات الـ Modal (تبقى كما هي) --- */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: linear-gradient(145deg, rgba(30, 35, 45, 0.95), rgba(15, 20, 28, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            color: white;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .close-btn {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #fff;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: right;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #00f2fe;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            outline: none;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #00f2fe;
            box-shadow: 0 0 10px rgba(0, 242, 254, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00f2fe, #4facfe);
            border: none;
            border-radius: 10px;
            color: #000;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            box-shadow: 0 8px 20px rgba(0, 242, 254, 0.4);
            transform: translateY(-2px);
        }

        /* --- Animations --- */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Media Queries للموبايل --- */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .buttons-group {
                flex-direction: column;
                width: 100%;
                margin-top: 15px;
            }

            .btn-add-account,
            .btn-add-partner {
                width: 100%;
            }

            .section-title {
                justify-content: center;
            }
        }

        /* --- تصميم قسم توزيع رأس المال (المخفي) --- */
        #capitalCalculationSection {
            display: none;
            /* مخفي افتراضياً */
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #capitalCalculationSection.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        /* كارت عرض رأس المال الحالي */
        .current-capital-badge {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            padding: 10px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #28a745;
            font-weight: bold;
        }

        /* زر إعادة الحساب */
        .btn-toggle-calc {
            background: rgba(0, 242, 254, 0.1);
            color: #00f2fe;
            border: 1px solid rgba(0, 242, 254, 0.4);
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-toggle-calc:hover {
            background: #00f2fe;
            color: #000;
        }

        .btn-toggle-calc.active {
            background: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }

        /* --- الأزرار العامة --- */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00f2fe, #4facfe);
            border: none;
            border-radius: 12px;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 242, 254, 0.4);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        /* الموبايل */
        @media (max-width: 768px) {
            .section-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .current-capital-badge {
                width: 100%;
                justify-content: center;
            }

            .btn-toggle-calc {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="dashboard-header">
        <div class="header-content">
            <div>
                <h1>إدارة النظام والشركاء</h1>
                <p>إدارة كافة الحسابات، الصلاحيات، وحصص الشركاء</p>
            </div>

            <div class="buttons-group">
                <button onclick="openModal('addPartnerModal')" class="btn-add-partner">
                    🤝 إضافة شريك
                </button>
                <a href="{{ route('register') }}" class="btn-add-account">
                    ➕ إنشاء حساب جديد
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div
            style="background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.4); padding: 15px; border-radius: 15px; max-width: 1200px; margin: 0 auto 1.5rem auto; text-align: center; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    <div class="section-container">
        <h3 class="section-title" style="color: #00f2fe;">👥 حسابات النظام</h3>

        <div class="cards-grid">
            @foreach ($accounts as $account)
                <div class="glass-card">
                    <div class="card-header-top">
                        <h4>{{ $account->name }}</h4>
                        @if ($account->email_verified_at)
                            <span class="badge badge-active">نشط</span>
                        @else
                            <span class="badge badge-blocked">محظور</span>
                        @endif
                    </div>

                    <div class="card-info-row">
                        <span class="card-info-label">البريد الإلكتروني:</span>
                        <span class="card-info-value" dir="ltr" style="font-size: 0.9rem;">{{ $account->email }}</span>
                    </div>
                    <div class="card-info-row">
                        <span class="card-info-label">الصلاحية:</span>
                        <span class="card-info-value">{{ $account->role === 'admin' ? 'مدير نظام' : 'مستخدم عادي' }}</span>
                    </div>
                    <div class="card-info-row">
                        <span class="card-info-label">تاريخ الانضمام:</span>
                        <span class="card-info-value" dir="ltr">{{ $account->created_at->format('Y-m-d') }}</span>
                    </div>

                    <div class="card-actions">
                        @if (auth()->user()->role === 'admin' && auth()->id() !== $account->id)
                            <form action="{{ route('system-accounts.block', $account->id) }}" method="POST"
                                style="width: 100%;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-action btn-block-action"
                                    onclick="return confirm('هل أنت متأكد من حظر هذا الحساب؟')"
                                    {{ is_null($account->email_verified_at) ? 'disabled' : '' }}>
                                    {{ is_null($account->email_verified_at) ? '🚫 تم الحظر' : '⛔ حظر الحساب' }}
                                </button>
                            </form>
                        @else
                            <div style="text-align: center; width: 100%; color: var(--text-muted); font-size: 0.9rem;">
                                لا توجد إجراءات متاحة
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="section-container">
        <div class="section-title">
            <div style="display: flex; align-items: center; gap: 10px; color: #00f2fe;">
                <span>💰 توزيع رأس المال</span>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <div class="current-capital-badge">
                    <span>رأس المال الحالي:</span>
                    <span style="font-size: 1.2rem; margin-right:5px;">{{ number_format($total_capital ?? 0, 2) }}</span>
                </div>

                <button onclick="toggleCalculation()" id="calcToggleBtn" class="btn-toggle-calc">
                    <span id="btnIcon">🔄</span> <span id="btnText">إعادة حساب النسب</span>
                </button>
            </div>
        </div>

        <div id="capitalCalculationSection" style="display: none;">
            <form action="{{ route('partners.calculate') }}" method="POST" id="calcForm">
                @csrf

                <div class="glass-card" style="margin-bottom:20px; border-right: 4px solid #f58529;">
                    <label style="color: #f58529; font-weight: bold; display: block; margin-bottom: 15px;">طريقة الحساب
                        المفضلة:</label>
                    <div style="display: flex; gap: 20px;">
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px; color: #fff;">
                            <input type="radio" name="calculation_type" value="amount" checked
                                onchange="updateUI('amount')">
                            إدخال مبالغ (حساب النسب)
                        </label>
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 8px; color: #fff;">
                            <input type="radio" name="calculation_type" value="percentage"
                                onchange="updateUI('percentage')">
                            إدخال نسب (حساب مبالغ)
                        </label>
                    </div>
                </div>

                <div class="glass-card" style="margin-bottom:20px; border-left: 4px solid #00f2fe;">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <label style="color: #00f2fe; font-weight: bold;">إجمالي رأس المال الجديد</label>
                        <input type="number" name="total" class="form-control" placeholder="مثال: 500000" required
                            step="any">
                    </div>
                </div>

                <div class="cards-grid">
                    @foreach ($partners as $partner)
                        <div class="glass-card">
                            <h4 style="color: #fff; margin-bottom: 15px;">{{ $partner->name }}</h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <label class="input-label"
                                    style="color: var(--text-muted, #a0aabf); font-size: 0.9rem;">المبلغ المدفوع</label>
                                <div style="position: relative;">
                                    <input type="number" name="values[{{ $partner->id }}]"
                                        class="form-control partner-input" placeholder="0.00" required step="any">
                                    <span class="unit-span"
                                        style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #00f2fe; font-weight: bold;">ج.م</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-submit">🚀 حفظ وتحديث البيانات</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateUI(type) {
            const labels = document.querySelectorAll('.input-label');
            const units = document.querySelectorAll('.unit-span');
            const inputs = document.querySelectorAll('.partner-input');

            if (type === 'amount') {
                labels.forEach(l => l.innerText = 'المبلغ المدفوع');
                units.forEach(u => u.innerText = 'ج.م');
                inputs.forEach(i => {
                    i.setAttribute('max', '999999999');
                    i.placeholder = '0.00';
                });
            } else {
                labels.forEach(l => l.innerText = 'النسبة المئوية');
                units.forEach(u => u.innerText = '%');
                inputs.forEach(i => {
                    i.setAttribute('max', '100');
                    i.placeholder = '0';
                });
            }
        }

        // نفس دالة toggleCalculation السابقة
        function toggleCalculation() {
            const section = document.getElementById('capitalCalculationSection');
            const btn = document.getElementById('calcToggleBtn');
            if (section.style.display === 'none') {
                section.style.display = 'block';
                btn.innerHTML = '❌ إخفاء الأدوات';
                btn.classList.add('active');
            } else {
                section.style.display = 'none';
                btn.innerHTML = '🔄 إعادة حساب النسب';
                btn.classList.remove('active');
            }
        }
    </script>
    <div class="section-container">
        <h3 class="section-title" style="color: #f58529;">🤝 الشركاء</h3>

        <div class="cards-grid">
            @forelse ($partners ?? [] as $partner)
                <div class="glass-card">
                    <div class="card-header-top">
                        <h4 style="color: #f58529;">{{ $partner->name }}</h4>
                    </div>

                    <div class="card-info-row">
                        <span class="card-info-label">النسبة المئوية:</span>
                        <span class="card-info-value" style="color: #00f2fe; font-size: 1.1rem; font-weight: bold;">
                            {{ $partner->percentage }}%
                        </span>
                    </div>
                    <div class="card-info-row">
                        <span class="card-info-label">إجمالي رأس المال:</span>
                        <span class="card-info-value">{{ number_format($partner->last_capital_snapshot, 2) }}</span>
                    </div>

                    <div class="card-actions">
                        <button type="button" class="btn-action btn-edit"
                            onclick="openEditModal({{ $partner->id }}, `{{ addslashes($partner->name) }}`, {{ $partner->percentage ?? 0 }})">
                            ✏️ تعديل
                        </button>

                        <form action="{{ route('partners.destroy', $partner->id) }}" method="POST"
                            style="flex: 1; display: flex;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" style="width: 100%;"
                                onclick="return confirm('هل أنت متأكد من حذف هذا الشريك نهائياً؟');">
                                🗑️ حذف
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div style="font-size: 3rem; margin-bottom: 10px;">📉</div>
                    <p>لا يوجد شركاء مضافين حالياً.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div id="addPartnerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0; font-size: 1.5rem;">إضافة شريك جديد</h2>
                <button type="button" class="close-btn" onclick="closeModal('addPartnerModal')">&times;</button>
            </div>
            <form action="{{ route('partners.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>اسم الشريك</label>
                    <input type="text" name="name" class="form-control" required placeholder="أدخل اسم الشريك">
                </div>
                <button type="submit" class="btn-submit">حفظ الشريك</button>
            </form>
        </div>
    </div>

    <div id="editPartnerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 style="margin: 0; font-size: 1.5rem;">تعديل بيانات الشريك</h2>
                <button type="button" class="close-btn" onclick="closeModal('editPartnerModal')">&times;</button>
            </div>
            <form id="editPartnerForm" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>اسم الشريك</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>النسبة المئوية</label>
                    <input type="number" id="edit_percentage" name="percentage" class="form-control" required
                        min="0" max="100" step="any">
                </div>

                <button type="submit" class="btn-submit">تحديث البيانات</button>
            </form>
        </div>
    </div>

    <script>
        // دالة فتح المودال
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        // دالة إغلاق المودال
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // دالة التعديل (تعمل بشكل مثالي مع الكروت الجديدة)
        function openEditModal(id, name, percentage) {
            let form = document.getElementById('editPartnerForm');
            form.action = `/partners/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_percentage').value = percentage;
            openModal('editPartnerModal');
        }

        // إغلاق المودال عند النقر خارج النافذة
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
                setTimeout(() => {
                    event.target.style.display = 'none';
                }, 300);
            }
        }
    </script>
    <script>
        function toggleCalculation() {
            const section = document.getElementById('capitalCalculationSection');
            const btn = document.getElementById('calcToggleBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');

            if (section.style.display === 'none' || section.style.display === '') {
                // إظهار القسم
                section.style.display = 'block';
                setTimeout(() => section.classList.add('active'), 10);

                btnText.innerText = 'إخفاء أدوات الحساب';
                btnIcon.innerText = '❌';
                btn.classList.add('active');
            } else {
                // إخفاء القسم
                section.classList.remove('active');
                btnText.innerText = 'إعادة حساب النسب';
                btnIcon.innerText = '🔄';
                btn.classList.remove('active');

                setTimeout(() => {
                    if (!section.classList.contains('active')) section.style.display = 'none';
                }, 500);
            }
        }
    </script>
@endsection
