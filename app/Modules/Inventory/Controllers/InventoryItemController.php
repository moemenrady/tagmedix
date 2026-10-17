<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\InventoryCategory;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Requests\StoreInventoryItemRequest;
use App\Modules\Inventory\Requests\UpdateInventoryItemRequest;
use App\Modules\Inventory\Services\InventoryStockService;
use App\Modules\Inventory\Services\InventoryValuationService;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    protected InventoryValuationService $valuationService;

    protected InventoryStockService $stockService;

    public function __construct(
        InventoryValuationService $valuationService,
        InventoryStockService $stockService
    ) {
        $this->valuationService = $valuationService;
        $this->stockService = $stockService;
    }

    /**
     * Display inventory items.
     */
    public function index(Request $request)
    {
        $query = InventoryItem::query()
            ->with('category')
            ->latest();

        /*
         * Search by:
         * - Name
         * - Code
         * - Color
         */
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            });
        }

        /*
         * Filter by category.
         */
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->paginate(15)->withQueryString();

        /*
         * Add calculated stock/value to every item.
         *
         * This is intentionally done here so the Blade
         * does not contain inventory calculations.
         */
        $items->getCollection()->transform(function (InventoryItem $item) {
            $item->current_stock = $this->getCurrentStock($item);

            $item->inventory_value = $this->valuationService
                ->getItemValue($item);

            return $item;
        });

        $categories = InventoryCategory::query()
            ->orderBy('name')
            ->get();

        return view('inventory.items.index', compact(
            'items',
            'categories'
        ));
    }

    /**
     * Show create item page.
     */
    public function create()
    {
        $categories = InventoryCategory::query()
            ->orderBy('name')
            ->get();

        return view('inventory.items.create', compact(
            'categories'
        ));
    }

    /**
     * Store a new inventory item.
     */
    public function store(StoreInventoryItemRequest $request)
    {
        $validated = $request->validated();

        InventoryItem::create($validated);

        return redirect()
            ->route('inventory.items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display inventory item details.
     */
    public function show(string $id)
    {
        $item = InventoryItem::query()
            ->with('category')
            ->findOrFail($id);

        $currentStock = $this->getCurrentStock($item);

        $inventoryValue = $this->valuationService
            ->getItemValue($item);

        /*
         * Trackable items:
         * Load available units only.
         *
         * Non-trackable items:
         * There are no individual units.
         */
        $availableUnits = $item->track_as_units
            ? $item->units()
                ->where('status', 'available')
                ->latest()
                ->get()
            : collect();

        /*
         * Latest inventory transactions.
         */
        $recentTransactions = $item->transactions()
            ->with([
                'unit',
                'user',
            ])
            ->latest()
            ->take(10)
            ->get();

        return view('inventory.items.show', compact(
            'item',
            'currentStock',
            'inventoryValue',
            'availableUnits',
            'recentTransactions'
        ));
    }

    /**
     * Show edit item page.
     */
    public function edit(string $id)
    {
        $item = InventoryItem::findOrFail($id);

        $categories = InventoryCategory::query()
            ->orderBy('name')
            ->get();

        return view('inventory.items.edit', compact(
            'item',
            'categories'
        ));
    }

    /**
     * Update inventory item.
     */
    public function update(
        UpdateInventoryItemRequest $request,
        string $id
    ) {
        $item = InventoryItem::findOrFail($id);

        $item->update(
            $request->validated()
        );

        return redirect()
            ->route('inventory.items.show', $item->id)
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Deactivate/delete inventory item.
     *
     * Items with transactions are deactivated instead of deleted.
     */
    public function destroy(string $id)
    {
        $item = InventoryItem::findOrFail($id);

        /*
         * If the item has transaction history,
         * do not delete it.
         */
        if ($item->transactions()->exists()) {
            $item->update([
                'is_active' => false,
            ]);

            return redirect()
                ->back()
                ->with(
                    'warning',
                    'Item deactivated because it has linked transactions.'
                );
        }

        /*
         * No transactions:
         * safe to delete.
         */
        $item->delete();

        return redirect()
            ->route('inventory.items.index')
            ->with(
                'success',
                'Item deleted successfully.'
            );
    }

    /**
     * Get current stock for an item.
     *
     * Trackable item:
     * SUM available inventory units.
     *
     * Non-trackable item:
     * Calculate through InventoryStockService.
     */
    protected function getCurrentStock(InventoryItem $item): float
    {
        if ($item->track_as_units) {
            return (float) $item->units()
                ->where('status', 'available')
                ->sum('quantity');
        }

        return (float) $this->stockService
            ->getAvailableStock($item->id);
    }
}