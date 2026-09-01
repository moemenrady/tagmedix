@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Stock Out (صرف مخزون)</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('inventory.stock-out.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <!-- اختيار الصنف -->
                    <div class="col-md-6">
                        <label class="form-label">Inventory Item</label>
                        <select name="inventory_item_id" id="item_selector" class="form-select" required>
                            <option value="">Select Item...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- اختيار التوب (يظهر ديناميكياً) -->
                    <div class="col-md-6" id="unit_selector_container" style="display: none;">
                        <label class="form-label text-warning fw-bold">Select Top to Issue (اختر التوب)</label>
                        <select name="inventory_unit_id" id="unit_selector" class="form-select">
                            <!-- Options populated via JS -->
                        </select>
                    </div>

                    <!-- الكمية (تكون للقراءة فقط عند صرف توب كامل) -->
                    <div class="col-md-6">
                        <label class="form-label">Quantity</label>
                        <input type="number" step="0.01" name="quantity" id="quantity_input" class="form-control" required>
                        <small id="qty_help" class="text-muted" style="display: none;">يتم صرف التوب بالكامل تلقائياً.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning">Issue Stock</button>
                    <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // تمرير البيانات من الـ Backend للـ JS بسلاسة
    const itemsData = @json($items);

    document.getElementById('item_selector').addEventListener('change', function() {
        let itemId = this.value;
        let selectedItem = itemsData.find(i => i.id == itemId);
        
        let unitContainer = document.getElementById('unit_selector_container');
        let unitSelector = document.getElementById('unit_selector');
        let qtyInput = document.getElementById('quantity_input');
        let qtyHelp = document.getElementById('qty_help');

        // تنظيف القائمة
        unitSelector.innerHTML = '<option value="">Select a top...</option>';

        if (selectedItem && selectedItem.track_as_units) {
            // إظهار اختيار التوب وجعله إجبارياً
            unitContainer.style.display = 'block';
            unitSelector.required = true;
            
            // إضافة التوبات المتاحة
            selectedItem.units.forEach(unit => {
                let option = document.createElement('option');
                option.value = unit.id;
                option.setAttribute('data-qty', unit.quantity);
                option.text = `Code: ${unit.code} (${unit.quantity} ${unit.unit})`;
                unitSelector.appendChild(option);
            });

            qtyInput.readOnly = true;
            qtyHelp.style.display = 'block';
        } else {
            // صنف عادي (كميات)، إخفاء التوبات
            unitContainer.style.display = 'none';
            unitSelector.required = false;
            
            qtyInput.readOnly = false;
            qtyInput.value = '';
            qtyHelp.style.display = 'none';
        }
    });

    // تعبئة الكمية تلقائياً عند اختيار توب
    document.getElementById('unit_selector').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];
        if(selectedOption.value) {
            document.getElementById('quantity_input').value = selectedOption.getAttribute('data-qty');
        }
    });
</script>
@endsection