@extends('layouts.app')

@section('page_title', 'المخزن')

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
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .container {
        max-width: 1300px;
        margin: auto;
        padding: 30px 20px;
    }

    /* العدادين */
    .stats-row {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    .stats-box {
        background: rgba(221, 205, 188, 0.15);
        backdrop-filter: blur(14px);
        padding: 20px 30px;
        border-radius: 18px;
        text-align: center;
        min-width: 220px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
    }

    .stats-box p:first-child {
        color: var(--prime-soft);
        font-weight: 700;
    }

    .stats-box p:last-child {
        font-size: 28px;
        font-weight: 900;
        color: var(--prime);
    }

    /* زر الإضافة الجديد */
    #addButton {
        top: 80px;
        /* المسافة من الأعلى */
        right: 40px;
        /* المسافة من اليمين */
        width: 120px;
        height: 50px;
        border-radius: 12px;
        /* مش دايرة كاملة */
        border: none;
        background: linear-gradient(135deg, var(--prime), var(--prime-soft));
        color: var(--bg);
        font-size: 28px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .35);
        transition: .3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #addButton:hover {
        transform: scale(1.05) translateY(-2px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
    }



    /* البحث */
    .search-box {
        text-align: center;
        margin-bottom: 30px;
    }

    .search-box input {
        width: 420px;
        max-width: 100%;
        padding: 14px 20px;
        border-radius: 20px;
        border: none;
        outline: none;
        background: rgba(221, 205, 188, 0.2);
        color: var(--white);
        font-size: 15px;
    }

    .search-box input::placeholder {
        color: var(--prime-soft);
    }

    /* كروت المنتجات */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 22px;
    }

    .product-card {
        background: rgba(221, 205, 188, 0.15);
        backdrop-filter: blur(14px);
        border-radius: 20px;
        padding: 18px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, .35);
        cursor: pointer;
        transition: .3s ease;
        border: 1px solid rgba(221, 205, 188, 0.25);
    }

    .product-card:hover {
        transform: translateY(-6px) scale(1.02);
        background: rgba(221, 205, 188, 0.25);
    }

    .product-title {
        font-weight: 900;
        font-size: 17px;
        color: var(--prime);
        margin-bottom: 8px;
    }

    .product-info {
        font-size: 14px;
        color: var(--prime-soft);
        margin: 4px 0;
    }

    .product-badge {
        display: inline-block;
        margin-top: 10px;
        padding: 6px 12px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--prime), var(--prime-soft));
        color: var(--bg);
        font-weight: 800;
        font-size: 13px;
    }
</style>

@section('content')
    <div class="container">

        {{-- الإشعارات --}}
        @if (session('success'))
            <div class="snackbar success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="snackbar error">{{ session('error') }}</div>
        @endif

        {{-- العدادين + زر الإضافة --}}
        <div class="stats-row">
            <div class="stats-box">
                <p>عدد المنتجات</p>
                <p>{{ $countProducts }}</p>
            </div>

            <button id="addButton" data-bs-toggle="modal" data-bs-target="#chooseActionModal">+</button>


        </div>

        {{-- البحث --}}
        <div class="search-box">
            <input type="text" id="searchBox" placeholder="🔍 ابحث عن منتج">
        </div>

        <div class="products-grid" id="productsGrid">

            @foreach ($products as $product)
                <div class="product-card" onclick="window.location='{{ route('products.show', $product->id) }}'">

                    <div class="product-title">{{ $product->name }}</div>

                    <div class="product-info">
                        📦 إجمالي القطع:
                        {{ $product->variants->flatMap->stocks->sum('quantity') }}
                    </div>

                    <div class="product-info">
                        🎨 عدد الألوان:
                        {{ $product->variants->groupBy('color_id')->count() }}
                    </div>

                </div>
            @endforeach

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const searchInput = document.getElementById('searchBox');
            const productsGrid = document.getElementById('productsGrid');

            function loadProducts(query = '') {
                // لو query فيها : أو فاضي
                query = query.trim();

                fetch(`/products/search?query=${encodeURIComponent(query)}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Server error');
                        return res.json();
                    })
                    .then(products => {

                        productsGrid.innerHTML = '';

                        if (!products.length) {
                            productsGrid.innerHTML = `
                        <div style="grid-column:1/-1;text-align:center;color:#ddcdbc">
                            لا توجد منتجات
                        </div>
                    `;
                            return;
                        }

                        products.forEach(product => {
                            const card = document.createElement('div');
                            card.className = 'product-card';
                            card.onclick = () => {
                                window.location = `/products/${product.id}`;
                            };

                            card.innerHTML = `
                        <div class="product-title">${product.name}</div>

                        <div class="product-info">
                            📦 إجمالي القطع: ${product.total_quantity}
                        </div>

                        <div class="product-info">
                            🎨 عدد الألوان: ${product.colors_count}
                        </div>
                    `;

                            productsGrid.appendChild(card);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        productsGrid.innerHTML = 'حدث خطأ';
                    });
            }



            // 🔥 تحميل المنتجات أول ما الصفحة تفتح
            loadProducts();

            // 🔍 البحث Live
            searchInput.addEventListener('keyup', function() {
                loadProducts(this.value.trim());
            });

        });
    </script>


    @include('products.modals.choose-action')
    @include('products.modals.add-product')
    @include('products.modals.add-quantity')

@endsection
