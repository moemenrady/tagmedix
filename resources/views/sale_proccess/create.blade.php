@extends('layouts.app_page')

@section('style')
    <style>
        :root {
            --prime: #ddcdbc;
            --prime-soft: #f4eee8;
            --bg: #515831;
            --bg-dark: #3f4526;
            --white: #ffffff;
            --radius: 15px;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Layout Adjustments */
        .main-wrapper {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 15px 0;
        }

        @media (min-width: 992px) {
            .main-wrapper {
                flex-direction: row-reverse;
                /* السلة يمين والمنتجات يسار في الديسكتوب */
                align-items: flex-start;
            }

            .products-section {
                flex: 1;
            }

            .cart-section {
                width: 350px;
                position: sticky;
                top: 20px;
            }
        }

        /* Search Box */
        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 15px 25px;
            border-radius: 30px;
            border: 2px solid var(--prime);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
        }

        .search-box input:focus {
            border-color: var(--bg);
            outline: none;
            box-shadow: 0 4px 15px rgba(81, 88, 49, 0.2);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }

        .product-card {
            background: #fff;
            border-radius: var(--radius);
            padding: 20px 10px;
            text-align: center;
            border: 1px solid #edf0f2;
            transition: 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80px;
            font-weight: 600;
            color: var(--bg-dark);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }

        .product-card:active {
            transform: scale(0.95);
            background: var(--prime-soft);
        }

        @media (min-width: 768px) {
            .product-card:hover {
                border-color: var(--bg);
                transform: translateY(-3px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }
        }

        /* Cart Section Styles */
        .cart-section {
            background: #fff;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        /* Mobile Table Overwrite */
        @media (max-width: 576px) {
            .table-responsive {
                border: none;
            }

            .table thead {
                display: none;
            }

            .table tr {
                display: block;
                border: 1px solid #eee;
                border-radius: 10px;
                margin-bottom: 10px;
                padding: 10px;
                position: relative;
            }

            .table td {
                display: block;
                text-align: right;
                border: none;
                padding: 5px 0;
            }

            .table td::before {
                content: attr(data-label);
                font-weight: bold;
                float: left;
                color: #888;
            }

            .btn-remove {
                position: absolute;
                top: 10px;
                left: 10px;
            }
        }

        /* Modal / Bottom Sheet */
        .variant-panel {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .6);
            display: none;
            align-items: flex-end;
            z-index: 2000;
            backdrop-filter: blur(3px);
        }

        .variant-box {
            background: #fff;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px 25px;
            border-radius: 25px 25px 0 0;
            animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .btn-add-cart {
            background: var(--bg);
            color: white;
            border-radius: 12px;
            padding: 12px;
            font-weight: bold;
            border: none;
            width: 100%;
        }

        .btn-add-cart:hover {
            background: var(--bg-dark);
            color: white;
        }

        /* Cart Badge Mobile */
        #cartCountBadge {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            vertical-align: middle;
        }

        .variant-panel {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .7);
            /* تغميق الخلفية قليلاً لزيادة التركيز */
            display: none;
            align-items: center;
            /* توسيط عمودي */
            justify-content: center;
            /* توسيط أفقي */
            z-index: 2000;
            backdrop-filter: blur(5px);
            padding: 20px;
            /* مسافة أمان للموبايل لكي لا يلمس حواف الشاشة */
        }

        .variant-box {
            background: #fff;
            width: 100%;
            max-width: 500px;
            /* عرض مناسب جداً للديسك توب والموبايل */
            padding: 25px;
            border-radius: 20px;
            /* زوايا دائرية من كل الجهات */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            /* أنيميشن ظهور من المنتصف */
            max-height: 90vh;
            /* التأكد من عدم خروج المودال عن الشاشة في الموبايل */
            overflow-y: auto;
            /* إضافة سكرول داخلي إذا كان المحتوى طويلاً في الموبايل */
        }

        /* أنيميشن احترافي للظهور من المنتصف */
        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* تحسين شكل المدخلات داخل المودال للموبايل */
        .form-select-lg,
        .form-control-lg {
            font-size: 1rem;
            padding: 10px;
        }

        @media (max-width: 576px) {
            .variant-box {
                padding: 20px 15px;
            }

            .variant-box h4 {
                font-size: 1.2rem;
            }
        }

        /* ... (باقي التنسيقات السابقة للسلة والمنتجات) ... */
    </style>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="main-wrapper">

            <div class="cart-section" id="cartSection" style="display: none;">
                <h4 class="mb-3 d-flex justify-content-between align-items-center">
                    <span>📦 السلة</span>
                    <span id="cartCountBadge">0</span>
                </h4>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>التفاصيل</th>
                                <th>الكمية</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cartItemsBody"></tbody>
                    </table>
                </div>

                <div class="mt-3 border-top pt-3">
                    <button class="btn btn-success w-100 btn-lg shadow-sm" onclick="previewInvoice()">
                        👁️ معاينة الفاتورة
                    </button>
                </div>
            </div>

            <div class="products-section">
                <div class="search-box">
                    <input type="text" id="searchBox" placeholder="🔍 ابحث عن منتج هنا...">
                </div>

                <div class="products-grid" id="productsGrid">
                </div>
            </div>

        </div>
    </div>

    <div id="variantModal" class="variant-panel" onclick="closeModal(event)">
        <div class="variant-box" onclick="event.stopPropagation()">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 id="productName" class="mb-0 text-dark"></h4>
                <button class="btn-close" onclick="document.getElementById('variantModal').style.display='none'"></button>
            </div>

            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold">اللون</label>
                    <select id="colorSelect" class="form-select form-select-lg" onchange="loadSizes()"></select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold">المقاس</label>
                    <select id="sizeSelect" class="form-select form-select-lg"></select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold">النوع</label>
                    <select id="printSelect" class="form-select form-select-lg">
                        <option value="0">بدون طباعة</option>
                        <option value="1">مطبوع</option>
                    </select>
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label small fw-bold">الكمية</label>
                    <input type="number" id="qty" value="1" min="1"
                        class="form-control form-control-lg text-center">
                </div>
            </div>

            <div class="mt-4">
                <button class="btn-add-cart shadow" onclick="addToCart()">إضافة إلى السلة</button>
                <button class="btn btn-light w-100 mt-2 py-2"
                    onclick="document.getElementById('variantModal').style.display='none'">إغلاق</button>
            </div>
        </div>
    </div>
    <script>
        let currentProduct = {};
        let cart = [];
        const productsGrid = document.getElementById('productsGrid');
        const searchInput = document.getElementById('searchBox');

        // 1. وظيفة البحث والتحميل الفوري
        function loadProducts(query = '') {
            fetch(`/products/search?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(products => {
                    productsGrid.innerHTML = '';
                    if (products.length === 0) {
                        productsGrid.innerHTML = '<p class="text-center w-100">لا توجد منتجات مطابقة</p>';
                        return;
                    }
                    products.forEach(p => {
                        const card = document.createElement('div');
                        card.className = 'product-card';
                        card.onclick = () => selectProduct(p.id, p.name);
                        card.innerHTML = `<div class="product-title">${p.name}</div>`;
                        productsGrid.appendChild(card);
                    });
                });
        }

        // تشغيل البحث عند فتح الصفحة
        document.addEventListener('DOMContentLoaded', () => loadProducts());

        // تشغيل البحث عند الكتابة
        searchInput.addEventListener('input', (e) => loadProducts(e.target.value));

        // 2. اختيار المنتج وفتح المودال
        function selectProduct(id, name) {
            currentProduct = {
                product_id: id,
                name: name
            };
            document.getElementById('productName').innerText = name;

            fetch(`/products/${id}/colors`)
                .then(res => res.json())
                .then(colors => {
                    let colorSel = document.getElementById('colorSelect');
                    colorSel.innerHTML = colors.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
                    loadSizes(); // تحميل المقاسات لأول لون متاح
                    document.getElementById('variantModal').style.display = 'flex';
                });
        }

        function loadSizes() {
            const colorId = document.getElementById('colorSelect').value;
            fetch(`/variants/sizes?product_id=${currentProduct.product_id}&color_id=${colorId}`)
                .then(res => res.json())
                .then(sizes => {
                    let sizeSel = document.getElementById('sizeSelect');
                    sizeSel.innerHTML = sizes.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
                });
        }

        // 3. إدارة السلة
        function addToCart() {
            let item = {
                product_id: currentProduct.product_id,
                name: currentProduct.name,
                color_id: document.getElementById('colorSelect').value,
                size_id: document.getElementById('sizeSelect').value,
                is_printed: document.getElementById('printSelect').value,
                quantity: document.getElementById('qty').value
            };

            cart.push(item);
            document.getElementById('cartCount').innerText = cart.length;
            document.getElementById('variantModal').style.display = 'none';
            alert('تمت الإضافة للسلة');
        }

        function createInvoice() {
            if (cart.length === 0) return alert('السلة فارغة!');

            fetch('{{ route('invoice.create') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: cart
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert('تم إنشاء الفاتورة بنجاح');
                    cart = [];
                    document.getElementById('cartCount').innerText = '0';
                });
        }

        function closeModal(e) {
            if (e.target.id === 'variantModal') e.target.style.display = 'none';
        }

        function renderCart() {
            const body = document.getElementById('cartItemsBody');
            const section = document.getElementById('cartSection');
            body.innerHTML = '';

            if (cart.length > 0) {
                section.style.display = 'block';
                cart.forEach((item, index) => {
                    body.innerHTML += `
                    <tr>
                        <td><b>${item.name}</b></td>
                        <td>${item.color_name} / ${item.size_name}</td>
                        <td>${item.is_printed == 1 ? 'مطبوع' : 'سادة'}</td>
                        <td>${item.quantity}</td>
                        <td><button class="btn-remove" onclick="removeFromCart(${index})">❌</button></td>
                    </tr>
                `;
                });
            } else {
                section.style.display = 'none';
            }
            document.getElementById('cartCount').innerText = cart.length;
        }

        function addToCart() {
            const colorSel = document.getElementById('colorSelect');
            const sizeSel = document.getElementById('sizeSelect');

            let item = {
                product_id: currentProduct.product_id,
                name: currentProduct.name,
                color_id: colorSel.value,
                color_name: colorSel.options[colorSel.selectedIndex].text,
                size_id: sizeSel.value,
                size_name: sizeSel.options[sizeSel.selectedIndex].text,
                is_printed: document.getElementById('printSelect').value,
                quantity: document.getElementById('qty').value
            };

            cart.push(item);
            renderCart();
            document.getElementById('variantModal').style.display = 'none';
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        // وظيفة الإرسال للمعاينة
        function previewInvoice() {
            if (cart.length === 0) return alert('السلة فارغة');

            // سنستخدم Form مخفي لإرسال البيانات وفتحها في صفحة جديدة
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('invoices.preview') }}';
            form.target = '_blank'; // فتح في صفحة جديدة

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const itemsInput = document.createElement('input');
            itemsInput.type = 'hidden';
            itemsInput.name = 'items';
            itemsInput.value = JSON.stringify(cart);
            form.appendChild(itemsInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
@endsection
