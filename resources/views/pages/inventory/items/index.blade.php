@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Inventory Items</h2>
        <a href="{{ route('inventory.items.create') }}" class="btn btn-primary">
            + Add New Item
        </a>
    </div>

    <!-- Flash Messages (Alerts) -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Filters Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('inventory.items.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, code, or color..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Table Section -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Color</th>
                            <th>Current Stock</th>
                            <th>Inventory Value</th>
                            <th>Tracking</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <span class="fw-bold text-muted">{{ $item->code ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $item->category->name }}</span>
                                </td>
                                <td>
                                    {{ $item->color ?? '-' }}
                                </td>
                                <td>
                                    <!-- Dynamic Unit Display -->
                                    <span class="fw-bold">
                                        {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                                    </span>
                                </td>
                                <td>
                                    {{ number_format($item->inventory_value, 2) }} EGP
                                </td>
                                <td>
                                    <!-- توضيح طريقة تتبع الصنف بناءً على track_as_units -->
                                    @if($item->track_as_units)
                                        <span class="badge bg-secondary">Units / Tops</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Quantity</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- الاعتماد على حقل is_active -->
                                    @if($item->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('inventory.items.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                    <a href="{{ route('inventory.items.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <!-- Empty State -->
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="mb-3">
                                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                    </div>
                                    <h5>No inventory items found.</h5>
                                    <p class="mb-3">Get started by creating a new inventory item.</p>
                                    <a href="{{ route('inventory.items.create') }}" class="btn btn-primary">Add Item</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        @if($items->hasPages())
            <div class="card-footer bg-white border-0 pt-4 pb-2">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection