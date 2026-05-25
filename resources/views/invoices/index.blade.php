@extends('layouts.app_page')
@section('title', 'المبيعات')

@section('content')
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('success') }}", "success");
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                showSnackbar("{{ session('error') }}", "error");
            });
        </script>
    @endif

    <div class="page-container">

        {{-- عنوان الصفحة --}}
    @section('page_title')
        <h1 class="title"> المبيعات</h1>
    @endsection

    {{-- إجمالي المبيعات --}}
    <div id="totalSales"
        style="
        margin-bottom: 15px;
        font-size: 20px;
        font-weight: bold;
        color: #198754;
    ">
        إجمالي المبيعات: 0 ج
    </div>

    {{-- صندوق البحث --}}
    <div class="search-box" style="margin-bottom:15px;">
        <input type="text" id="invoiceSearch" placeholder="ابحث عن فاتورة" style="width:100%; padding:8px;"
            value="{{ request('search') ?? '' }}">
    </div>

    {{-- فلتر التاريخ --}}
    <div class="date-filters" style="margin-bottom:20px; display:flex; gap:8px; flex-wrap:wrap;">
        <input type="date" id="fromDate" placeholder="من">
        <input type="date" id="toDate" placeholder="إلى">
    </div>

    {{-- قائمة الفواتير --}}
    <div class="invoice-list" id="invoiceList">
        <p class="text-center p-3">⏳ جاري التحميل...</p>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('invoiceSearch');
        const invoiceList = document.getElementById('invoiceList');
        const typeCheckboxes = document.querySelectorAll('.filter-type');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');

        const searchRoute = @json(route('invoices.ajaxSearch'));
        const showRoute = @json(route('invoices.client.show', ':id'));

        // دالة منع الاستدعاء السريع (debounce)
        function debounce(fn, delay = 300) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        // دالة حماية النصوص من HTML injection
        function safeText(s) {
            return String(s ?? '').replace(/[&<>"]/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;'
                } [c];
            });
        }

        // دالة إنشاء كارد الفاتورة
        function renderInvoiceCard(inv) {
            const client = safeText(inv.client_name ?? 'عميل غير معروف');
            const number = safeText(inv.invoice_number);
            const type = safeText(inv.type);
            const total = inv.total ?? 0;
            const date = inv.updated_at ? new Date(inv.updated_at).toLocaleDateString() : '-';
            const url = showRoute.replace(':id', inv.id);

            return `
        <div class="session-card" role="button" onclick="window.location.href='${url}'">
            <div class="info" style="text-align:right;">
                <h3>#${number}</h3>
                <p>التاريخ: ${date}</p>
            </div>
            <div class="persons">
                <div class="total-amount">الإجمالي: ${total} ج</div>
            </div>
        </div>
        `;
        }

        // عرض حالة التحميل
        function showLoading() {
            invoiceList.innerHTML = `<p class="text-center p-3">⏳ جاري التحميل...</p>`;
            document.getElementById('totalSales').textContent = `إجمالي المبيعات: 0 ج`;
        }

        // عرض لا توجد نتائج
        function showNoResults() {
            invoiceList.innerHTML = `<p class="no-results">❌ لا توجد فواتير</p>`;
            document.getElementById('totalSales').textContent = `إجمالي المبيعات: 0 ج`;
        }

    


        // جلب وعرض الفواتير
        async function fetchInvoices() {
            const q = searchInput.value.trim();
            const types = Array.from(typeCheckboxes).filter(c => c.checked).map(c => c.value);
            const from = fromDate.value;
            const to = toDate.value;

            showLoading();
            try {
                const url = new URL(searchRoute, location.origin);
                if (q) url.searchParams.append('q', q);
                if (types.length > 0) url.searchParams.append('types', types.join(','));
                if (from) url.searchParams.append('from', from);
                if (to) url.searchParams.append('to', to);

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                const items = Array.isArray(data) ? data : (data.data ?? data.items ?? data);

                if (!items || items.length === 0) {
                    showNoResults();
                    return;
                }

                invoiceList.innerHTML = '';
                items.forEach(i => invoiceList.innerHTML += renderInvoiceCard(i));


                // استخدم هذا السطر:
                const totalSalesAmount = items.reduce((sum, inv) => sum + parseFloat(inv.total ?? 0), 0);
                document.getElementById('totalSales').textContent =
                    `إجمالي المبيعات: ${totalSalesAmount.toFixed(2)} ج`;

            } catch (err) {
                console.error(err);
                invoiceList.innerHTML = `<p class="no-results">حدث خطأ أثناء جلب الفواتير</p>`;
                document.getElementById('totalSales').textContent = `إجمالي المبيعات: 0 ج`;
            }
        }

        // ربط الأحداث
        const debouncedFetch = debounce(fetchInvoices, 250);
        searchInput.addEventListener('input', debouncedFetch);
        typeCheckboxes.forEach(cb => cb.addEventListener('change', fetchInvoices));
        fromDate.addEventListener('change', fetchInvoices);
        toDate.addEventListener('change', fetchInvoices);

        // استدعاء أولي لجلب الفواتير
        fetchInvoices();
    });
