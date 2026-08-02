<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Get all items
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. الإحصائيات العامة
        |--------------------------------------------------------------------------
        */

        $stats = [

            // إجمالي عدد الأصناف
            'total_items' => Item::count(),

            // الأصناف النشطة
            'active_items' => Item::where('is_active', true)->count(),

            // الأصناف غير النشطة
            'inactive_items' => Item::where('is_active', false)->count(),

            // إجمالي التصنيفات
            'total_categories' => ItemCategory::count(),

            // التصنيفات النشطة
            'active_categories' => ItemCategory::where('is_active', true)->count(),

            // إجمالي الكمية الموجودة في المخزن
            'total_quantity' => Item::sum('current_quantity'),

            // إجمالي قيمة المخزون الحالية
            'total_inventory_value' => Item::select(
                DB::raw('SUM(current_quantity * last_purchase_price) as total')
            )->value('total') ?? 0,

            // عدد الأصناف التي وصلت للحد الأدنى
            'low_stock_items' => Item::whereColumn(
                'current_quantity',
                '<=',
                'minimum_quantity'
            )->count(),

            // عدد الأصناف التي نفدت
            'out_of_stock_items' => Item::where(
                'current_quantity',
                '<=',
                0
            )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | 2. إحصائيات حركات المخزون
        |--------------------------------------------------------------------------
        */

        $transactionStats = [

            // إجمالي المشتريات
            'total_purchases' => InventoryTransaction::where(
                'type',
                'purchase'
            )->sum(
                DB::raw('quantity * unit_cost')
            ),

            // إجمالي كمية المشتريات
            'purchased_quantity' => InventoryTransaction::where(
                'type',
                'purchase'
            )->sum('quantity'),

            // إجمالي الصرف
            'issued_quantity' => InventoryTransaction::where(
                'type',
                'issue'
            )->sum('quantity'),

            // قيمة الصرف
            'issued_value' => InventoryTransaction::where(
                'type',
                'issue'
            )->sum(
                DB::raw('quantity * unit_cost')
            ),

            // المرتجع إلى المخزن
            'return_in_quantity' => InventoryTransaction::where(
                'type',
                'return_in'
            )->sum('quantity'),

            // المرتجع إلى المورد
            'return_out_quantity' => InventoryTransaction::where(
                'type',
                'return_out'
            )->sum('quantity'),

            // التسويات بالزيادة
            'adjustment_in_quantity' => InventoryTransaction::where(
                'type',
                'adjustment_in'
            )->sum('quantity'),

            // التسويات بالنقص
            'adjustment_out_quantity' => InventoryTransaction::where(
                'type',
                'adjustment_out'
            )->sum('quantity'),

            // إجمالي عدد الحركات
            'total_transactions' => InventoryTransaction::count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | 3. الأصناف مع بياناتها الكاملة
        |--------------------------------------------------------------------------
        */

        $items = Item::with('category')

            // عدد الحركات الخاصة بكل صنف
            ->withCount('inventoryTransactions')

            // إجمالي الكمية التي دخلت للصنف
            ->withSum([
                'inventoryTransactions as total_purchased_quantity' => function ($query) {
                    $query->where('type', 'purchase');
                }
            ], 'quantity')

            // إجمالي الكمية التي خرجت
            ->withSum([
                'inventoryTransactions as total_issued_quantity' => function ($query) {
                    $query->where('type', 'issue');
                }
            ], 'quantity')

            // البحث
            ->when($request->search, function ($query, $search) {

                $query->where(function ($query) use ($search) {

                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })

            // فلترة حسب التصنيف
            ->when($request->category_id, function ($query, $categoryId) {

                $query->where(
                    'category_id',
                    $categoryId
                );
            })

            // فلترة حسب الحالة
            ->when(
                $request->is_active !== null,
                function ($query) use ($request) {

                    $query->where(
                        'is_active',
                        $request->boolean('is_active')
                    );
                }
            )

            // فلترة الأصناف منخفضة المخزون
            ->when(
                $request->low_stock,
                function ($query) {

                    $query->whereColumn(
                        'current_quantity',
                        '<=',
                        'minimum_quantity'
                    );
                }
            )

            // فلترة الأصناف التي نفدت
            ->when(
                $request->out_of_stock,
                function ($query) {

                    $query->where(
                        'current_quantity',
                        '<=',
                        0
                    );
                }
            )

            ->latest()

            ->paginate(
                min(
                    $request->integer('per_page', 15),
                    100
                )
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | 4. الأصناف منخفضة المخزون
        |--------------------------------------------------------------------------
        */

        $lowStockItems = Item::with('category')
            ->whereColumn(
                'current_quantity',
                '<=',
                'minimum_quantity'
            )
            ->where('is_active', true)
            ->orderBy('current_quantity')
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 5. الأصناف الأعلى قيمة
        |--------------------------------------------------------------------------
        */

        $highestValueItems = Item::with('category')
            ->select('*')
            ->selectRaw(
                '(current_quantity * last_purchase_price) as inventory_value'
            )
            ->orderByDesc('inventory_value')
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 6. آخر حركات المخزون
        |--------------------------------------------------------------------------
        */

        $latestTransactions = InventoryTransaction::with([
            'item:id,name,code,unit',
            'creator:id,name',
        ])
            ->latest()
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 7. أكثر الأصناف حركة
        |--------------------------------------------------------------------------
        */

        $mostMovedItems = Item::with('category')
            ->withCount('inventoryTransactions')
            ->orderByDesc('inventory_transactions_count')
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 8. إحصائيات أنواع الحركات
        |--------------------------------------------------------------------------
        */

        $transactionTypes = InventoryTransaction::select(
            'type',
            DB::raw('COUNT(*) as transactions_count'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(quantity * unit_cost) as total_value')
        )
            ->groupBy('type')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 9. إحصائيات التصنيفات
        |--------------------------------------------------------------------------
        */

        $categoryStats = ItemCategory::withCount('items')
            ->withSum('items', 'current_quantity')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | 10. إحصائيات الشهور
        |--------------------------------------------------------------------------
        */

        $monthlyTransactions = InventoryTransaction::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as transactions_count'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(quantity * unit_cost) as total_value')
        )
            ->where(
                'created_at',
                '>=',
                now()->subMonths(12)
            )
            ->groupBy(
                DB::raw('YEAR(created_at)'),
                DB::raw('MONTH(created_at)')
            )
            ->orderBy('year')
            ->orderBy('month')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'pages.inventory.items.index',
            compact(
                'items',
                'stats',
                'transactionStats',
                'lowStockItems',
                'highestValueItems',
                'latestTransactions',
                'mostMovedItems',
                'transactionTypes',
                'categoryStats',
                'monthlyTransactions'
            )
        );
    }
    public function create()
    {
        $categories = ItemCategory::where('is_active', true)->get();
        return view('pages.inventory.create', compact('categories'));
    }
    /**
     * Store new item
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                'unique:items,code',
            ],

            'category_id' => [
                'required',
                'exists:item_categories,id',
            ],

            'unit' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'minimum_quantity' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $item = Item::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'category_id' => $validated['category_id'],
            'unit' => $validated['unit'] ?? null,
            'description' => $validated['description'] ?? null,
            'minimum_quantity' => $validated['minimum_quantity'] ?? 0,
            'current_quantity' => 0,
            'last_purchase_price' => 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $item->load('category');

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الصنف بنجاح',
            'data' => $item,
        ], 201);
    }

    /**
     * Show single item
     */
    public function show(Item $item): JsonResponse
    {
        $item->load('category');

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات الصنف بنجاح',
            'data' => $item,
        ]);
    }

    /**
     * Update item
     */
    public function update(
        Request $request,
        Item $item
    ): JsonResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'code')
                    ->ignore($item->id),
            ],

            'category_id' => [
                'required',
                'exists:item_categories,id',
            ],

            'unit' => [
                'nullable',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'minimum_quantity' => [
                'required',
                'numeric',
                'min:0',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $item->update($validated);

        $item->load('category');

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الصنف بنجاح',
            'data' => $item,
        ]);
    }

    /**
     * Delete item
     */
    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الصنف بنجاح',
        ]);
    }
}
