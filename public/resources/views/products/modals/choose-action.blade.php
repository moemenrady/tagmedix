<!-- Modal اختيار العملية -->
<div class="modal fade" id="chooseActionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom rounded-4 shadow-lg">
            <div class="modal-header modal-header-custom rounded-top-4 d-flex align-items-center">
                <button type="button" class="btn-close me-auto" data-bs-dismiss="modal"></button>
                <h5 class="modal-title mx-auto"> هتعمل ايه ؟</h5>
            </div>
            <div class="modal-body text-center py-4">
                <button class="btn btn-lg w-100 mb-3 fw-bold btn-gradient" data-bs-target="#addProductModal"
                    data-bs-toggle="modal" data-bs-dismiss="modal">
                هضيف منتج
                </button>

               
            </div>
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
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-gradient:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 28px rgba(0,0,0,0.45);
}

.btn-gradient-alt {
    background: linear-gradient(135deg, var(--bg-dark), var(--bg));
    color: var(--prime);
    font-weight: 900;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-gradient-alt:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 28px rgba(0,0,0,0.45);
}
</style>
