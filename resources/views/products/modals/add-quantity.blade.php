<!-- Modal إضافة كمية -->
<div class="modal fade" id="addQuantityModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom rounded-4 shadow-lg">
            <div class="modal-header modal-header-custom rounded-top-4">
                <button type="button" class="btn-close me-auto" data-bs-dismiss="modal"></button>
                <h5 class="modal-title mx-auto">إضافة كمية</h5>
            </div>
            <div class="modal-body p-4">
                <input type="text" id="searchProduct" class="form-control form-control-lg mb-3"
                    placeholder="ابحث عن المنتج...">
                <ul id="searchResults" class="list-group mb-4"></ul>

             <form id="addQuantityForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="product_id" id="product_id">

    <div class="mb-3">
        <label class="form-label">الكمية المراد إضافتها</label>
        <input type="number" name="quantity" class="form-control form-control-lg" required>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-gradient btn-lg px-5 fw-bold">إضافة</button>
    </div>
</form>


            </div>
        </div>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchProduct');
    const resultsList = document.getElementById('searchResults');
    const form = document.getElementById('addQuantityForm');
    const productIdInput = document.getElementById('product_id');

    searchInput.addEventListener('keyup', function () {
        const query = this.value.trim();

        if (!query) {
            resultsList.innerHTML = '';
            form.style.display = 'none';
            return;
        }

        fetch(`{{ route('products.search') }}?query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                resultsList.innerHTML = '';

                if (!data.length) {
                    resultsList.innerHTML = `<li class="list-group-item text-center text-muted">لا توجد نتائج</li>`;
                    form.style.display = 'none';
                    return;
                }

                data.forEach(product => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item list-group-item-action';
                    li.textContent = `${product.name} | الكمية: ${product.quantity}`;
                    li.style.cursor = 'pointer';
                    li.addEventListener('click', () => {
                        productIdInput.value = product.id;
                        searchInput.value = product.name;
                        resultsList.innerHTML = '';

                        // حدث action الفورم حسب id المنتج
                        form.action = `/products/${product.id}/add-quantity`;

                        form.style.display = 'block';
                    });
                    resultsList.appendChild(li);
                });
            })
            .catch(err => {
                console.error(err);
                resultsList.innerHTML = `<li class="list-group-item text-center text-danger">حدث خطأ</li>`;
            });
    });
});

</script>
<style>
    /* مودال حسب الثيم */
    .modal-custom {
        background: rgba(221, 205, 188, 0.15);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(221, 205, 188, 0.3);
        color: var(--white);
        transform: translateX(-200%) rotate(-10deg);
        transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }

    .modal.show .modal-custom {
        transform: translateX(0) rotate(0deg);
    }

    .modal-header-custom {
        background: linear-gradient(135deg, var(--prime), var(--prime-soft));
        color: var(--bg);
        font-weight: 700;
        font-size: 18px;
        border-bottom: none;
        display: flex;
        justify-content: center;
        position: relative;
    }

    /* زر الإغلاق */
    .modal-header-custom .btn-close {
        filter: brightness(0.9);
    }

    /* أزرار المودال */
    .btn-gradient {
        background: linear-gradient(135deg, var(--prime), var(--prime-soft));
        color: var(--bg);
        font-weight: 900;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .btn-gradient:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
    }

    .btn-gradient-alt {
        background: linear-gradient(135deg, var(--bg-dark), var(--bg));
        color: var(--prime);
        font-weight: 900;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .btn-gradient-alt:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
    }
</style>
