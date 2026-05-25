@extends('layouts.app_page')

@section('title', "تفاصيل المنتج — {$product->name}")

@section('content')
    <div class="product-container">
        <div class="main-product-card fade-in">
            {{-- الرأس: اسم المنتج والتحكم --}}
            <div class="product-header-section">
                <div class="info">
                    <span class="category-label">📦 منتج</span>
                    <h1 class="product-title">{{ $product->name }}</h1>
                    <div class="product-meta">
                        <span class="meta-item">ID: {{ $product->id }}</span>
                    </div>
                </div>
                <button type="button" class="btn-modern edit" onclick="openEditProductNameModal()">
                    <span>✏️</span> تعديل الاسم
                </button>
            </div>
            {{-- قسم الألوان والمقاسات --}}
            <div class="variants-section">
                <div class="section-title-wrapper">
                    <h3>🎨 الألوان والمخزون</h3>
                    <a href="{{ route('variants.create', $product->id) }}" class="btn-modern success">
                        <span>➕</span> إضافة تنوع جديد
                    </a>
                </div>

                @foreach ($colors as $colorId => $variants)
                    <div class="color-group">
                        {{-- بطاقة اللون --}}
                        <div class="color-header" onclick="toggleSizes({{ $colorId }})">
                            <div class="color-info">
                                <span class="color-dot"
                                    style="background-color: {{ strtolower($variants->first()->color->name) == 'white' ? '#fff' : (strtolower($variants->first()->color->name) == 'black' ? '#000' : '#515831') }}; border: 1px solid #ddd;"></span>
                                <span class="color-name">{{ $variants->first()->color->name }}</span>
                            </div>
                            <div class="color-summary">
                                <span class="total-qty">📦 {{ $variants->sum(fn($v) => $v->stocks->sum('quantity')) }}
                                    قطعة</span>
                                <span class="toggle-icon">▼</span>
                            </div>
                        </div>

                        {{-- تفاصيل المقاسات داخل اللون --}}
                        <div class="sizes-container d-none" id="sizes-{{ $colorId }}">
                            <table class="sizes-table">
                                <thead>
                                    <tr>
                                        <th>المقاس</th>
                                        <th>إجمالي</th>
                                        <th>🖨️ مخزون مطبوع</th>
                                        <th>👕 مخزون سادة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($variants->groupBy('size_id') as $sizeVariants)
                                        @php $variant = $sizeVariants->first(); @endphp
                                        <tr>
                                            <td class="size-name-cell"><strong>{{ $variant->size->name }}</strong></td>
                                            <td><span class="qty-badge total">{{ $variant->stocks->sum('quantity') }}</span>
                                            </td>
                                            <td>
                                                <div class="stock-action-wrapper">
                                                    <span
                                                        class="qty-num printed-text">{{ $variant->printedStock->quantity ?? 0 }}</span>
                                                    <button class="btn-add-stock printed"
                                                        onclick="openAddStock({{ $variant->id }}, 1)">
                                                        <span>➕</span> مطبوع
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="stock-action-wrapper">
                                                    <span
                                                        class="qty-num plain-text">{{ $variant->plainStock->quantity ?? 0 }}</span>
                                                    <button class="btn-add-stock plain"
                                                        onclick="openAddStock({{ $variant->id }}, 0)">
                                                        <span>➕</span> سادة
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach



            </div>



            <div class="section">
                <div class="box">
                    <ul class="mini-list">
                        <li><strong>تاريخ إضافة المنتج :</strong> {{ $product->created_at->format('Y-m-d H:i') }}</li>
                        <li>
                            <strong>آخر إضافة للمخزون:</strong>
                            @php
                                // جلب آخر سجل مخزون مضاف لهذا المنتج عبر التنوعات
                                $lastStock = $product->variants->flatMap->stocks->sortByDesc('created_at')->first();
                            @endphp

                            @if ($lastStock)
                                {{ $lastStock->created_at->format('Y-m-d H:i') }}
                                <span style="font-size: 11px; color: #777;">
                                    ({{ $lastStock->is_printed ? 'مطبوع' : 'سادة' }})
                                </span>
                            @else
                                <span class="muted">لا يوجد مخزون مضاف بعد</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>




    <!-- Add Quantity Modal -->
    <div class="modal fade" id="addQtyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">إضافة كمية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" id="addQtyForm">
                    @csrf

                    <div class="modal-body">
                        <label class="form-label">الكمية</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <input type="hidden" name="is_printed" id="isPrinted">

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            حفظ
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
    <!-- Add Color Modal -->
    <div class="modal fade" id="addColorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">➕ إضافة لون جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('variants.storeColor', $product->id) }}">
                    @csrf

                    <div class="modal-body">

                        {{-- اللون --}}
                        <div class="mb-3">
                            <label class="form-label">🎨 اللون</label>
                            <select name="color_id" class="form-control" required>
                                @foreach ($allColors as $color)
                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- المقاسات --}}
                        <label class="form-label">📐 المقاسات</label>

                        @foreach ($allSizes as $size)
                            <div class="d-flex align-items-center mb-2 gap-2">
                                <input type="checkbox" name="sizes[{{ $size->id }}][enabled]">
                                <span>{{ $size->name }}</span>

                                <input type="number" name="sizes[{{ $size->id }}][quantity]" placeholder="الكمية"
                                    min="0" class="form-control" style="width:120px">
                            </div>
                        @endforeach

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            حفظ اللون
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
<div class="modal fade" id="editProductNameModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✏️ تعديل اسم المنتج</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('products.updateName', $product->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم المنتج الجديد</label>
                        <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">تحديث الاسم</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditProductNameModal() {
        new bootstrap.Modal(document.getElementById('editProductNameModal')).show();
    }
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // وظيفة عامة لفتح أي مودال بسلاسة بدون تظليل
        function openModal(modalId) {
            const modalEl = document.getElementById(modalId);

            // إزالة أي fade قديم
            modalEl.querySelector('.modal-dialog').classList.remove('animate__fadeInUp');

            // إضافة Fade animation
            modalEl.querySelector('.modal-dialog').classList.add('animate__animated', 'animate__fadeInUp');

            // تفعيل المودال بدون backdrop مظلل
            const modal = new bootstrap.Modal(modalEl, {
                backdrop: false,
                keyboard: true
            });

            modal.show();
        }

        // فتح مودال تعديل المنتج
        $('#openEditModal').on('click', function(e) {
            e.preventDefault();
            $('#edit_name').val(`{{ $product->name }}`);
            $('#edit_price').val(`{{ $product->price }}`);
            $('#edit_cost').val(`{{ $product->cost }}`);
            $('#edit_quantity').val(`{{ $product->quantity }}`);

            openModal('editProductModal');
        });

        // فتح مودال إضافة كمية
        function openAddQty(variantId) {
            const form = document.getElementById('addQtyForm');
            form.action = `/variants/${variantId}/add-quantity`;
            openModal('addQtyModal');
        }

        // فتح مودال إضافة لون
        function openAddColorModal() {
            openModal('addColorModal');
        }

        // فتح مودال تعديل المنتج المهم
        $(document).on('click', '.edit-important-btn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const productName = $(this).data('product');
            const productId = $(this).data('product-id');

            $('#editName').val(name);
            $('#editSelectedProductText').text(productName);
            $('#editSelectedProduct').show();
            $('#editProductId').val(productId);

            $('#editForm').attr('action', `/important-products/${id}`);
            openModal('editModal');
        });
    </script>


    <script>
        function toggleSizes(colorId) {
            const box = document.getElementById(`sizes-${colorId}`);
            const icon = box.previousElementSibling.querySelector('.toggle-icon');

            if (box.classList.contains('d-none')) {
                box.classList.remove('d-none');
                icon.style.transform = 'rotate(180deg)';
            } else {
                box.classList.add('d-none');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
    <script>
        function openAddQty(variantId) {
            const form = document.getElementById('addQtyForm');

            form.action = `/variants/${variantId}/add-quantity`;

            const modal = new bootstrap.Modal(
                document.getElementById('addQtyModal')
            );

            modal.show();
        }

        function openAddStock(variantId, isPrinted) {
            const form = document.getElementById('addQtyForm');
            form.action = `/variants/${variantId}/add-stock`;

            document.getElementById('isPrinted').value = isPrinted;

            new bootstrap.Modal(
                document.getElementById('addQtyModal')
            ).show();
        }
    </script>
    <script>
        function openAddColorModal() {
            const modal = new bootstrap.Modal(
                document.getElementById('addColorModal')
            );
            modal.show();
        }
    </script>


@endsection

@section('style')
    <style>
        <style> :root {
            --primary-color: #515831;
            --secondary-color: #79c879;
            --text-dark: #2d3436;
            --bg-light: #f8f9fa;
            --border-radius: 16px;
        }

        .main-product-card {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eee;
        }

        /* Header Section */
        .product-header-section {
            background: linear-gradient(135deg, #515831 0%, #3a4124 100%);
            padding: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .product-title {
            font-size: 28px;
            margin: 5px 0;
            font-weight: 800;
        }

        .product-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            opacity: 0.7;
        }

        /* Variants Section */
        .variants-section {
            padding: 25px;
        }

        .section-title-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Color Groups */
        .color-group {
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .color-header {
            background: #fdfdfd;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background 0.2s;
        }

        .color-header:hover {
            background: #f1f3f0;
        }

        .color-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .color-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
        }

        .color-name {
            font-weight: 700;
            font-size: 18px;
            color: var(--text-dark);
        }

        .total-qty {
            background: #e9ecef;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        /* Sizes Table */
        .sizes-container {
            padding: 0 20px 20px;
            background: #fff;
        }

        .sizes-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .sizes-table th {
            color: #888;
            font-size: 13px;
            font-weight: 600;
            padding: 10px;
            text-align: right;
        }

        .sizes-table td {
            background: #fcfcfc;
            padding: 12px;
            border-top: 1px solid #f1f1f1;
            border-bottom: 1px solid #f1f1f1;
        }

        .sizes-table td:first-child {
            border-right: 1px solid #f1f1f1;
            border-radius: 0 10px 10px 0;
        }

        .sizes-table td:last-child {
            border-left: 1px solid #f1f1f1;
            border-radius: 10px 0 0 10px;
        }

        /* Stock Control Logic */
        .stock-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-count {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 16px;
            min-width: 30px;
        }

        .qty-badge.total {
            background: #51583120;
            color: #515831;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .plus-btn {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            border: none;
            background: var(--secondary-color);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        .plus-btn.plain {
            background: #6c757d;
        }

        .plus-btn:hover {
            transform: scale(1.1);
        }

        /* Buttons */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-modern.edit {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-modern.edit:hover {
            background: white;
            color: var(--primary-color);
        }

        .btn-modern.success {
            background: var(--secondary-color);
            color: white;
        }

        .d-none {
            display: none;
        }
    </style>

    <style>
        :root {
            --primary: #515831;
            --printed-color: #28a745;
            /* أخضر للمطبوع */
            --plain-color: #495057;
            /* رمادي داكن للسادة */
            --bg-light: #f8f9fa;
        }

        .product-container {
            max-width: 1000px;
            margin: 20px auto;
            font-family: 'Cairo', sans-serif;
        }

        .main-product-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Header */
        .product-header-section {
            background: var(--primary);
            padding: 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-title {
            font-size: 24px;
            margin: 0;
            font-weight: 700;
        }

        /* Table Styling */
        .variants-section {
            padding: 20px;
        }

        .sizes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .sizes-table th {
            padding: 12px;
            color: #777;
            font-size: 14px;
            text-align: center;
            border-bottom: 2px solid #eee;
        }

        .sizes-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #f1f1f1;
        }

        /* Stock Actions */
        .stock-action-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .qty-num {
            font-size: 18px;
            font-weight: 800;
        }

        .printed-text {
            color: var(--printed-color);
        }

        .plain-text {
            color: var(--plain-color);
        }

        /* الأزرار الجديدة */
        .btn-add-stock {
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
            color: white;
            min-width: 110px;
            justify-content: center;
        }

        /* زر المطبوع (أخضر) */
        .btn-add-stock.printed {
            background-color: var(--printed-color);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.2);
        }

        .btn-add-stock.printed:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        /* زر السادة/غير المطبوع (رمادي احترافي) */
        .btn-add-stock.plain {
            background-color: var(--plain-color);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-add-stock.plain:hover {
            background-color: #343a40;
            transform: translateY(-2px);
        }

        .qty-badge.total {
            background: #eee;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        /* Utility */
        .d-none {
            display: none;
        }

        .color-group {
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .color-header {
            background: #fdfdfd;
            padding: 15px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
        }
    </style>
    <style>
        body {
            background: #fafafa;
            font-family: "Cairo", sans-serif;
        }

        .product-container {
            max-width: 960px;
            margin: 40px auto;
            padding: 20px;
            position: relative;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 26px;
        }

        .card.fade-in {
            animation: fadeInUp 0.6s ease;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f1f1;
            margin-bottom: 16px;
            padding-bottom: 10px;
        }

        .card-header h2 {
            font-size: 24px;
            color: #2b2b2b;
            margin: 0;
        }

        .badge {
            background: #515831;
            color: #fff;
            padding: 6px 12px;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-dialog {
            border-radius: 16px;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .modal-backdrop {
            display: none;
            /* إزالة التظليل */
        }

        .section h3 {
            color: #515831;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .box {
            background: #fafafa;
            padding: 14px 18px;
            border-radius: 12px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.04);
            margin-bottom: 18px;
            font-size: 15px;
            line-height: 1.6;
        }

        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }

        .col {
            flex: 1;
        }

        .lbl {
            display: block;
            font-weight: 600;
            color: #555;
        }

        .value {
            font-size: 17px;
            font-weight: 700;
            color: #222;
        }

        .flex-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 14px;
        }

        .status-card {
            background: #fff;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .d-check {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .d-check input {
            transform: scale(1.2);
        }

        .actions {
            margin-top: 6px;
        }

        .btn.small {
            background: #515831;
            color: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
            transition: all .18s ease;
        }

        .btn.small:hover {
            transform: translateY(-3px);
            background: #515831;
        }

        .btn.small.success {
            background: #79c879;
        }

        .muted {
            color: #888;
        }

        .mini-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
            color: #333;
        }

        .mini-list li {
            margin-bottom: 8px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
