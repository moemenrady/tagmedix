<!-- resources/views/invoice.blade.php -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta charset="UTF-8">
    <title>الفاتورة</title>
    <style>
        :root {
            --bg-start: #fdfdfd;
            --bg-end: #bdbdbd;
            --card-bg: #ffffff;
            --muted: #888;
            --accent1: #ff416c;
            --accent2: #ff4b2b;
            --pill-bg: #cfcfcf;
            --success: #2e8b57;
            --card-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: "Tahoma", sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: linear-gradient(to bottom, var(--bg-start), var(--bg-end));
            color: #222;
        }

        /* صفحة كاملة */
        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 18px;
            box-sizing: border-box;
            gap: 18px;
            align-items: center;
        }

        h2.page-title {
            margin: 0;
            padding: 6px 12px;
            font-size: 20px;
            text-align: center;
        }

        /* زرّ الرجوع (كما عندك) */
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #e5c6c3;
            border-radius: 50%;
            padding: 13px;
            cursor: pointer;
            transition: transform 0.2s;
            z-index: 2000;
        }

        /* الحاوية الرئيسية */
        .invoice-container {
            width: 100%;
            max-width: 1100px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: visible;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* رأس الفاتورة: الإجمالي + عنوان */
        .invoice-header {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .summary {
            background: var(--pill-bg);
            padding: 10px 22px;
            border-radius: 25px;
            font-weight: bold;
            align-self: flex-start;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .invoice-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
            min-width: 160px;
        }

        .invoice-meta .small {
            color: var(--muted);
            font-size: 13px;
        }

        /* جدول الفاتورة (ديسكتوب) */
        .table-wrapper {
            width: 100%;
            box-sizing: border-box;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            min-width: 520px;
        }

        table.invoice-table thead th {
            padding: 12px 10px;
            text-align: center;
            font-weight: 700;
            background: rgba(250, 250, 250, 0.98);
            position: sticky;
            top: 0;
            z-index: 3;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        table.invoice-table tbody td {
            padding: 12px 10px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            word-break: break-word;
        }

        td.total {
            color: var(--success);
            font-weight: 700;
        }

        /* Scrollable tbody technique */
        .invoice-table thead,
        .invoice-table tbody,
        .invoice-table tr {
            display: block;
        }

        .invoice-table tbody {
            max-height: 48vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .invoice-table tr {
            display: table;
            table-layout: fixed;
            width: 100%;
        }

        /* كروت الأصناف (مخفية افتراضياً، تظهر فقط على الموبايل) */
        .cards-wrapper {
            display: none;
            /* افتراضي - لا تظهر على الديسكتوب */
            width: 100%;
            box-sizing: border-box;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .item-card {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(180deg, #fff, #fafafa);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .item-left {
            display: flex;
            gap: 10px;
            align-items: center;
            min-width: 0;
        }

        .item-badge {
            background: rgba(0, 0, 0, 0.06);
            border-radius: 10px;
            padding: 8px 10px;
            font-weight: 700;
            min-width: 48px;
            text-align: center;
            flex-shrink: 0;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .item-name {
            font-weight: 800;
            font-size: 15px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            direction: rtl;
        }

        .item-meta {
            font-size: 13px;
            color: var(--muted);
        }

        .item-right {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            min-width: 88px;
        }

        .item-price {
            font-weight: 700;
        }

        .item-total {
            color: var(--success);
            font-weight: 800;
        }

        /* تفاصيل الفوتر/أزرار */
        .action-bar {
            width: 100%;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
        }

        .action-left,
        .action-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        button {
            border: none;
            border-radius: 25px;
            padding: 12px 22px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
        }

        .btn-cancel {
            background: #e7b4b4;
            color: white;
        }

        .btn-print {
            background: #fcefb1;
            color: #333;
        }

        .btn-done {
            background: linear-gradient(135deg, var(--accent1), var(--accent2));
            width: 220px;
            color: white;
            font-size: 16px;
            font-weight: 800;
            border: none;
            border-radius: 50px;
            padding: 13px 26px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1.6px;
            transition: all 0.35s ease-in-out;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(255, 65, 108, 0.6), 0 0 30px rgba(255, 75, 43, 0.35);
        }

        .btn-done:hover {
            transform: scale(1.06) rotate(-1deg);
            box-shadow: 0 0 25px rgba(255, 65, 108, 0.95), 0 0 50px rgba(255, 75, 43, 0.6);
        }

        .btn-done::before {
            content: "";
            position: absolute;
            top: 0;
            left: -75%;
            width: 50%;
            height: 100%;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 100%);
            transform: skewX(-25deg);
        }

        .btn-done:hover::before {
            animation: shine 0.9s forwards;
        }

        @keyframes shine {
            0% {
                left: -75%;
            }

            100% {
                left: 125%;
            }
        }

        /* شريط أزرار ثابت في أسفل الموبايل */
        .sticky-actions-mobile {
            display: none;
        }

        /* طباعة */
        @media print {
            body {
                background: white;
            }

            .action-bar,
            .sticky-actions-mobile {
                display: none !important;
            }

            .invoice-container {
                box-shadow: none;
                border-radius: 0;
                padding: 6px;
            }

            table.invoice-table thead th {
                position: static;
            }

            .invoice-table tbody {
                max-height: none;
                overflow: visible;
                display: table-row-group;
            }

            .invoice-table thead,
            .invoice-table tbody,
            .invoice-table tr {
                display: table;
                width: 100%;
            }

            /* كروت لا تظهر في الطباعة */
            .cards-wrapper {
                display: none !important;
            }
        }

        /* --- Responsive behavior --- */
        @media (max-width: 900px) {
            .invoice-container {
                padding: 14px;
            }

            .invoice-table tbody {
                max-height: 40vh;
            }

            .btn-done {
                width: 180px;
                font-size: 15px;
            }

            .invoice-meta {
                min-width: 120px;
            }
        }

        /* MOBILE: show cards, hide table */
        @media (max-width: 640px) {
            .invoice-container {
                padding: 12px;
                border-radius: 10px;
            }

            h2.page-title {
                font-size: 18px;
            }

            .invoice-header {
                align-items: flex-start;
                gap: 8px;
            }

            .summary {
                position: static;
                align-self: stretch;
                display: inline-block;
                width: auto;
                margin-bottom: 6px;
            }

            /* على الموبايل: اخفي الجدول واظهر الكروت */
            table.invoice-table {
                display: none;
            }

            .table-wrapper {
                display: none;
            }

            /* هنا نظهر الكروت (فقط على الموبايل) */
            .cards-wrapper {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .action-bar {
                display: none;
            }

            /* show sticky bottom actions for mobile */
            .sticky-actions-mobile {
                display: flex;
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                padding: 10px;
                gap: 8px;
                justify-content: center;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
                box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.08);
                z-index: 60;
            }

            .sticky-actions-mobile .btn-done {
                width: calc(100% - 28px);
                max-width: 620px;
            }

            /* اجعل قائمة الكروت لا تتغطى بالشريط السفلي */
            .cards-wrapper {
                padding-bottom: 82px;
                /* مساحة للشريط الثابت */
            }

            .invoice-table tbody {
                max-height: calc(48vh - 56px);
            }
        }


        /* تنسيق المودال */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            /* مخفي افتراضياً */
            align-items: center;
            justify-content: center;
            z-index: 3000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #333;
        }

        .payment-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .pay-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px;
            border: 2px solid #eee;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pay-btn:hover {
            border-color: var(--accent1);
            background: #fff5f6;
        }

        .pay-btn i {
            font-size: 24px;
        }

        .pay-btn span {
            font-weight: bold;
            font-size: 16px;
        }

        .btn-close-modal {
            margin-top: 20px;
            background: none;
            color: #888;
            font-size: 14px;
            text-decoration: underline;
        }

        .notes-section {
            margin: 20px 0;
        }

        .notes-section label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #333;
        }

        .notes-section textarea {
            width: 100%;
            min-height: 110px;
            resize: vertical;
            padding: 15px;
            border-radius: 15px;
            border: 2px solid #e8e8e8;
            background: #fff;
            font-family: inherit;
            font-size: 15px;
            transition: .3s;
            box-sizing: border-box;
        }

        .notes-section textarea:focus {
            outline: none;
            border-color: #ff416c;
            box-shadow: 0 0 0 5px rgba(255, 65, 108, .12);
        }

        .notes-hint {
            margin-top: 8px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="page">
        <h2 class="page-title">الفاتورة</h2>

        {{-- زر الرجوع للرئيسيه --}}
        <form action="{{ route('main.create') }}">
            <button class="back-btn">الرئيسيه</button>

        </form>
        <div class="invoice-container" role="region" aria-label="Invoice container">
            <div class="invoice-header">
                <div class="header-left" style="gap:18px;">
                    <div class="summary" id="fixedSummary">الإجمالي:
                        @php $grandTotal = 0; @endphp
                        @foreach ($items as $item)
                            @php $grandTotal += $item['total']; @endphp
                        @endforeach
                        <span style="margin-left:10px;">{{ $grandTotal }} جينة</span>
                    </div>
                </div>


            </div>

            <!-- جدول الديسكتوب -->
            <div class="table-wrapper" aria-live="polite">
                <table class="invoice-table" role="table" aria-label="Invoice items">
                    <thead role="rowgroup">
                        <tr role="row">
                            <th role="columnheader">عدد</th>
                            <th role="columnheader">منتج</th>
                            <th role="columnheader">سعر</th>
                            <th role="columnheader">مجموع</th>
                        </tr>
                    </thead>
                    <tbody role="rowgroup">
                        @foreach ($items as $item)
                            <tr role="row">
                                <td role="cell">{{ $item['qty'] }}</td>
                                <td role="cell" style="text-align:right; padding-right:14px;">{{ $item['name'] }}
                                </td>
                                <td role="cell">{{ $item['price'] ?? '-' }}</td>
                                <td role="cell" class="total">{{ $item['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- كروت الموبايل (تظهر فقط على شاشات صغيرة) -->
            <div class="cards-wrapper" aria-live="polite">
                @foreach ($items as $item)
                    <div class="item-card" role="article" aria-label="صنف: {{ $item['name'] }}">
                        <div class="item-left">
                            <div class="item-badge">{{ $item['qty'] }}</div>
                            <div class="item-details">
                                <div class="item-name">{{ $item['name'] }}</div>
                            </div>
                        </div>

                        <div class="item-right">
                            <div class="item-price">سعر الوحدة: {{ $item['price'] ?? '-' }}</div>
                            <div class="item-total">مجموع: {{ $item['total'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- زر إتمام الفاتورة (الـ form الرئيسي) -->
            <form id="invoiceForm" style="margin-top:8px;">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id ?? '' }}">
                <div class="notes-section">
                    <label for="notes">📝 ملاحظات الطلب</label>

                    <textarea id="notes" name="notes" rows="4" placeholder="اكتب أي ملاحظات خاصة بالطلب أو الفاتورة..."></textarea>

                    <div class="notes-hint">
                        سيتم طباعة هذه الملاحظات داخل الفاتورة تحت عنوان
                        <strong>Elevenz Special Notes</strong>
                    </div>
                </div>
                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][item_type]" value="product">
                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item['id'] }}">
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                @endforeach

                <div class="action-bar" aria-hidden="false">
                    <div class="action-left">
                        <button type="button" class="btn-cancel"
                            onclick="window.location.replace('{{ route('sale_proccess.create') }}')">إلغاء</button>
                        <button type="button" class="btn-print" onclick="openPrintForm()">طباعة الفاتورة</button>
                    </div>

                    <div class="action-right">
                        <button type="submit" class="btn-done">إتمام الفاتورة</button>
                    </div>
                </div>
            </form>

            <!-- فورم الطباعة: يبقى موجوداً منفصلاً ويُرسل target=_blank -->
            <form id="printForm" action="{{ route('invoices.print') }}" method="POST" target="_blank">
                @csrf

                <!-- هنبعت النوتس -->
                <input type="hidden" name="notes" id="print_notes">

                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                    <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item['name'] }}">
                    <input type="hidden" name="items[{{ $index }}][price]" value="{{ $item['price'] ?? 0 }}">
                @endforeach
            </form>

            <!-- شريط أزرار ثابت للهواتف (يحتوي نفس الزرار الرئيسي) -->
            <div class="sticky-actions-mobile" aria-hidden="true">
                <button type="button" class="btn-cancel"
                    onclick="window.location.replace('{{ route('sale_proccess.create') }}')">إلغاء</button>
                <button type="submit" form="invoiceForm" class="btn-done">إتمام الفاتورة</button>
            </div>
        </div>
    </div>
    <div id="paymentModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-title">اختر طريقة الدفع</div>

            <div class="payment-options">
                <div class="pay-btn" onclick="submitWithPayment('cash')">
                    <span style="font-size: 30px;">💵</span>
                    <span>كاش (Cash)</span>
                </div>

                <div class="pay-btn" onclick="submitWithPayment('digital')">
                    <span style="font-size: 30px;">📱</span>
                    <span>محفظة (Digital)</span>
                </div>
            </div>

            <button type="button" class="btn-close-modal" onclick="closePaymentModal()">إغلاق وتعديل
                الفاتورة</button>
        </div>
    </div>
    <script>
        // 1. تعريف المتغيرات والدوال المساعدة في النطاق العام
        let selectedPaymentType = '';

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function openPrintForm() {
            document.getElementById('print_notes').value =
                document.getElementById('notes').value;

            document.getElementById('printForm').submit();
        }

        function buildFormData(form) {
            return new FormData(form);
        }

        function postJsonForm(url, fd) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd,
                credentials: 'same-origin'
            });
        }

        // 2. معالجة فتح المودال عند الضغط على زر الإرسال
        document.getElementById('invoiceForm').addEventListener('submit', function(e) {
            e.preventDefault(); // منع الإرسال المباشر
            document.getElementById('paymentModal').style.display = 'flex'; // فتح مودال الدفع
        });

        // 3. الدالة النهائية للإرسال (تستدعى من أزرار المودال كاش/محفظة)
        async function submitWithPayment(type) {
            selectedPaymentType = type;
            const modal = document.getElementById('paymentModal');
            const form = document.getElementById('invoiceForm');
            const doneBtn = document.querySelector('.btn-done');

            // إغلاق المودال
            modal.style.display = 'none';

            const fd = buildFormData(form);
            fd.append('payment_type', selectedPaymentType);

            try {
                // تغيير حالة الزر لمنع التكرار
                doneBtn.disabled = true;
                doneBtn.innerText = "جاري الحفظ...";

                // تأكد أن هذا الراوت موجود في web.php
                let response = await postJsonForm("{{ route('invoices.store') }}", fd);

                const ct = response.headers.get('content-type') || '';
                let data;

                try {
                    data = ct.includes('application/json') ?
                        await response.json() : {
                            message: await response.text()
                        };
                } catch (e) {
                    data = {
                        message: "خطأ في قراءة رد السيرفر",
                        errors: []
                    };
                }

                if (response.ok) {

                    // نجاح العملية
                    const invoiceId = data.invoice ? (data.invoice.id ?? null) : null;

                    if (confirm('✅ تم إنشاء الفاتورة بنجاح. هل تريد طباعتها الآن؟')) {
                        document.getElementById('print_notes').value =
                            document.getElementById('notes').value;

                        if (invoiceId) {
                            const invInput = document.createElement('input');
                            invInput.type = 'hidden';
                            invInput.name = 'invoice_id';
                            invInput.value = invoiceId;
                            document.getElementById('printForm').appendChild(invInput);
                        }

                        document.getElementById('printForm').submit();
                    }

                    setTimeout(() => {
                        window.location.href = "{{ route('main.create') }}";
                    }, 1000);

                } else {

                    doneBtn.disabled = false;
                    doneBtn.innerText = "إتمام الفاتورة";

                    const error = new Error(data.message || 'حدث خطأ في السيرفر');
                    error.errors = data.errors || [];

                    throw error;
                }
            } catch (err) {
                console.error(err);

                showErrorModal(
                    err.message || 'حدث خطأ غير متوقع',
                    err.errors || []
                );

                const doneBtn = document.querySelector('.btn-done');
                if (doneBtn) {
                    doneBtn.disabled = false;
                    doneBtn.innerText = "إتمام الفاتورة";
                }
            }
        }

        // 4. منطق مفتاح Enter
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter' || e.repeat) return;

            const active = document.activeElement;
            if (active.tagName === 'TEXTAREA' || active.isContentEditable) return;

            e.preventDefault();
            const doneBtn = document.querySelector('.btn-done');
            if (doneBtn) doneBtn.click();
        });
    </script>
    <script>
        /**
         * Submit on Enter:
         * - اضغط Enter => ينفذ click على زر .btn-done
         * - يتجاهل لو التركيز على textarea أو على عنصر contenteditable
         * - يتجاهل لو العنصر الحالي يحمل data-ignore-enter="true"
         */
        document.addEventListener('keydown', function(e) {
            // فقط مفتاح Enter
            if (e.key !== 'Enter') return;

            // لا نريد تكرار عند الضغط المستمر
            if (e.repeat) return;

            const active = document.activeElement;
            if (!active) return;

            const tag = active.tagName.toUpperCase();

            // تجاهل لو بنكتب في textarea أو داخل عنصر قابل للتحرير
            if (tag === 'TEXTAREA' || active.isContentEditable) return;

            // تجاهل لو المستخدم وضع علامه لمنع السلوك على حقل معين
            if (active.hasAttribute && active.hasAttribute('data-ignore-enter')) return;

            // بعض الـ INPUTs نسمح لهم بالـ Enter (مثلاً حقل البحث) أو نمنعهم
            // هنا نمنع لو التركيز على input من نوع button/file/radio/checkbox/image
            if (tag === 'INPUT') {
                const t = (active.type || '').toLowerCase();
                const blocked = ['button', 'file', 'radio', 'checkbox', 'image'];
                if (blocked.includes(t)) return;
                // إذا كنت لا تريد ارسال Enter داخل حقول النص (مثلاً عند تعديل اسم المنتج)
                // أزل التعليق من السطر التالي لتعطيل submit عند التركيز في أي INPUT نصي:
                // if (['text','search','number','email','tel','password'].includes(t)) return;
            }

            // منع السلوك الإفتراضي (مثلاً إرسال فورم افتراضياً)
            e.preventDefault();

            // شوف زر الإتمام (نسختين: داخل الفورم أو شريط الهواتف)
            const doneBtn = document.querySelector('.btn-done[form="invoiceForm"]') ||
                document.querySelector('.btn-done');

            if (doneBtn) {
                // أمر مباشر للنقر — سيشغّل الـ submit handler الموجود
                doneBtn.click();
            }
        });

        function showErrorModal(message, errors = []) {

            document.getElementById('errorMessage').innerText = message;

            const container = document.getElementById('errorList');
            container.innerHTML = '';

            errors.forEach(err => {

                const el = document.createElement('div');

                el.style.cssText = `
            background:#fff5f5;
            border:1px solid #ffd1d1;
            padding:10px;
            border-radius:12px;
            font-size:14px;
        `;

                el.innerHTML = `
            <div><strong>📦 ${err.name}</strong></div>
            <div>المطلوب: <b>${err.required}</b></div>
            <div>المتاح: <b>${err.available}</b></div>
            <div style="color:#d33;">ناقص: <b>${err.missing}</b></div>
            ${err.product ? `<div style="color:#888;font-size:12px;">منتج: ${err.product}</div>` : ''}
        `;

                container.appendChild(el);
            });

            document.getElementById('errorModal').style.display = 'flex';
        }

        function closeErrorModal() {
            document.getElementById('errorModal').style.display = 'none';
        }
    </script>
    <div id="errorModal" class="modal-overlay">
        <div class="modal-content" style="border:2px solid #ff4b2b; text-align:right;">

            <div style="font-size:22px; text-align:center;">⚠️ خطأ في المخزون</div>

            <p id="errorMessage" style="margin-top:10px; font-weight:700; color:#d33; text-align:center;"></p>

            <div id="errorList" style="margin-top:15px; display:flex; flex-direction:column; gap:10px;">
            </div>

            <button onclick="closeErrorModal()" class="btn-close-modal">
                إغلاق
            </button>

        </div>
    </div>

</body>

</html>
