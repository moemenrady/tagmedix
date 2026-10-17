<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemCategoryController extends Controller
{

    public function index(Request $request)
    {
        $categories = ItemCategory::latest()->get();

        return view('pages.inventory.categories.index', compact('categories'));
    }

    /**
     * Store new category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:item_categories,name',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $category = ItemCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء تصنيف الصنف بنجاح',
            'data' => $category,
        ], 201);
    }

    /**
     * Get single category
     */
    public function show(ItemCategory $itemCategory)
    {
        $itemCategory->load('items');

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات التصنيف بنجاح',
            'data' => $itemCategory,
        ]);
    }

    /**
     * Update category
     */
    public function update(
        Request $request,
        ItemCategory $itemCategory
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_categories', 'name')
                    ->ignore($itemCategory->id),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $itemCategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث تصنيف الصنف بنجاح',
            'data' => $itemCategory->fresh(),
        ]);
    }

    /**
     * Delete category
     */
    public function destroy(ItemCategory $itemCategory)
    {
        // منع حذف التصنيف إذا كان مرتبطًا بأصناف
        if ($itemCategory->items()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف التصنيف لأنه مرتبط بأصناف',
            ], 422);
        }

        $itemCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف تصنيف الصنف بنجاح',
        ]);
    }
}
