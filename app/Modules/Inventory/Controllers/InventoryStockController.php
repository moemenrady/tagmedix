<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\PantType;
use App\Models\Size;
use App\Models\SleeveType;
use App\Models\Style;
use App\Models\Type;
use App\Modules\Inventory\DTOs\CreateInventoryStockData;
use App\Modules\Inventory\Requests\StoreInventoryStockRequest;
use App\Modules\Inventory\Services\InventoryStockInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryStockController extends Controller
{
    public function __construct(
        private readonly InventoryStockInService $stockInService
    ) {}

    public function create(): View
    {
        $colors = Color::all();
        $sizes = Size::all();
        $styles = Style::all();
        $types = Type::all();
        $pantTypes = PantType::all();
        $sleeveType = SleeveType::all();
        return view(
            'pages.inventory.stock.create',
            compact('colors', 'sizes', 'styles', 'types', 'pantTypes', 'sleeveType')
        );
    }

    public function store(
        StoreInventoryStockRequest $request
    ): RedirectResponse {

        $data = CreateInventoryStockData::fromArray(
            $request->validated(),
            auth()->id()
        );

        $item = $this->stockInService->execute($data);


        return redirect()
            ->route('inventory.dashboard')
            ->with(
                'success',
                "تم إضافة المخزون بنجاح. كود الصنف: {$item->code}"
            );
    }
}
