@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.items.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $item->name }}</li>
                </ol>
            </nav>
            <h2 class="mb-0">
                {{ $item->name }} 
                @if($item->color)
                    <span class="text-muted">({{ $item->color }})</span>
                @endif
            </h2>
        </div>
        <div>
            <a href="{{ route('inventory.items.edit', $item->id) }}" class="btn btn-outline-secondary">
                Edit Item
            </a>
            <!-- أزرار الإدخال والصرف السريعة -->
            <button type="button" class="btn btn-success ms-2">Stock In</button>
            <button type="button" class="btn btn-warning ms-2">Stock Out</button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Total Stock</h6>
                    <!-- Dynamic Unit Display -->
                    <h3 class="mb-0">{{ number_format($currentStock, 2) }} <small class="text-muted fs-6">{{ $item->unit }}</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Inventory Value</h6>
                    <h3 class="mb-0">{{ number_format($inventoryValue, 2) }} <small class="text-muted fs-6">EGP</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Cost Per {{ ucfirst($item->unit) }}</h6>
                    <h3 class="mb-0">{{ number_format($item->cost_per_unit, 2) }} <small class="text-muted fs-6">EGP</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-2">Status & Tracking</h6>
                    <h5 class="mb-1">
                        @if($item->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </h5>
                    <small class="text-muted">
                        {{ $item->track_as_units ? 'Tracked by Units (Tops)' : 'Tracked by Total Quantity' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Units (Tops) Table - يظهر فقط للأصناف التي تتبع الوحدات -->
    @if($item->track_as_units)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Available Units / Tops</h5>
            <span class="badge bg-primary rounded-pill">{{ $availableUnits->count() }} Tops</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Top Code</th>
                            <th>Quantity</th>
                            <th>Cost / {{ ucfirst($item->unit) }}</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($availableUnits as $unit)
                            <tr>
                                <td><strong class="text-primary">{{ $unit->code }}</strong></td>
                                <td>{{ number_format($unit->quantity, 2) }} {{ $unit->unit }}</td>
                                <td>{{ number_format($unit->cost_per_unit, 2) }} EGP</td>
                                <td>{{ number_format($unit->total_cost, 2) }} EGP</td>
                                <td><span class="badge bg-success">{{ ucfirst($unit->status) }}</span></td>
                                <td>{{ $unit->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <!-- Issue Top Form -->
                                    <form action="#" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to issue Top {{ $unit->code }} ({{ $unit->quantity }} {{ $unit->unit }})?');">
                                        @csrf
                                        <input type="hidden" name="inventory_unit_id" value="{{ $unit->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Issue Top</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No available units found. Add stock to see units here.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Transactions History -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Recent Transactions History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Top / Unit</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>User</th>
                            <th class="text-end">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <!-- Transaction Type Badges -->
                                    @if($transaction->type == 'stock_in')
                                        <span class="badge bg-success">Stock In</span>
                                    @elseif($transaction->type == 'stock_out')
                                        <span class="badge bg-warning text-dark">Stock Out</span>
                                    @elseif($transaction->type == 'adjustment')
                                        <span class="badge bg-info text-dark">Adjustment</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $transaction->unit_id ? $transaction->unit->code : '-' }}
                                </td>
                                <td>
                                    <!-- إظهار الكمية بالموجب أو السالب للتوضيح -->
                                    <span class="{{ in_array($transaction->type, ['stock_out']) || $transaction->quantity < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                        {{ $transaction->quantity > 0 && !in_array($transaction->type, ['stock_out']) ? '+' : '' }}{{ number_format($transaction->quantity, 2) }} {{ $transaction->unit }}
                                    </span>
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $transaction->reason ?? 'N/A')) }}</td>
                                <td>{{ $transaction->user->name ?? 'System' }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light border">Details</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 text-center">
            <a href="#" class="text-decoration-none">View All Transactions &rarr;</a>
        </div>
    </div>

</div>
@endsection