<?php

namespace App\Http\Controllers;

use App\Models\TurboReturnAmount;
use App\Integrations\Turbo\Services\TurboService;
use Illuminate\Http\Request;

class TurboReturnAmountController extends Controller
{
    protected TurboService $turboService;

    public function __construct(TurboService $turboService)
    {
        $this->turboService = $turboService;
    }

    public function index()
    {
        // Ensure only admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $amounts = TurboReturnAmount::all();
        $governments = [];
        
        try {
            $response = $this->turboService->getGovernments();
            if (isset($response['feed'])) {
                $governments = $response['feed'];
            }
        } catch (\Exception $e) {
            // Ignore if API fails
        }

        return view('pages.settings.turbo-return-amounts.index', compact('amounts', 'governments'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'government_name' => 'required|string|max:255',
            'area_name'       => 'nullable|string|max:255',
            'return_amount'   => 'required|numeric|min:0',
        ]);

        TurboReturnAmount::create($validated);

        return redirect()->back()->with('success', 'تمت إضافة تكلفة الارتجاع بنجاح');
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $amount = TurboReturnAmount::findOrFail($id);

        $validated = $request->validate([
            'government_name' => 'required|string|max:255',
            'area_name'       => 'nullable|string|max:255',
            'return_amount'   => 'required|numeric|min:0',
        ]);

        $amount->update($validated);

        return redirect()->back()->with('success', 'تم تحديث تكلفة الارتجاع بنجاح');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $amount = TurboReturnAmount::findOrFail($id);
        $amount->delete();

        return redirect()->back()->with('success', 'تم حذف تكلفة الارتجاع بنجاح');
    }

    public function getAreas($govId)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        try {
            $response = $this->turboService->getAreas($govId);
            if (isset($response['feed'])) {
                return response()->json($response['feed']);
            }
        } catch (\Exception $e) {
            return response()->json([]);
        }

        return response()->json([]);
    }
}
