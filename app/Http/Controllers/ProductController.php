<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('variants')
            ->latest()
            ->paginate(2000);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([

                'name' => 'required|string|max:255',

                'selling_price' => 'required|numeric|min:0',

                'cost_price' => 'nullable|numeric|min:0',

                'type' =>
                'required|in:hoodie,tshirt,pants,shirt,custom',

                'variants' => 'required|array|min:1',

                'variants.*.color' => 'required|string',

                'variants.*.size' => 'required|string',

                'variants.*.quantity' =>
                'required|integer|min:0',
            ]);

            DB::beginTransaction();

            $product = Product::create([

                'name' => $request->name,

                'description' => $request->description,

                'selling_price' => $request->selling_price,

                'cost_price' => $request->cost_price ?? 0,

                'type' => $request->type,

                'sleeve_type' => $request->sleeve_type,

                'design_code' => $request->design_code,

                'image' => $request->image,
            ]);

            foreach ($request->variants as $variant) {

                $createdVariant =
                    $product->variants()->create([

                        'color' => $variant['color'],

                        'size' => $variant['size'],

                        'quantity' => $variant['quantity'],

                        'minimum_quantity' =>
                        $variant['minimum_quantity'] ?? 0,

                        'selling_price' =>
                        $variant['selling_price'] ?? null,
                    ]);

                if ($variant['quantity'] > 0) {

                    StockMovement::create([

                        'product_variant_id' =>
                        $createdVariant->id,

                        'type' => 'add',

                        'quantity' => $variant['quantity'],

                        'reason' => 'Initial stock',
                    ]);
                }
            }

            DB::commit();

            return redirect()

                ->back()

                ->with(
                    'success',
                    'تم إنشاء المنتج بنجاح'
                );
        } catch (\Illuminate\Validation\ValidationException $e) {

            return redirect()

                ->back()

                ->withErrors($e->validator)

                ->withInput()

                ->with(
                    'error',
                    'بيانات المنتج غير صحيحة'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()

                ->back()

                ->withInput()

                ->with(
                    'error',
                    'فشل إنشاء المنتج: ' . $e->getMessage()
                );
        }
    }

    public function show($id)
    {
        $product = Product::with('variants')
            ->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update($request->all());

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }

    public function restore($id)
    {
        Product::withTrashed()
            ->findOrFail($id)
            ->restore();

        return response()->json([
            'message' => 'Restored successfully'
        ]);
    }

    public function lowStock()
    {
        $variants = ProductVariant::with('product')
            ->whereColumn(
                'quantity',
                '<=',
                'minimum_quantity'
            )
            ->get();

        return response()->json($variants);
    }

    public function addStock(Request $request, $id)
    {
        $variant =
            ProductVariant::findOrFail($id);

        $request->validate([
            'quantity' =>
            'required|integer|min:1'
        ]);

        $variant->increment(
            'quantity',
            $request->quantity
        );

        StockMovement::create([

            'product_variant_id' =>
            $variant->id,

            'type' => 'add',

            'quantity' =>
            $request->quantity,

            'reason' =>
            $request->reason,
        ]);

        return response()->json([
            'message' => 'Stock added'
        ]);
    }

    public function deductStock(Request $request, $id)
    {
        $variant =
            ProductVariant::findOrFail($id);

        $request->validate([
            'quantity' =>
            'required|integer|min:1'
        ]);

        if (
            $variant->quantity <
            $request->quantity
        ) {
            return response()->json([
                'message' => 'Insufficient stock'
            ], 400);
        }

        $variant->decrement(
            'quantity',
            $request->quantity
        );

        StockMovement::create([

            'product_variant_id' =>
            $variant->id,

            'type' => 'deduct',

            'quantity' =>
            $request->quantity,

            'reason' =>
            $request->reason,
        ]);

        return response()->json([
            'message' => 'Stock deducted'
        ]);
    }
}