</script>
@endsection
@section('style')
<style>
:root {
    --prime: #ddcdbc;
    --prime-soft: #e6ddd4;
    --bg: #515831;
    --bg-dark: #3f4526;
    --white: #ffffff;
}

/* الخلفية العامة */
body {
    background: linear-gradient(-45deg, var(--bg), var(--bg-dark), var(--bg));
    background-size: 400% 400%;
    animation: gradientMove 14s ease infinite;
    color: var(--white);
    font-family: system-ui, sans-serif;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* الحاوية */
.page-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 18px;
}

/* عنوان الصفحة */
.title {
    font-size: 28px;
    font-weight: 900;
    color: var(--prime);
    margin-bottom: 20px;
}

/* إجمالي المبيعات */
#totalSales {
    font-size: 20px;
    font-weight: 700;
    color: var(--prime-soft);
    margin-bottom: 15px;
}

/* صندوق البحث */
.search-box input {
    width: 100%;
    padding: 10px 14px;
    border-radius: 12px;
    border: none;
    outline: none;
    font-size: 16px;
    background: rgba(221, 205, 188, 0.15);
    color: var(--white);
}

/* لون Placeholder واضح */
.search-box input::placeholder {
    color: var(--prime);
    opacity: 1; /* لضمان وضوح اللون */
}


/* فلتر التاريخ */
.date-filters input {
    padding: 8px 12px;
    border-radius: 12px;
    border: none;
    background: rgba(221, 205, 188, 0.15);
    color: var(--white);
    font-size: 14px;
}

/* كارد الفاتورة */
.session-card {
    background: rgba(221, 205, 188, 0.15);
    border-radius: 16px;
    padding: 16px 18px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
}

.session-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0,0,0,.35);
}

.session-card .info h3 {
    font-size: 18px;
    font-weight: 900;
    color: var(--prime);
    margin-bottom: 6px;
}

.session-card .info p {
    margin: 2px 0;
    color: var(--prime-soft);
    font-size: 14px;
}

/* الإجمالي */
.session-card .total-amount {
    font-size: 18px;
    font-weight: 800;
    color: var(--bg);
    background: linear-gradient(135deg, var(--prime), var(--prime-soft));
    padding: 8px 16px;
    border-radius: 12px;
    border: 1px solid var(--prime);
    display: inline-block;
    text-align: center;
}

/* Snackbar العملاء */
#clientsSnackbar {
    background: rgba(221, 205, 188, 0.95);
    color: var(--bg);
    border-radius: 18px;
    padding: 14px 18px;
    box-shadow: 0 20px 40px rgba(0,0,0,.25);
    position: fixed;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    max-width: 320px;
    z-index: 9999;
    text-align: right;
}

#clientsSnackbar ul {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 140px;
    overflow-y: auto;
}

#clientsSnackbar li {
    padding: 4px 0;
    border-bottom: 1px solid rgba(221, 205, 188, 0.5);
}

/* نص التحميل وعدم وجود فواتير */
.text-center, .no-results {
    font-weight: 700;
    color: var(--prime-soft);
    padding: 12px 0;
    text-align: center;
}

/* التأثير عند تمرير الماوس على الفلاتر */
.date-filters input:hover,
.search-box input:hover {
    background: rgba(221, 205, 188, 0.25);
    cursor: pointer;
}

/* Scrollbar للكارد */
.session-card .persons {
    text-align: center;
}

/* Scrollbar داخل Snackbar */
#clientsSnackbar ul::-webkit-scrollbar {
    width: 6px;
}
#clientsSnackbar ul::-webkit-scrollbar-thumb {
    background: var(--bg-dark);
    border-radius: 4px;
}
</style>
@endsection
