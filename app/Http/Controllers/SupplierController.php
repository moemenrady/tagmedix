<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers with stats.
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::with(['purchaseInvoices', 'payments'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json($suppliers);
        }

        return view('pages.suppliers.index', compact('suppliers'));
    }

    /**
     * Show detailed profile and statement of account for a supplier.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseInvoices.items.inventoryItem',
            'payments.purchaseInvoice',
            'inventoryItems.variants'
        ]);

        $invoices = $supplier->purchaseInvoices()->latest()->paginate(10, ['*'], 'invoices_page');
        $payments = $supplier->payments()->latest()->paginate(10, ['*'], 'payments_page');

        return view('pages.suppliers.show', compact('supplier', 'invoices', 'payments'));
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        $supplier = Supplier::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المورد بنجاح',
                'supplier' => $supplier,
            ], 201);
        }

        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد بنجاح');
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المورد بنجاح',
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('suppliers.show', $supplier)->with('success', 'تم تحديث بيانات المورد بنجاح');
    }

    /**
     * Remove a supplier from storage.
     */
    public function destroy(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        // Check if supplier has linked invoices
        if ($supplier->purchaseInvoices()->exists()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن حذف المورد لوجود فواتير شراء مرتبطة به. يمكنك إلغاء تفعيله بدلاً من ذلك.',
                ], 422);
            }
            return back()->with('error', 'لا يمكن حذف المورد لوجود فواتير شراء مرتبطة به.');
        }

        $supplier->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المورد بنجاح',
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد بنجاح');
    }

    /**
     * Record a payment / voucher for this supplier.
     */
    public function addPayment(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'purchase_invoice_id' => 'nullable|exists:purchase_invoices,id',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['supplier_id'] = $supplier->id;
        $validated['payment_number'] = SupplierPayment::generatePaymentNumber();
        $validated['created_by'] = auth()->id();

        $payment = SupplierPayment::create($validated);

        // If linked to a specific invoice, update that invoice's totals
        if (!empty($validated['purchase_invoice_id'])) {
            $invoice = $supplier->purchaseInvoices()->find($validated['purchase_invoice_id']);
            $invoice?->recalculateTotals();
        }

        return back()->with('success', 'تم تسجيل سند الصرف والدفعة للمورد بنجاح.');
    }
}
