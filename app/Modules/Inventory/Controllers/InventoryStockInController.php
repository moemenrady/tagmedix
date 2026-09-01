<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Requests\StockInRequest;
use App\Modules\Inventory\Services\InventoryStockInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryStockInController extends Controller
{
    public function __construct(
        private readonly InventoryStockInService $stockInService
    ) {}

    public function create(): View
    {
        return view('pages.inventory.stock-in.create');
    }

    public function store(
        StockInRequest $request
    ): RedirectResponse {
        $this->stockInService->execute(
            data: $request->validated(),
            userId: auth()->id()
        );

        return redirect()
            ->route('inventory.dashboard')
            ->with(
                'success',
                'Stock has been received successfully.'
            );
    }
}