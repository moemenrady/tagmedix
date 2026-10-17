@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4">Stock In (إدخال مخزون)</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('inventory.stock-in.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <!-- اختيار الصنف -->
                    <div class="col-md-6">
                        <label class="form-label">Inventory Item</label>
                        <select name="inventory_item_id" id="item_selector" class="form-select" required>
                            <option value="">Select Item...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-track="{{ $item->track_as_units }}">
                                    {{ $item->name }} ({{ $item->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- الكمية -->
                    <div class="col-md-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" step="0.01" name="quantity" class="form-control" required>
                    </div>

                    <!-- التكلفة -->
                    <div class="col-md-3">
                        <label class="form-label">Cost per Unit</label>
                        <input type="number" step="0.01" name="cost_per_unit" class="form-control" required>
                    </div>

                    <!-- كود التوب (يظهر ديناميكياً) -->
                    <div class="col-md-6" id="top_code_container" style="display: none;">
                        <label class="form-label text-primary fw-bold">Top Code (كود التوب)</label>
                        <input type="text" name="code" id="top_code_input" class="form-control" placeholder="e.g. TOP-001">
                        <small class="text-muted">مطلوب لأن هذا الصنف يُتتبع كتوبات مستقلة.</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Stock In</button>
                    <a href="{{ route('inventory.items.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('item_selector').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];
        let tracksUnits = selectedOption.getAttribute('data-track') == '1';
        
        let container = document.getElementById('top_code_container');
        let input = document.getElementById('top_code_input');

        if (tracksUnits) {
            container.style.display = 'block';
            input.required = true;
        } else {
            container.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    });
</script>
@endsection