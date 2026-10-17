<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Requests\StockOutRequest;
use App\Modules\Inventory\Services\InventoryStockOutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryStockOutController extends Controller
{
    public function __construct(
        private readonly InventoryStockOutService $stockOutService
    ) {}

    public function create(): View
    {
        return view('pages.inventory.stock-out.create');
    }

    public function store(
        StockOutRequest $request
    ): RedirectResponse {
        $this->stockOutService->execute(
            data: $request->validated(),
            userId: auth()->id()
        );

        return redirect()
            ->route('inventory.dashboard')
            ->with(
                'success',
                'Stock has been issued successfully.'
            );
    }
}