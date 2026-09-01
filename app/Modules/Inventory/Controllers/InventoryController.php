<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Modules\Inventory\Services\InventoryValuationService;
use App\Modules\Inventory\Models\InventoryTransaction;
use Illuminate\Http\Request;
use App\Modules\Inventory\Data\InventoryStockData;
use App\Modules\Inventory\Requests\StoreInventoryStockRequest;
use App\Modules\Inventory\Services\InventoryReceivingService;
use Illuminate\Http\RedirectResponse;

class InventoryController extends Controller
{
    protected $valuationService;

    public function __construct(InventoryValuationService $valuationService)
    {
        // حقن الخدمة المسؤولة عن حسابات وقيم المخزون
        $this->valuationService = $valuationService;
    }

    /**
     * عرض لوحة التحكم الرئيسية للمخازن (Dashboard)
     */
    public function dashboard(Request $request)
    {

        // 3. تمرير البيانات إلى واجهة الـ Blade
        return view('pages.inventory.dashboard',);
    }

    public function create()
    {
        $colors = Color::all();
        return view('pages.inventory.create', compact("colors"));
    }


    public function store(
        StoreInventoryStockRequest $request,
        InventoryReceivingService $service
    ): RedirectResponse {
        $service->receive(
            data: InventoryStockData::fromArray(
                $request->validated()
            ),
            userId: auth()->id()
        );

        return redirect()
            ->route('inventory.stocks.index')
            ->with(
                'success',
                'Inventory received successfully.'
            );
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
