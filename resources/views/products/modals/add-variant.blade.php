<!-- Modal إضافة منتج جديد -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-custom rounded-4 shadow-lg">
            <div class="modal-header modal-header-custom rounded-top-4">
                <button type="button" class="btn-close me-auto" data-bs-dismiss="modal"></button>
                <h5 class="modal-title mx-auto">منتج جديد</h5>
            </div>

            <form action="{{ route('variants.store') }}" method="POST">
                @csrf

                <select name="product_id" required>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>

                <select name="color_id" required>
                    @foreach ($colors as $color)
                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                    @endforeach
                </select>

                <select name="size_id" required>
                    @foreach ($sizes as $size)
                        <option value="{{ $size->id }}">{{ $size->name }}</option>
                    @endforeach
                </select>

                <input type="number" name="price" placeholder="سعر البيع" required>
                <input type="number" name="cost" placeholder="التكلفة" required>
                <input type="number" name="quantity" placeholder="الكمية" required>
                <input type="number" name="min_quantity" placeholder="الحد الأدنى" required>

                <button class="btn btn-gradient mt-3">حفظ الصنف</button>
            </form>


        </div>
    </div>
</div>

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
