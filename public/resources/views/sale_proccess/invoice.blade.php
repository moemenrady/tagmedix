<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>معاينة الفاتورة - {{ $type }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* التنسيقات الخاصة بك كما هي مع تحسينات بسيطة */
        :root {
            --prime: #ddcdbc;
            --prime-soft: #e6ddd4;
            --bg: #515831;
            --bg-dark: #3f4526;
            --white: #ffffff;
            --border: rgba(221, 205, 188, .35);
        }

        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: linear-gradient(180deg, var(--bg), var(--bg-dark));
            color: var(--white);
            min-height: 100vh;
        }

        .page {
            max-width: 1100px;
            margin: auto;
            padding: 24px;
        }

        .invoice-box {
            background: rgba(221, 205, 188, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            padding: 22px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, .35);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .header h2 {
            color: var(--prime);
            margin: 0;
            font-size: 22px;
            font-weight: 900;
        }

        .back-btn {
            background: linear-gradient(135deg, var(--prime), var(--prime-soft));
            color: var(--bg);
            border-radius: 12px;
            padding: 8px 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
        }

        .summary-banner {
            background: rgba(221, 205, 188, 0.15);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--prime);
        }

        .summary-banner div {
            font-size: 18px;
            font-weight: 900;
            color: var(--prime);
        }

        .type-badge {
            background: var(--prime);
            color: var(--bg);
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, .03);
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }

        th {
            color: var(--prime);
            font-weight: 800;
            background: rgba(221, 205, 188, .1);
        }

        td.name {
            text-align: right;
            font-weight: 700;
        }

        td.total {
            color: #b6ffb6;
            font-weight: 900;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            border: none;
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-cancel {
            background: rgba(255, 255, 255, .15);
            color: var(--white);
        }

        .btn-print {
            background: var(--prime-soft);
            color: var(--bg);
        }

        .btn-done {
            background: linear-gradient(135deg, var(--prime), var(--prime-soft));
            color: var(--bg);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-done:hover {
            transform: scale(1.05);
        }

        @media (max-width: 640px) {
            table {
                display: none;
            }

            .cards {
                display: grid;
                gap: 12px;
            }

            .card-item {
                background: rgba(255, 255, 255, .05);
                border: 1px solid var(--border);
                border-radius: 14px;
                padding: 14px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .qty {
                background: var(--prime);
                color: var(--bg);
                border-radius: 8px;
                padding: 4px 10px;
                font-weight: 900;
                margin-left: 10px;
            }

            .card-info {
                flex-grow: 1;
                text-align: right;
            }

            .name {
                font-weight: 800;
                color: var(--prime);
                display: block;
            }

            .price {
                font-size: 12px;
                color: #ccc;
            }
        }
    </style>
</head>

<body>

    <div class="page">
        <div class="header">
            <button class="back-btn" onclick="history.back()">← تعديل السلة</button>
            <h2>معاينة الفاتورة</h2>
        </div>

        <div class="invoice-box">
            <div class="summary-banner">
                <div>الإجمالي النهائي: {{ number_format($grandTotal, 2) }} جنيه</div>
                <span class="type-badge">نوع الفاتورة: {{ $type }}</span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>العدد</th>
                        <th style="text-align: right;">المنتج والتفاصيل</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item['qty'] }}</td>
                            <td class="name">{{ $item['name'] }}</td>
                            <td>{{ number_format($item['price'], 2) }}</td>
                            <td class="total">{{ number_format($item['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="cards" style="display: none;">
                @foreach ($items as $item)
                    <div class="card-item">
                        <div class="qty">{{ $item['qty'] }}</div>
                        <div class="card-info">
                            <span class="name">{{ $item['name'] }}</span>
                            <span class="price">سعر: {{ number_format($item['price'], 2) }}</span>
                        </div>
                        <div class="total">{{ number_format($item['total'], 2) }}</div>
                    </div>
                @endforeach
            </div>

            <form id="invoiceForm">
                @csrf
                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][product_id]"
                        value="{{ $item['product_id'] }}">
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                    <input type="hidden" name="items[{{ $index }}][is_printed]"
                        value="{{ $item['is_printed'] }}">

                    <input type="hidden" name="items[{{ $index }}][color_id]"
                        value="{{ $item['color_id'] ?? '' }}">
                    <input type="hidden" name="items[{{ $index }}][size_id]"
                        value="{{ $item['size_id'] ?? '' }}">
                    <input type="hidden" name="items[{{ $index }}][color_name]"
                        value="{{ $item['color_name'] ?? '' }}">
                    <input type="hidden" name="items[{{ $index }}][size_name]"
                        value="{{ $item['size_name'] ?? '' }}">
                @endforeach


                <div class="actions">
                    <div>
                        <button type="button" class="btn-cancel" onclick="history.back()">إلغاء</button>
                        <button type="button" class="btn-print" onclick="openPrintForm()">🖨️ طباعة</button>
                    </div>
                    <button type="submit" class="btn-done">✅ إتمام وحفظ الفاتورة</button>
                </div>
            </form>

            <form id="printForm" action="{{ route('invoices.print') }}" method="POST" target="_blank"
                style="display:none;">
                @csrf
                <input type="hidden" name="grandTotal" value="{{ $grandTotal }}">
                @foreach ($items as $index => $item)
                    <input type="hidden" name="items[{{ $index }}][qty]" value="{{ $item['qty'] }}">
                    <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item['name'] }}">
                    <input type="hidden" name="items[{{ $index }}][price]" value="{{ $item['price'] }}">
                    <input type="hidden" name="items[{{ $index }}][total]" value="{{ $item['total'] }}">
                @endforeach
            </form>

        </div>
    </div>

    <script>
        function openPrintForm() {
            document.getElementById('printForm').submit();
        }

       document.getElementById('invoiceForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = this.querySelector('.btn-done');
    const originalText = btn.innerText;
    btn.innerText = 'جاري الحفظ...';
    btn.disabled = true;

    try {
        const res = await fetch("{{ route('invoices.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: new FormData(this)
        });

        const data = await res.json();

        if (res.ok) {
            alert('✅ تم إنشاء الفاتورة وحسم الكميات بنجاح');
            window.location.href = "{{ route('invoice.index') }}";
        } else {
            // معالجة رسالة نقص المخزون بشكل احترافي
            if (data.shortages) {
                let errorMsg = '❌ لا يوجد مخزون كافٍ لـ:\n';
                data.shortages.forEach(item => {
                    errorMsg += `- ${item.product_name}: مطلوب (${item.required}), متاح (${item.available})\n`;
                });
                alert(errorMsg);
            } else {
                alert(data.message || 'حدث خطأ غير متوقع');
            }
            
            btn.innerText = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        alert('فشل الاتصال بالسيرفر');
        btn.innerText = originalText;
        btn.disabled = false;
    }
});
    </script>
</body>

</html>
