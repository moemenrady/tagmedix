@extends('layouts.app_page') {{-- نفس layout بتاع show --}}

@section('content')
<div class="container">
    <h3 class="mb-4">
        ➕ إضافة لون ومقاسات
        <small class="text-muted">{{ $product->name }}</small>
    </h3>

    <form method="POST" action="{{ route('variants.store', $product->id) }}">
        @csrf

        {{-- 🎨 اللون --}}
        <div class="card mb-4">
            <div class="card-header">🎨 اللون</div>
            <div class="card-body">
                <select name="color_id" class="form-control mb-2">
                    <option value="">— اختر لون موجود —</option>
                    @foreach ($colors as $color)
                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                    @endforeach
                </select>

                <input type="text"
                       name="new_color"
                       class="form-control"
                       placeholder="أو أضف لون جديد">
            </div>
        </div>

        {{-- 📐 المقاسات --}}
        <div class="card mb-4">
            <div class="card-header">📐 المقاسات</div>
            <div class="card-body">
                <div class="row">
                    @foreach ($sizes as $size)
                        <div class="col-md-3 mb-2">
                            <label class="d-flex align-items-center gap-2">
                                <input type="checkbox" name="sizes[]" value="{{ $size->id }}">
                                {{ $size->name }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <input type="text"
                       name="new_sizes"
                       class="form-control mt-3"
                       placeholder="أضف مقاسات جديدة (مثال: XXL, 4XL)">
            </div>
        </div>

        {{-- 💰 السعر --}}
        <div class="card mb-4">
            <div class="card-header">💰 السعر</div>
            <div class="card-body row">
                <div class="col-md-6">
                    <label>سعر البيع</label>
                    <input type="number"
                           step="0.01"
                           name="price"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6">
                    <label>التكلفة</label>
                    <input type="number"
                           step="0.01"
                           name="cost"
                           class="form-control"
                           required>
                </div>
            </div>
        </div>

        <button class="btn btn-success btn-lg">
            💾 حفظ وتكوين المنتج
        </button>
    </form>
</div>
@endsection
